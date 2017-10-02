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
use Portrino\SvconnectorCsvExtended\Service\CycledDataFetcher;
use Portrino\SvconnectorCsvExtended\Service\CycleService;
use Portrino\SvconnectorCsvExtended\Service\FileNameService;
use TYPO3\CMS\Core\Charset\CharsetConverter;

/**
 * Class CycledDataFetcherTest
 * @package Portrino\SvconnectorCsvExtended\Tests\Unit\Service
 */
class CycledDataFetcherTest extends UnitTestCase
{
    /**
     * @var CycledDataFetcher|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $cycledDataFetcher;

    /**
     * @var string
     */
    protected static $cycleTempFileName = 'test-1506687043.txt';

    /**
     * @var string
     */
    protected static $csvFile = 'test.csv';

    /**
     * @var string
     */
    protected static $csvFileWithHeader = 'test_header.csv';

    /**
     * @var string
     */
    protected $tempPath;

    /**
     * @var string
     */
    protected $fixturePath;

    /**
     *
     */
    protected function setUp()
    {
        parent::setUp();

        $this->tempPath = PATH_site . 'typo3temp/external_import/';
        $this->fixturePath = PATH_site . 'typo3conf/ext/svconnector_csv_extended/Tests/Unit/Fixtures/';

        $files = glob($this->tempPath . '*');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }

        $this->cycledDataFetcher = $this->getMock(
            CycledDataFetcher::class,
            [
                'getCycleService',
                'getCharsetConverter'
            ]
        );

        $cycleService = $this->getMock(
            CycleService::class,
            [
                'getCharset'
            ]
        );

        $cycleService
            ->expects(static::any())
            ->method('getCharset')
            ->willReturn('utf8');

        $this->cycledDataFetcher
            ->expects(static::any())
            ->method('getCycleService')
            ->willReturn($cycleService);

        $fileNameService = $this->getMock(
            FileNameService::class,
            [
                'dummy'
            ]
        );
        $this->inject($cycleService, 'fileNameService', $fileNameService);

        $charsetConverter = $this->getMock(
            CharsetConverter::class,
            [
                'parse_charset'
            ]
        );

        $this->cycledDataFetcher
            ->expects(static::any())
            ->method('getCharsetConverter')
            ->willReturn($charsetConverter);

        $charsetConverter
            ->expects(static::any())
            ->method('parse_charset')
            ->willReturn('utf8');

        $this->inject($cycleService, 'charsetConverter', $charsetConverter);
    }

    /**
     * @test
     */
    public function fetchData()
    {
        $parameters = [
            'filename' => $this->fixturePath . self::$csvFileWithHeader,
            'rows_per_cycle' => 1,
            'skip_rows' => 1,
            'delimiter' => ';',
            'text_qualifier' => '"',
            'encoding' => 'utf8'
        ];

        $data = $this->cycledDataFetcher->fetchData(
            $parameters,
            $parameters['delimiter'],
            $parameters['text_qualifier'],
            true,
            $parameters['encoding'],
            'utf8'
        );

        static::assertEquals(1, count($data));
        static::assertContains('A', $data[0][0]);
        static::assertContains('B', $data[0][1]);

        $data = $this->cycledDataFetcher->fetchData(
            $parameters,
            $parameters['delimiter'],
            $parameters['text_qualifier'],
            true,
            $parameters['encoding'],
            'utf8'
        );

        static::assertEquals(2, count($data));
        static::assertContains('A', $data[0][0]);
        static::assertContains('B', $data[0][1]);
        static::assertContains('1', $data[1][0]);
        static::assertContains('1', $data[1][1]);


        $data = $this->cycledDataFetcher->fetchData(
            $parameters,
            $parameters['delimiter'],
            $parameters['text_qualifier'],
            true,
            $parameters['encoding'],
            'utf8'
        );

        static::assertEquals(2, count($data));
        static::assertContains('A', $data[0][0]);
        static::assertContains('B', $data[0][1]);
        static::assertContains('2', $data[1][0]);
        static::assertContains('2', $data[1][1]);

        $data = $this->cycledDataFetcher->fetchData(
            $parameters,
            $parameters['delimiter'],
            $parameters['text_qualifier'],
            true,
            $parameters['encoding'],
            'utf8'
        );

        static::assertEquals(2, count($data));
        static::assertContains('A', $data[0][0]);
        static::assertContains('B', $data[0][1]);
        static::assertContains('3', $data[1][0]);
        static::assertContains('3', $data[1][1]);

        $data = $this->cycledDataFetcher->fetchData(
            $parameters,
            $parameters['delimiter'],
            $parameters['text_qualifier'],
            true,
            $parameters['encoding'],
            'utf8'
        );

        static::assertContains('A', $data[0][0]);
        static::assertContains('B', $data[0][1]);

        static::assertEquals(1, count($data));
    }
}
