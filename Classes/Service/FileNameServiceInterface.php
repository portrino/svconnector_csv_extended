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

/**
 * Interface FileNameServiceInterface
 * @package Portrino\SvconnectorCsvExtended\Service
 */
interface FileNameServiceInterface
{
    /**
     * @param string $filename
     * @param string $identifier
     * @return string
     */
    public static function getTempFileName($filename, $identifier = '');
}
