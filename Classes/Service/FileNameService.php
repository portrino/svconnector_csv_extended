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

use TYPO3\CMS\Core\Core\SystemEnvironmentBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class FileNameService
 * @package Portrino\SvconnectorCsvExtended\Service
 */
class FileNameService implements FileNameServiceInterface
{
    /**
     * @return string
     */
    public function getTempPath()
    {
        $tempPath = GeneralUtility::getFileAbsFileName('typo3temp') . '/external_import/';
        if (!file_exists($tempPath)) {
            mkdir($tempPath, 0775, true);
        }
        return $tempPath;
    }

    /**
     * @param array $parameters
     * @return string
     */
    public function getTempFileName($parameters)
    {
        $result = '';

        $filename = $parameters['filename'];

        if (isset($parameters['rows_per_cycle_identifier'])) {
            $identifier = $parameters['rows_per_cycle_identifier'];
        } else {
            $identifier = false;
        }

        $absFilename = $this->getFileAbsFileName($filename);
        $modificatioTime = $this->getFileModificationTime($absFilename);
        if ($identifier === false) {
            $identifier = pathinfo(basename($absFilename), PATHINFO_FILENAME);
        }
        $result = sprintf(
            '%s%s-%d.txt',
            $this->getTempPath(),
            $identifier,
            $modificatioTime
        );

        return $result;
    }

    /**
     * @param string $filename
     * @return string
     */
    public function getFileAbsFileName($filename)
    {
        return GeneralUtility::getFileAbsFileName($filename);
    }

    /**
     * @param string $filename
     * @return bool|int
     */
    public function getFileModificationTime($filename)
    {
        return filemtime($filename);
    }
}
