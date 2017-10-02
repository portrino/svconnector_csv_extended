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
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Service\EnvironmentService;

/**
 * Class CycledDataFetcher
 * @package Portrino\SvconnectorCsvExtended\Service
 */
class CycledDataFetcher
{
    /**
     * @var CycleServiceInterface
     */
    protected $cycleService;

    /**
     * @var CharsetConverter
     */
    protected $charsetConverter;

    /**
     * @var EnvironmentService
     */
    protected $environmentService;

    /**
     * @return CycleService
     * @codeCoverageIgnore
     */
    public function getCycleService()
    {
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        /** @var CycleService $cycleService */
        $cycleService = $objectManager->get(CycleService::class);
        return $cycleService;
    }

    /**
     * @return CharsetConverter
     * @codeCoverageIgnore
     */
    public function getCharsetConverter()
    {
        /** @var CharsetConverter $charsetConverter */
        $charsetConverter = GeneralUtility::makeInstance(CharsetConverter::class);
        return $charsetConverter;
    }

    /**
     * @param array $parameters
     * @param string $delimiter
     * @param string $qualifier
     * @param bool $isSameCharset
     * @param string $encoding
     * @param string $charset
     * @return array
     */
    public function fetchData(
        $parameters,
        $delimiter,
        $qualifier,
        $isSameCharset,
        $encoding,
        $charset
    ) {
        $result = [];
        $data = [];
        $headers = [];

        $this->cycleService = $this->getCycleService();
        $this->charsetConverter = $this->getCharsetConverter();

        if ($this->cycleService->hasCycleBehaviour($parameters)) {
            /** @var CycleInfo $cycleInfo */
            $cycleInfo = $this->cycleService->getCycleInfo($parameters);
            $rowsPerCycle = $this->cycleService->getRowsPerCycle($parameters);

            if ($cycleInfo->isFirstCycle() === false) {
                $headers = $this->cycleService->getHeaders($parameters, $charset);
            }

            $isFirstRow = true;
            $index = 0;

            $fp = fopen($this->cycleService->getFileNameOfCsvFile($parameters), 'r');
            fseek($fp, $cycleInfo->getLastPosition());
            while ($row = fgetcsv($fp, 0, $delimiter, $qualifier)) {
                // In the first row, remove UTF-8 Byte Order Mark if applicable
                if ($isFirstRow) {
                    $byteOrderMark = pack('H*', 'EFBBBF');
                    $row[0] = preg_replace('/^' . $byteOrderMark . '/', '', $row[0]);
                    $isFirstRow = false;
                }
                $numData = count($row);
                // If the row is an array with a single NULL entry, it corresponds to a blank line
                // and we want to skip it
                // see note in http://php.net/manual/en/function.fgetcsv.php#refsect1-function.fgetcsv-returnvalues
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
                $data[] = $row;

                $index++;
                if ($index >= $rowsPerCycle) {
                    break;
                }
            }

            if (count($data) > 0) {
                $cycleInfo->incrementCycle();
                $cycleInfo->setLastPosition(ftell($fp));
                $this->cycleService->storeCycleInfo($parameters, $cycleInfo);
            }

            fclose($fp);

            foreach ($headers as $rowHeader) {
                $result[] = $rowHeader;
            }

            foreach ($data as $rowData) {
                $result[] = $rowData;
            }
        }
        return $result;
    }
}
