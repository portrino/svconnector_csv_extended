<?php

namespace Portrino\SvconnectorCsvExtended\Tests\Unit\Domain\Model;

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

use Nimut\TestingFramework\MockObject\AccessibleMockObjectInterface;
use Nimut\TestingFramework\TestCase\UnitTestCase;
use Portrino\SvconnectorCsvExtended\Domain\Model\CycleInfo;

/**
 * Class CycleInfoTest
 * @package Portrino\SvconnectorCsvExtended\Tests\Unit\Domain\Model
 */
class CycleInfoTest extends UnitTestCase
{
    /**
     * @var CycleInfo|\PHPUnit_Framework_MockObject_MockObject|AccessibleMockObjectInterface
     */
    protected $cycleInfo;

    /**
     *
     */
    public function setUp()
    {
        $this->cycleInfo = $this->getAccessibleMock(
            CycleInfo::class,
            [
                'dummy'
            ],
            [1,1],
            ''
        );
    }

    /**
     * @test
     */
    public function setCycle()
    {
        $this->cycleInfo->setCycle(1);

        static::assertEquals(
            1,
            $this->cycleInfo->_get('cycle')
        );

        static::assertEquals(
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

        static::assertEquals(
            123,
            $this->cycleInfo->_get('lastPosition')
        );

        static::assertSame(
            123,
            $this->cycleInfo->getLastPosition()
        );
    }

    /**
     * @test
     */
    public function incrementCycle()
    {
        $this->cycleInfo->incrementCycle();

        static::assertEquals(
            2,
            $this->cycleInfo->_get('cycle')
        );

        static::assertSame(
            2,
            $this->cycleInfo->getCycle()
        );
    }

    /**
     * @test
     */
    public function isFirstCycle()
    {
        $this->cycleInfo->setCycle(0);
        static::assertTrue(
            $this->cycleInfo->isFirstCycle()
        );
    }
}
