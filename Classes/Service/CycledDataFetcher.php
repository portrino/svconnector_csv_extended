<?php

namespace Portrino\SvconnectorCsvExtended\Service;

use Cobweb\SvconnectorCsv\Service\DataFetcherInterface;
use Portrino\SvconnectorCsvExtended\Domain\Model\CycleInfo;
use TYPO3\CMS\Core\Charset\CharsetConverter;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;

/**
 * Class CycledFetcher
 * @package Portrino\SvconnectorCsvExtended\Service
 */
class CycledDataFetcher implements DataFetcherInterface
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
     * @return CycleService
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
     * @return array
     */
    public function fetchData(
        $parameters,
        $delimiter,
        $qualifier,
        $isSameCharset,
        $encoding
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
                $headers = $this->cycleService->getHeaders($parameters);
            }

            $fp = fopen($this->cycleService->getFileNameOfCsvFile($parameters), 'r');
            fseek($fp, $cycleInfo->getLastPosition());
            $isFirstRow = true;

            $index = 0;
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
                        $row[$i] = $this->charsetConverter->conv($row[$i], $encoding, $this->getCharset());
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

            foreach ($headers as $rowHeader) {
                $result[] = $rowHeader;
            }

            foreach ($data as $rowData) {
                $result[] = $rowData;
            }
        }
        return $result;
    }

    /**
     * Gets the currently used character set depending on context.
     *
     * Defaults to UTF-8 if information is not available.
     *
     * @return string
     */
    public function getCharset()
    {
        if (TYPO3_MODE === 'FE') {
            return $GLOBALS['TSFE']->renderCharset;
        } elseif (isset($GLOBALS['LANG'])) {
            return $GLOBALS['LANG']->charSet;
        } else {
            return 'utf-8';
        }
    }
}
