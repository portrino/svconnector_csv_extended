<?php

namespace Portrino\SvconnectorCsvExtended\Service;


use Cobweb\SvconnectorCsv\Service\DataFetcherInterface;
use Portrino\SvconnectorCsvExtended\Domain\Model\CycleInfo;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;

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
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * CycledDataFetcher constructor.
     */
    public function __construct()
    {
        $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $this->cycleService = $this->objectManager->get(CycleService::class);
    }

    /**
     * @param resource $fp
     * @param array $parameters
     * @param string $delimiter
     * @param string $qualifier
     * @param string $oldLocale
     * @param bool $isSameCharset
     * @param string $encoding
     * @return array
     */
    public function fetchData(
        $fp,
        $parameters,
        $delimiter,
        $qualifier,
        $oldLocale,
        $isSameCharset,
        $encoding
    ) {
        $result = [];
        if ($this->cycleService->hasCycleBehaviour($parameters)) {
            /** @var CycleInfo $cycleInfo */
            $cycleInfo = $this->cycleService->getCycleInfo($parameters);
            $rowsPerCycle = $this->cycleService->getRowsPerCycle($parameters);
            fseek($fp, $lastPosition);

            $isFirstRow = true;
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
                        $row[$i] = $this->getCharsetConverter()->conv($row[$i], $encoding, $this->getCharset());
                    }
                }
                $result[] = $row;

                $index++;
                if ($index >= $rowsPerCycle) {
                    break;
                }
            }
            $cycleInfo->incrementCycle();
            $cycleInfo->setLastPosition(ftell($fp));
            $this->cycleService->storeCycleInfo($parameters, $cycleInfo);
            return $result;
        }

    }

}