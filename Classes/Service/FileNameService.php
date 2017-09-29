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
        $tempPath = PATH_site . 'typo3temp/external_import/';
        if (!file_exists($tempPath)) {
            mkdir($tempPath, 0775, true);
        }
        return $tempPath;
    }

    /**
     * @param string $filename
     * @param string $identifier
     * @return string
     */
    public function getTempFileName($filename, $identifier = '')
    {
        $result = '';
        $absFilename = $this->getFileAbsFileName($filename);
        $modificatioTime = $this->getFileModificationTime($absFilename);
        if (!$identifier) {
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
