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
class CycleServiceTest extends BaseServiceTest
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

        $this->inject($this->cycleService, 'fileNameService', $this->fileNameService);
    }

    /**
     * @test
     */
    public function hasCycleBehaviour()
    {
        $parameters['rows_per_cycle'] = 10;
        static::assertTrue($this->cycleService->hasCycleBehaviour($parameters));
    }

    /**
     * @test
     */
    public function getRowsPerCycle()
    {
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
        $parameters = [
            'filename' => $this->fixturePath . self::$csvFile
        ];
        $fileNameOfCsvFile = $this->cycleService->getFileNameOfCsvFile($parameters);
        static::assertContains(
            GeneralUtility::getFileAbsFileName($this->fixturePath . self::$csvFile),
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
        $fileName = $this->fixturePath . self::$csvFile;
        static::assertTrue($this->cycleService->fileIsExisting($fileName));
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
            ->willReturn($this->fixturePath . self::$cycleTempFileName);

        $this->inject($this->cycleService, 'fileNameService', $fileNameService);

        $parameters = [
            'filename' => $this->fixturePath . self::$csvFile,
            'rows_per_cycle' => 2
        ];

        $cycleInfo = $this->cycleService->getCycleInfo($parameters);

        static::assertEquals(1, $cycleInfo->getCycle());
        static::assertEquals(936, $cycleInfo->getLastPosition());
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

        static::assertEquals(20, $cycleService->getProgress([]));
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

        static::assertEquals(100, $cycleService->getProgress([]));
    }

    /**
     * @test
     */
    public function getTotalRowsOfImportFile()
    {
        $parameters['filename'] = $this->fixturePath . self::$csvFile;

        $totalRows = $this->cycleService->getTotalRowsOfImportFile($parameters);
        static::assertEquals(3, $totalRows);
    }

    /**
     * @test
     */
    public function storeCycleInfo()
    {
        $cycleInfo = new CycleInfo(1, 100);

        $parameters = [
            'filename' => $this->fixturePath . self::$csvFile,
            'rows_per_cycle' => 2
        ];

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

        static::assertEquals(1, $cycleInfo->getCycle());
        static::assertEquals(100, $cycleInfo->getLastPosition());

        unlink($fileNameService->getTempPath() . 'test-123456.txt');
    }

    /**
     * @test
     */
    public function getHeaders()
    {
        $parameters = [
            'filename' => $this->fixturePath . self::$csvFileWithHeader,
            'rows_per_cycle' => 2,
            'skip_rows' => 1,
            'delimiter' => ';',
            'text_qualifier' => ''
        ];

        $headers = $this->cycleService->getHeaders($parameters);

        foreach ($headers as $headerRow) {
            static::assertContains('A', $headerRow);
            static::assertContains('B', $headerRow);
        }
    }
}
