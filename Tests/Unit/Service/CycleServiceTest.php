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
use Portrino\SvconnectorCsvExtended\Service\FileNameService;
use Portrino\SvconnectorCsvExtended\Service\FileNameServiceInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class CycleServiceTest
 * @package Portrino\SvconnectorCsvExtended\Tests\Unit\Service
 */
class CycleServiceTest extends UnitTestCase
{
    /**
     * @var CycleServiceInterface|CycleService|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $cycleService;

    /**
     * @var FileNameServiceInterface|FileNameService|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $fileNameService;

    /**
     * @var string
     */
    protected static $cycleTempFileName = 'tx_foo_bar-1-1506668314.txt';

    /**
     * @var string
     */
    protected static $csvFile = 'test.csv';

    /**
     *
     */
    protected function setUp()
    {
        parent::setUp();

        $this->cycleService = $this->getMock(
            CycleService::class,
            [
                'dummy'
            ]
        );

        $this->fileNameService = $this->getMock(
            FileNameService::class,
            [
                'dummy'
            ]
        );

        $this->inject($this->cycleService,'fileNameService', $this->fileNameService);

        copy(
            __DIR__ . '/../Fixtures/' . self::$cycleTempFileName,
            $this->fileNameService->getTempPath() . self::$cycleTempFileName
        );

        copy(
            __DIR__ . '/../Fixtures/' . self::$csvFile,
            PATH_site . 'typo3temp/' . self::$csvFile
        );
    }

    protected function tearDown()
    {
        parent::tearDown();

        unlink($this->fileNameService->getTempPath() . self::$cycleTempFileName);
        unlink(PATH_site . 'typo3temp/' . self::$csvFile);
    }


    /**
     * @test
     */
    public function hasCycleBehaviour()
    {
        $table = 'tx_foo_bar';
        $index = 1;

        $parameters['rows_per_cycle'] = 10;

        static::assertTrue($this->cycleService->hasCycleBehaviour($parameters));
    }

    /**
     * @test
     */
    public function getRowsPerCycle()
    {
        $table = 'tx_foo_bar';
        $index = 1;
        $parameters['rows_per_cycle'] = 10;

        static::assertEquals(10, $this->cycleService->getRowsPerCycle($parameters));

        unset($parameters['rows_per_cycle']);

        static::assertFalse($this->cycleService->getRowsPerCycle($parameters));
    }

    /**
     * @test
     */
    public function getFileNameOfCsvFile()
    {
        $table = 'tx_foo_bar';
        $index = 1;
        $parameters['filename'] = 'typo3temp/' . self::$csvFile;

        $fileNameOfCsvFile = $this->cycleService->getFileNameOfCsvFile($parameters);
        static::assertContains(
            GeneralUtility::getFileAbsFileName('typo3temp/' . self::$csvFile),
            $fileNameOfCsvFile
        );

        unset($parameters['filename']);

        static::assertEmpty($this->cycleService->getFileNameOfCsvFile($parameters));
    }

    /**
     * @test
     */
    public function fileIsExisting()
    {
        $this->cycleService->fileIsExisting(GeneralUtility::getFileAbsFileName('index.php'));
    }

    /**
     * @test
     */
    public function getCycleInfo()
    {
        /** @var FileNameServiceInterface|FileNameService|\PHPUnit_Framework_MockObject_MockObject $fileNameService */
        $fileNameService = $this->getMock(
            FileNameService::class,
            [
                'getTempFileName'
            ]
        );
        $fileNameService
            ->expects(static::any())
            ->method('getTempFileName')
            ->willReturn($fileNameService->getTempPath() . self::$cycleTempFileName);

        $this->inject($this->cycleService, 'fileNameService', $fileNameService);

        $parameters['filename'] = 'foo.csv';
        $parameters['rows_per_cycle'] = 2;

        $cycleInfo = $this->cycleService->getCycleInfo($parameters);

        $this->assertEquals(1, $cycleInfo->getCycle());
        $this->assertEquals(936, $cycleInfo->getLastPosition());
    }

    /**
     * @test
     */
    public function getProgress()
    {
        /** @var CycleServiceInterface|CycleService|\PHPUnit_Framework_MockObject_MockObject $cycleService */
        $cycleService = $this->getMock(
            CycleService::class,
            [
                'hasCycleBehaviour',
                'getCycleInfo',
                'getTotalRowsOfImportFile',
                'getRowsPerCycle'
            ]
        );

        $cycleService->expects(static::any())->method('hasCycleBehaviour')->willReturn(true);
        $cycleInfo = new CycleInfo(1, 100);
        $cycleService->expects(static::any())->method('getCycleInfo')->willReturn($cycleInfo);
        $cycleService->expects(static::any())->method('getTotalRowsOfImportFile')->willReturn(10);
        $cycleService->expects(static::any())->method('getRowsPerCycle')->willReturn(2);

        $this->assertEquals(20, $cycleService->getProgress([]));
    }

    /**
     * @test
     */
    public function getProgressIfNoCycleInfoExists()
    {
        /** @var CycleServiceInterface|CycleService|\PHPUnit_Framework_MockObject_MockObject $cycleService */
        $cycleService = $this->getMock(
            CycleService::class,
            [
                'hasCycleBehaviour',
                'getCycleInfo',
                'getTotalRowsOfImportFile',
                'getRowsPerCycle'
            ]
        );

        $cycleService->expects(static::any())->method('hasCycleBehaviour')->willReturn(true);
        $cycleService->expects(static::any())->method('getCycleInfo')->willReturn(null);
        $cycleService->expects(static::any())->method('getTotalRowsOfImportFile')->willReturn(10);
        $cycleService->expects(static::any())->method('getRowsPerCycle')->willReturn(2);

        $this->assertEquals(100, $cycleService->getProgress([]));
    }

    /**
     * @test
     */
    public function getTotalRowsOfImportFile()
    {
        $table = 'tx_foo_bar';
        $index = 1;
        $parameters['filename'] = 'typo3temp/' . self::$csvFile;

        $totalRows = $this->cycleService->getTotalRowsOfImportFile($parameters);
        $this->assertEquals(3, $totalRows);
    }

    /**
     * @test
     */
    public function storeCycleInfo()
    {
        $cycleInfo = new CycleInfo(1, 100);

        $parameters['filename'] = 'typo3temp/' . self::$csvFile;
        $parameters['rows_per_cycle'] = 2;

        /** @var CycleServiceInterface|CycleService|\PHPUnit_Framework_MockObject_MockObject $cycleService */
        $cycleService = $this->getMock(
            CycleService::class,
            [
                'dummy',
            ]
        );

        /** @var FileNameServiceInterface|FileNameService|\PHPUnit_Framework_MockObject_MockObject $fileNameService */
        $fileNameService = $this->getMock(
            FileNameService::class,
            [
                'getTempFileName'
            ]
        );
        $fileNameService
            ->expects(static::any())
            ->method('getTempFileName')
            ->willReturn(
                $fileNameService->getTempPath() . 'test-123456.txt'
            );
        $this->inject($cycleService, 'fileNameService', $fileNameService);
        $cycleService->storeCycleInfo($parameters, $cycleInfo);

        $cycleInfo = $cycleService->getCycleInfo($parameters);

        $this->assertEquals(1, $cycleInfo->getCycle());
        $this->assertEquals(100, $cycleInfo->getLastPosition());

        unlink($fileNameService->getTempPath() . 'test-123456.txt');
    }
}
