<?php
namespace Portrino\SvconnectorCsvExtended\Domain\Model;

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
 * Class CycleInfo
 * @package Portrino\SvconnectorCsvExtended\Domain\Model
 */
class CycleInfo
{
    /**
     * @var int
     */
    protected $cycle;

    /**
     * @var int
     */
    protected $lastPosition;

    /**
     * CycleInfo constructor.
     * @param int $cycle
     * @param int $lastPosition
     */
    public function __construct($cycle, $lastPosition)
    {
        $this->cycle = $cycle;
        $this->lastPosition = $lastPosition;
    }

    /**
     * @return int
     */
    public function getCycle()
    {
        return $this->cycle;
    }

    /**
     * @param int $cycle
     */
    public function setCycle($cycle)
    {
        $this->cycle = $cycle;
    }

    /**
     *
     */
    public function incrementCycle()
    {
        $this->cycle++;
    }

    /**
     * @return int
     */
    public function getLastPosition()
    {
        return $this->lastPosition;
    }

    /**
     * @param int $lastPosition
     */
    public function setLastPosition($lastPosition)
    {
        $this->lastPosition = $lastPosition;
    }

    /**
     * @return bool
     */
    public function isFirstCycle()
    {
        return ((int)$this->cycle === 0) ? true : false;
    }
}
