<?php

namespace Portrino\SvconnectorCsvExtended\Tests\Unit\Service;

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

use Nimut\TestingFramework\TestCase\UnitTestCase;
use Portrino\SvconnectorCsvExtended\Domain\Model\CycleInfo;
use Portrino\SvconnectorCsvExtended\Service\CycleService;
use Portrino\SvconnectorCsvExtended\Service\CycleServiceInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class CycleInfoTest
 * @package Portrino\SvconnectorCsvExtended\Tests\Unit\Service
 */
class CycleInfoTest extends UnitTestCase
{
    /**
     * @var CycleInfo
     */
    protected $cycleInfo;

    /**
     *
     */
    public function setUp()
    {
        $this->cycleInfo = new CycleInfo(1, 1);
    }

    /**
     * @test
     */
    public function setCycle()
    {
        $this->cycleInfo->setCycle(1);

        static::assertAttributeEquals(
            1,
            'cycle',
            $this->cycleInfo
        );
        static::assertSame(
            1,
            $this->cycleInfo->getCycle()
        );
    }

    /**
     * @test
     */
    public function setLastPosition()
    {
        $this->cycleInfo->setLastPosition(123);

        static::assertAttributeEquals(
            123,
            'lastPosition',
            $this->cycleInfo
        );
        static::assertSame(
            123,
            $this->cycleInfo->getLastPosition()
        );
    }

    /**
     *
     */
    public function incrementCycle()
    {
        $this->cycleInfo->incrementCycle();

        static::assertAttributeEquals(
            2,
            'cycle',
            $this->cycleInfo
        );
        static::assertSame(
            2,
            $this->cycleInfo->getCycle()
        );
    }
}
