<?php

namespace Portrino\SvconnectorCsvExtended\Service;

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

use Portrino\SvconnectorCsvExtended\Domain\Model\CycleInfo;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class CycleService
 * @package Portrino\SvconnectorCsvExtended\Service
 */
class CycleService implements CycleServiceInterface
{
    /**
     * @var FileNameServiceInterface
     * @inject
     */
    protected $fileNameService;

    /**
     * @param string $table
     * @param int $index
     * @return bool
     */
    public function hasCycleBehaviour($table, $index)
    {
        return isset($GLOBALS['TCA'][$table]['ctrl']['external'][$index]['parameters']['rows_per_cycle']);
    }

    /**
     * @param string $table
     * @param int $index
     * @return bool|int
     */
    public function getRowsPerCycle($table, $index)
    {
        $result = false;
        if ($this->hasCycleBehaviour($table, $index)) {
            $result = (int)$GLOBALS['TCA'][$table]['ctrl']['external'][$index]['parameters']['rows_per_cycle'];
        }
        return $result;
    }

    /**
     * @param string $table
     * @param int $index
     * @return string
     */
    public function getFileNameOfCsvFile($table, $index)
    {
        $result = '';
        if (isset($GLOBALS['TCA'][$table]['ctrl']['external'][$index]['parameters']['filename'])) {
            $filename = $GLOBALS['TCA'][$table]['ctrl']['external'][$index]['parameters']['filename'];
            $result = GeneralUtility::getFileAbsFileName($filename);
        }
        return $result;
    }

    /**
     * @param string $filename
     * @return bool
     */
    public function fileIsExisting($filename)
    {
        return true;
    }

    /**
     * @param string $table
     * @param int $index
     * @return CycleInfo|null
     */
    public function getCycleInfo($table, $index)
    {
        $result = null;
        if ($this->hasCycleBehaviour($table, $index)) {
            $filename = $this->getFileNameOfCsvFile($table, $index);
            if ($this->fileIsExisting($filename)) {
                $tempFileName = $this->fileNameService->getTempFileName($parameters);
                if ($this->fileIsExisting($tempFileName)) {
                    $cycleInfo = explode('#', file_get_contents($tempFileName));
                } else {
                    $cycleInfo = [0 => 0, 1 => 0];
                }
                $result = new CycleInfo($cycleInfo[0], $cycleInfo[1]);
            }
        }
        return $result;
    }

    /**
     * @param string $table
     * @param int $index
     * @return bool|float
     */
    public function getProgress($table, $index)
    {
        $result = false;
        if ($this->hasCycleBehaviour($table, $index)) {
            $cycleInfo = $this->getCycleInfo($table, $index);
            if ($cycleInfo) {
                $totalRows = $this->getTotalRowsOfImportFile($table, $index);
                $rowsPerCycle = $this->getRowsPerCycle($table, $index);
                $result = round((intval($cycleInfo->getCycle() * $rowsPerCycle) / $totalRows) * 100, 2);
            } else {
                $result = 100.00;
            }
        }
        return $result;
    }

    /**
     * @param string $filename
     * @return int
     */
    public function getTotalRowsOfImportFile($table, $index)
    {
        $result = 0;
        $csv = $this->getFileNameOfCsvFile($table, $index);
        if ($this->fileIsExisting($csv)) {
            $fp = file($csv);
            $result = count($fp);
        }
        return $result;
    }

}
