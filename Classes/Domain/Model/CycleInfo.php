<?php
namespace Portrino\SvconnectorCsvExtended\Domain\Model;

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
}
