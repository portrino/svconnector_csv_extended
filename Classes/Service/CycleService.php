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
use TYPO3\CMS\Core\Charset\CharsetConverter;
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
     * @var \TYPO3\CMS\Core\Charset\CharsetConverter
     * @inject
     */
    protected $charsetConverter;

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
        return file_exists($filename);
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
                $tempFileName = $this->fileNameService->getTempFileName($parameters);
                $cycleInfo = [0 => 0, 1 => 0];
                if ($this->fileIsExisting($tempFileName)) {
                    $cycleInfo = explode('#', file_get_contents($tempFileName));
                }
                $result = new CycleInfo($cycleInfo[0], $cycleInfo[1]);
            }
        }
        return $result;
    }

    /**
     * @param array $parameters
     * @return false|float
     */
    public function getProgress($parameters)
    {
        $result = false;
        if ($this->hasCycleBehaviour($parameters)) {
            $cycleInfo = $this->getCycleInfo($parameters);
            $result = 100.00;
            if ($cycleInfo) {
                $totalRows = $this->getTotalRowsOfImportFile($parameters);
                $rowsPerCycle = $this->getRowsPerCycle($parameters);
                $result = round((intval($cycleInfo->getCycle() * $rowsPerCycle) / $totalRows) * 100, 2);
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
                $tempFileName = $this->fileNameService->getTempFileName($parameters);
                $cycleInfoString = $cycleInfo->getCycle() . '#' . (string)$cycleInfo->getLastPosition();
                $result = file_put_contents($tempFileName, $cycleInfoString);
            }
        }
        return $result;
    }

    /**
     * @param array $parameters
     * @param string $charset
     * @return array
     */
    public function getHeaders($parameters, $charset = 'utf8')
    {
        $headers = [];
        $filename = $this->getFileNameOfCsvFile($parameters);
        if ($this->fileIsExisting($filename)) {
            ini_set('auto_detect_line_endings', true);

            $isSameCharset = true;
            $encoding = $charset;
            if (isset($parameters['encoding'])) {
                $encoding = $this->charsetConverter->parse_charset($parameters['encoding']);
                $isSameCharset = $charset == $encoding;
            }

            // Open the file and read it line by line, already interpreted as CSV data
            $fp = fopen($filename, 'r');
            $delimiter = (empty($parameters['delimiter'])) ? ',' : $parameters['delimiter'];
            $qualifier = (empty($parameters['text_qualifier'])) ? '"' : $parameters['text_qualifier'];

            // Set locale, if specific locale is defined
            $oldLocale = '';
            if (!empty($parameters['locale'])) {
                // Get the old locale first, in order to restore it later
                $oldLocale = setlocale(LC_ALL, 0);
                setlocale(LC_ALL, $parameters['locale']);
            }

            $skipRows = $parameters['skip_rows'];
            $index = 0;

            while ($row = fgetcsv($fp, 0, $delimiter, $qualifier)) {
                $numData = count($row);
                // If the row is an array with a single NULL entry, it corresponds to a blank line
                // and we want to skip it (see note in http://php.net/manual/en/function.fgetcsv.php#refsect1-function.fgetcsv-returnvalues)
                if ($numData === 1 && current($row) === null) {
                    continue;
                }
                // If the charset of the file is not the same as the BE charset,
                // convert every input to the proper charset
                if (!$isSameCharset) {
                    for ($i = 0; $i < $numData; $i++) {
                        $row[$i] = $this->charsetConverter->conv($row[$i], $encoding, $charset);
                    }
                }
                $headers[] = $row;

                $index++;
                if ($index >= $skipRows) {
                    break;
                }
            }
        }
        return $headers;
    }
}
