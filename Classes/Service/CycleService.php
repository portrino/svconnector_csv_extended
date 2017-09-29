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
     * @var \Portrino\SvconnectorCsvExtended\Service\FileNameServiceInterface
     * @inject
     */
    protected $fileNameService;

    /**
     * @param array $parameters
     * @return bool
     */
    public function hasCycleBehaviour($parameters)
    {
        return isset($parameters['rows_per_cycle']);
    }

    /**
     * @param array $parameters
     * @return bool|int
     */
    public function getRowsPerCycle($parameters)
    {
        $result = false;
        if ($this->hasCycleBehaviour($parameters)) {
            $result = (int)$parameters['rows_per_cycle'];
        }
        return $result;
    }

    /**
     * @param array $parameters
     * @return string
     */
    public function getFileNameOfCsvFile($parameters)
    {
        $result = '';
        if (isset($parameters['filename'])) {
            $filename = $parameters['filename'];
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
     * @param array $parameters
     * @return CycleInfo|null
     */
    public function getCycleInfo($parameters)
    {
        $result = null;
        if ($this->hasCycleBehaviour($parameters)) {
            $filename = $this->getFileNameOfCsvFile($parameters);
            if ($this->fileIsExisting($filename)) {
                $tempFileName = $this->fileNameService->getTempFileName($filename);
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
     * @param array $parameters
     * @return bool|float
     */
    public function getProgress($parameters)
    {
        $result = false;
        if ($this->hasCycleBehaviour($parameters)) {
            $cycleInfo = $this->getCycleInfo($parameters);
            if ($cycleInfo) {
                $totalRows = $this->getTotalRowsOfImportFile($parameters);
                $rowsPerCycle = $this->getRowsPerCycle($parameters);
                $result = round((intval($cycleInfo->getCycle() * $rowsPerCycle) / $totalRows) * 100, 2);
            } else {
                $result = 100.00;
            }
        }
        return $result;
    }

    /**
     * @param $parameters
     * @return int
     */
    public function getTotalRowsOfImportFile($parameters)
    {
        $result = 0;
        $csv = $this->getFileNameOfCsvFile($parameters);
        if ($this->fileIsExisting($csv)) {
            $fp = file($csv);
            $result = count($fp);
        }
        return $result;
    }

    /**
     * @param array $parameters
     * @param CycleInfo $cycleInfo
     * @return bool|int
     */
    public function storeCycleInfo($parameters, $cycleInfo)
    {
        $result = false;
        if ($this->hasCycleBehaviour($parameters)) {
            $filename = $this->getFileNameOfCsvFile($parameters);
            if ($this->fileIsExisting($filename)) {
                $tempFileName = $this->fileNameService->getTempFileName($filename);
                $string = $cycleInfo->getCycle() . '#' . (string)$cycleInfo->getLastPosition();
                $result = file_put_contents($this->tempPath . $tempFileName, $string);
            }
        }
        return $result;
    }

}
