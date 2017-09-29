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

use TYPO3\CMS\Core\SingletonInterface;

/**
 * Interface CycleServiceInterface
 * @package Portrino\SvconnectorCsvExtended\Service
 */
interface CycleServiceInterface extends SingletonInterface
{
    /**
     * @param string $table
     * @param int $index
     * @return bool
     */
    public function hasCycleBehaviour($table, $index);

    /**
     * @param string $table
     * @param int $index
     * @return bool|int
     */
    public function getRowsPerCycle($table, $index);

    /**
     * @param string $table
     * @param int $index
     * @return string
     */
    public function getFileNameOfCsvFile($table, $index);

    /**
     * @param string $filename
     * @return bool
     */
    public function fileIsExisting($filename);

    /**
     * @param string $table
     * @param int $index
     * @return double
     */
    public function getProgress($table, $index);

    /**
     * @param string $table
     * @param int $index
     * @return CycleInfo|null
     */
    public function getCycleInfo($table, $index);
}
