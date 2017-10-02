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

use Portrino\SvconnectorCsvExtended\Service\FileNameService;
use Portrino\SvconnectorCsvExtended\Service\FileNameServiceInterface;

/**
 * Class FileNameServiceTest
 * @package Portrino\SvconnectorCsvExtended\Tests\Unit\Service
 */
class FileNameServiceTest extends BaseServiceTest
{
    /**
     * @var FileNameServiceInterface|FileNameService|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $fileNameService;

    /**
     * @var string
     */
    protected static $filename = 'uploads/tx_foo/bar';

    /**
     *
     */
    protected function setUp()
    {
        parent::setUp();

        $this->fileNameService = $this->getMock(
            FileNameService::class,
            [
                'getFileAbsFileName',
                'getFileModificationTime',
            ]
        );

        $this->fileNameService
            ->expects(static::any())
            ->method('getFileAbsFileName')
            ->with($this->fixturePath  . 'test.csv')
            ->willReturn($this->fixturePath  . 'test.csv');
        $this->fileNameService
            ->expects(static::any())
            ->method('getFileModificationTime')
            ->with($this->fixturePath  . 'test.csv')
            ->willReturn(1506675716286);
    }


    /**
     * @test
     */
    public function getTempFileNameWithoutIdentifier()
    {
        $parameters = [
            'filename' => $this->fixturePath . 'test.csv',
        ];
        $tempFileName = $this->fileNameService->getTempFileName($parameters);
        static::assertEquals(
            $this->tempPath . 'test-1506675716286.txt',
            $tempFileName
        );
    }

    /**
     * @test
     */
    public function getTempFileNameWithIdentifier()
    {
        $parameters = [
            'filename' => $this->fixturePath  . 'test.csv',
            'rows_per_cycle_identifier' => 'tx_foo_bar'
        ];

        $tempFileName = $this->fileNameService->getTempFileName($parameters);
        static::assertEquals(
            $this->tempPath . 'tx_foo_bar-1506675716286.txt',
            $tempFileName
        );
    }

    /**
     * @test
     */
    public function getFileAbsFileName()
    {
        $fileNameService = new FileNameService();
        $this->assertNotEmpty($fileNameService->getFileAbsFileName('index.php'));
    }

    /**
     * @test
     */
    public function getFileModificationTime()
    {
        $fileNameService = new FileNameService();
        $absFileName = $fileNameService->getFileAbsFileName('index.php');
        $this->assertGreaterThan(1, $fileNameService->getFileModificationTime($absFileName));
    }

    /**
     * @test
     */
    public function getTempPathCreatesDirectoryIfNotExists()
    {
        $files = glob($this->tempPath . '*');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        rmdir($this->tempPath);
        $tempPath = $this->fileNameService->getTempPath();
        $this->assertTrue(file_exists($tempPath));
    }
}
