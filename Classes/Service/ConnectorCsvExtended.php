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

use Cobweb\Svconnector\Exception\SourceErrorException;
use Cobweb\SvconnectorCsv\Service\ConnectorCsv;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class ConnectorCsvExtended
 * @package Portrino\SvconnectorCsvExtended\Service
 */
class ConnectorCsvExtended extends ConnectorCsv
{

    /**
     * This method reads the content of the file line by line defined in the parameters
     * and returns it as a array
     *
     * NOTE: this method does not implement the "processParameters" hook,
     *       as it does not make sense in this case
     *
     * @param array $parameters Parameters for the call
     * @throws SourceErrorException
     * @return array Content of the file
     *
     * @codeCoverageIgnore
     */
    protected function query($parameters)
    {
        $fileData = [];
        // Check if the file is defined and exists
        if (empty($parameters['filename'])) {
            $message = $this->sL(
                'LLL:EXT:' . $this->extKey . '/Resources/Private/Language/locallang.xlf:no_file_defined'
            );
            throw new SourceErrorException(
                $message,
                1299358179
            );
        } else {
            $filename = GeneralUtility::getFileAbsFileName($parameters['filename']);
            if (file_exists($filename)) {
                // Force auto-detection of line endings
                ini_set('auto_detect_line_endings', true);

                // Check if the current (BE) charset is the same as the file encoding
                if (empty($parameters['encoding'])) {
                    $encoding = '';
                    $isSameCharset = true;
                } else {
                    $encoding = $this->getCharsetConverter()->parse_charset($parameters['encoding']);
                    $isSameCharset = $this->getCharset() === $encoding;
                }

                $delimiter = (empty($parameters['delimiter'])) ? ',' : $parameters['delimiter'];
                $qualifier = (empty($parameters['text_qualifier'])) ? '"' : $parameters['text_qualifier'];

                // Set locale, if specific locale is defined
                $oldLocale = '';
                if (!empty($parameters['locale'])) {
                    // Get the old locale first, in order to restore it later
                    $oldLocale = setlocale(LC_ALL, 0);
                    setlocale(LC_ALL, $parameters['locale']);
                }

                /** @var CycledDataFetcher $dataFetcher */
                $dataFetcher = GeneralUtility::makeInstance(CycledDataFetcher::class);
                $fileData = $dataFetcher->fetchData(
                    $parameters,
                    $delimiter,
                    $qualifier,
                    $isSameCharset,
                    $encoding,
                    $this->getCharset()
                );

                // Reset locale, if necessary
                if (!empty($oldLocale)) {
                    setlocale(LC_ALL, $oldLocale);
                }

                // Error: file does not exist
            } else {
                $message = sprintf(
                    $this->sL('LLL:EXT:' . $this->extKey . '/Resources/Private/Language/locallang.xlf:file_not_found'),
                    $parameters['filename']
                );
                throw new SourceErrorException(
                    $message,
                    1299358355
                );
            }
        }
        // Process the result if any hook is registered
        if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS'][$this->extKey]['processResponse'])) {
            foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS'][$this->extKey]['processResponse'] as $className) {
                $processor = GeneralUtility::getUserObj($className);
                $fileData = $processor->processResponse($fileData, $this);
            }
        }
        // Return the result
        return $fileData;
    }
}
