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
     * @var FileNameServiceInterface
     */
    protected $fileNameService;

    protected static $filename = 'uploads/tx_foo/bar';

    protected function setUp()
    {
        parent::setUp();

        /** @var FileNameServiceInterface|\PHPUnit_Framework_MockObject_MockObject $fileNameService */
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
        static::assertEquals('bar-1506675716286.txt', $tempFileName);
    }

    /**
     * @test
     */
    public function getTempFileNameWithIdentifier()
    {
        $tempFileName = $this->fileNameService->getTempFileName(self::$filename, 'identifier');
        static::assertEquals('identifier-1506675716286.txt', $tempFileName);
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
}
