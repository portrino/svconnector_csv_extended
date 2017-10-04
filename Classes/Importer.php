<?php

namespace Portrino\SvconnectorCsvExtended;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use Portrino\SvconnectorCsvExtended\Service\CycleService;
use Portrino\SvconnectorCsvExtended\Service\CycleServiceInterface;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;

/**
 * Class Importer
 * @package Portrino\SvconnectorCsvExtended
 */
class Importer extends \Cobweb\ExternalImport\Importer
{
    /**
     * @var CycleServiceInterface
     */
    protected $cycleService;

    /**
     * Importer constructor.
     * @codeCoverageIgnore
     */
    public function __construct()
    {
        parent::__construct();

        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        /** @var CycleServiceInterface $cycleService */
        $this->cycleService = $objectManager->get(CycleService::class);
    }

    /**
     * This method synchronises all the external tables, respecting the order of priority
     *
     * @return array List of all messages
     */
    public function synchronizeAllTables()
    {
        // Look in the TCA for tables with an "external" control section and a "connector"
        // Tables without connectors cannot be synchronised
        // @todo: use configuration repository for this
        $externalTables = [];
        foreach ($GLOBALS['TCA'] as $tableName => $sections) {
            if (isset($sections['ctrl']['external'])) {
                foreach ($sections['ctrl']['external'] as $index => $externalConfig) {
                    if (!empty($externalConfig['connector'])) {
                        // Default priority if not defined, set to very low
                        $priority = self::DEFAULT_PRIORITY;
                        if (isset($externalConfig['priority'])) {
                            $priority = $externalConfig['priority'];
                        }
                        if (!isset($externalTables[$priority])) {
                            $externalTables[$priority] = [];
                        }
                        $externalTables[$priority][] = ['table' => $tableName, 'index' => $index];
                    }
                }
            }
        }

        // Sort tables by priority (lower number is highest priority)
        ksort($externalTables);
        if ($this->getProgressForAllTables() === false) {
            // Synchronize all tables at once
            $allMessages = [];
            foreach ($externalTables as $tables) {
                foreach ($tables as $tableData) {
                    $this->messages = [
                        FlashMessage::ERROR => [],
                        FlashMessage::WARNING => [],
                        FlashMessage::OK => []
                    ]; // Reset error messages array
                    $messages = $this->synchronizeData($tableData['table'], $tableData['index']);
                    $key = $tableData['table'] . '/' . $tableData['index'];
                    $allMessages[$key] = $messages;
                }
            }
        } else {
            // Synchronize all tables at once
            $allMessages = [];
            foreach ($externalTables as $tableKey => $tables) {
                $break = false;
                foreach ($tables as $tableDataKey => $tableData) {
                    $progress = $this->getProgressForTable($tableData['table'], $tableData['index']);
                    // for cycle data imports

                    if (intval($progress) < 100 && $progress !== false) {
                        $this->messages = [
                            FlashMessage::ERROR => [],
                            FlashMessage::WARNING => [],
                            FlashMessage::OK => []
                        ]; // Reset error messages array
                        $messages = $this->synchronizeData(
                            $tableData['table'],
                            $tableData['index']
                        );
                        $key = $tableData['table'] . '/' . $tableData['index'];
                        $allMessages[$key] = $messages;
                        $break = true;
                        break;
                    }
                }
                if ($break) {
                    break;
                }
            }
        }

        // Return compiled array of messages for all imports
        return $allMessages;
    }

    /**
     * @param $table
     * @param $index
     * @return bool|float
     */
    public function getProgressForTable($table, $index)
    {
        $result = false;
        $externalConfig = $this->configurationRepository->findByTableAndIndex(
            $table,
            $index
        );
        $parameters = isset($externalConfig['parameters']) ? $externalConfig['parameters'] : [];
        if ($this->cycleService->hasCycleBehaviour($parameters)) {
            $result = $this->cycleService->getProgress($parameters);
        }
        return $result;
    }

    /**
     * @return bool|float FALSE if there are no cycle tables within the set of configured external imports,
     *                    float is the overall percentage if the progress of all import tasks
     */
    public function getProgressForAllTables()
    {
        $result = false;
        // Look in the TCA for tables with an "external" control section and a "connector"
        // Tables without connectors cannot be synchronised
        $externalTables = [];
        foreach ($GLOBALS['TCA'] as $tableName => $sections) {
            if (isset($sections['ctrl']['external'])) {
                foreach ($sections['ctrl']['external'] as $index => $externalConfig) {
                    if (!empty($externalConfig['connector'])) {
                        // Default priority if not defined, set to very low
                        $priority = 1000;
                        if (isset($externalConfig['priority'])) {
                            $priority = $externalConfig['priority'];
                        }
                        if (!isset($externalTables[$priority])) {
                            $externalTables[$priority] = [];
                        }
                        $externalTables[$priority][] = ['table' => $tableName, 'index' => $index];
                    }
                }
            }
        }
        ksort($externalTables);
        $temp = 0;
        $tablesWithCycle = 0;
        foreach ($externalTables as $tables) {
            foreach ($tables as $tableData) {
                $externalConfig = $this->configurationRepository->findByTableAndIndex(
                    $tableData['table'],
                    $tableData['index']
                );
                $parameters = isset($externalConfig['parameters']) ? $externalConfig['parameters'] : [];
                if ($this->cycleService->hasCycleBehaviour($parameters)) {
                    $progressForTable = $this->cycleService->getProgress($parameters);
                    if ($progressForTable !== false) {
                        $temp += $progressForTable;
                        $tablesWithCycle++;
                    }
                }
            }
        }
        if ($tablesWithCycle > 0) {
            $result = $temp / $tablesWithCycle;
        }
        return $result;
    }
}
