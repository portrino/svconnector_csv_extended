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
use TYPO3\CMS\Core\SingletonInterface;

/**
 * Interface CycleServiceInterface
 * @package Portrino\SvconnectorCsvExtended\Service
 */
interface CycleServiceInterface extends SingletonInterface
{
    /**
     * @param array $parameters
     * @return bool
     */
    public function hasCycleBehaviour($parameters);

    /**
     * @param array $parameters
     * @return bool|int
     */
    public function getRowsPerCycle($parameters);

    /**
     * @param array $parameters
     * @return string
     */
    public function getFileNameOfCsvFile($parameters);

    /**
     * @param string $filename
     * @return bool
     */
    public function fileIsExisting($filename);

    /**
     * @param array $parameters
     * @return bool|float
     */
    public function getProgress($parameters);

    /**
     * @param array $parameters
     * @return CycleInfo|null
     */
    public function getCycleInfo($parameters);

    /**
     * @param $parameters
     * @return int
     */
    public function getTotalRowsOfImportFile($parameters);

    /**
     * @param array $parameters
     * @param CycleInfo $cycleInfo
     * @return bool|int
     */
    public function storeCycleInfo($parameters, $cycleInfo);

    /**
     * @param array $parameters
     * @return array
     */
    public function getHeaders($parameters);
}
