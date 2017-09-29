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
use Portrino\SvconnectorCsvExtended\Service\FileNameService;
use Portrino\SvconnectorCsvExtended\Service\FileNameServiceInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class FileNameServiceTest
 * @package Portrino\SvconnectorCsvExtended\Tests\Unit\Service
 */
class FileNameServiceTest extends UnitTestCase
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
     * @var string
     */
    protected static $tempPath;

    /**
     *
     */
    protected function setUp()
    {
        parent::setUp();

        self::$tempPath = PATH_site . 'typo3temp/external_import/';

        $this->fileNameService = $this->getMock(
            FileNameService::class,
            [
                'getFileAbsFileName',
                'getFileModificationTime',
            ]
        );
        $absFilename = 'web/uploads/tx_foo/bar';

        $this->fileNameService
            ->expects(static::any())
            ->method('getFileAbsFileName')
            ->with(self::$filename)
            ->willReturn($absFilename);
        $this->fileNameService
            ->expects(static::any())
            ->method('getFileModificationTime')
            ->with($absFilename)
            ->willReturn(1506675716286);
    }


    /**
     * @test
     */
    public function getTempFileNameWithoutIdentifier()
    {

        $tempFileName = $this->fileNameService->getTempFileName(self::$filename);
        static::assertEquals(
            self::$tempPath . 'bar-1506675716286.txt',
            $tempFileName
        );
    }

    /**
     * @test
     */
    public function getTempFileNameWithIdentifier()
    {
        $tempFileName = $this->fileNameService->getTempFileName(self::$filename, 'identifier');
        static::assertEquals(
            self::$tempPath . 'identifier-1506675716286.txt',
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

        $files = glob(PATH_site . 'typo3temp/external_import/'); // get all file names
        foreach ($files as $file) { // iterate files
            if (is_file($file)) {
                unlink($file); // delete file
            }
        }
        rmdir(PATH_site . 'typo3temp/external_import/');
        $tempPath = $this->fileNameService->getTempPath();
        $this->assertTrue(file_exists($tempPath));
    }
}
