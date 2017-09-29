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

use Cobweb\SvconnectorCsv\Service\ConnectorCsv;

/**
 * Class ConnectorCsvExtended
 * @package Portrino\SvconnectorCsvExtended\Service
 */
class ConnectorCsvExtended extends ConnectorCsv
{


    /**
     * @return array
     */
    protected function getHeaders($parameters)
    {
        $fileData = [];
        $encoding = 0;
        if (TYPO3_DLOG || $this->extConf['debug']) {
            GeneralUtility::devLog('Call parameters', $this->extKey, -1, $parameters);
        }
        // Check if the file is defined and exists
        if (empty($parameters['filename'])) {
            $message = $this->sL('LLL:EXT:' . $this->extKey . '/sv1/locallang.xml:no_file_defined');
            if (TYPO3_DLOG || $this->extConf['debug']) {
                GeneralUtility::devLog($message, $this->extKey, 3);
            }
            throw new Exception($message, 1299358179);
        } else {
            $filename = GeneralUtility::getFileAbsFileName($parameters['filename']);
            if (file_exists($filename)) {
                // Force auto-detection of line endings
                ini_set('auto_detect_line_endings', true);
                // Check if the current (BE) charset is the same as the file encoding
                if (empty($parameters['encoding'])) {
                    $isSameCharset = true;
                } else {
                    $encoding = $this->getCharsetConverter()->parse_charset($parameters['encoding']);
                    $isSameCharset = $this->getCharset() == $encoding;
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
                    $fileData[] = $row;
                    $index++;
                    if ($index >= $skipRows) {
                        break;
                    }
                }
                fclose($fp);
                if (TYPO3_DLOG || $this->extConf['debug']) {
                    GeneralUtility::devLog('Data from file', $this->extKey, -1, $fileData);
                }
                // Reset locale, if necessary
                if (!empty($oldLocale)) {
                    setlocale(LC_ALL, $oldLocale);
                }
                // Error: file does not exist
            } else {
                $message = sprintf(
                    $this->sL('LLL:EXT:' . $this->extKey . '/sv1/locallang.xml:file_not_found'),
                    $filename
                );
                if (TYPO3_DLOG || $this->extConf['debug']) {
                    GeneralUtility::devLog($message, $this->extKey, 3);
                }
                throw new Exception($message, 1299358355);
            }
        }
        // Return the result
        return $fileData;
    }
}
