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
     * @param string $filename
     * @param string $identifier
     * @return string
     */
    public function getTempFileName($filename, $identifier = '')
    {
        $result = '';
        $filename = GeneralUtility::getFileAbsFileName($filename);
        var_dump($filename);exit;
        if ($identifier) {
            $result = $identifier . '-' . self::getTempFileName($filename) . '.txt';
        } else {
            $result = pathinfo(basename($filename),
                    PATHINFO_FILENAME) . '-' . self::getTempFileName($filename) . '.txt';
        }
        return $result;
    }

    /**
     * @param string $filename
     * @return bool|int
     */
    protected function getFileModificationTime($filename)
    {
        return filemtime($filename);
    }

}
