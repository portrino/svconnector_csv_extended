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

/**
 * Class FileNameServiceTest
 * @package Portrino\SvconnectorCsvExtended\Tests\Unit\Service
 */
class FileNameServiceTest extends UnitTestCase
{

    /**
     * @test
     */
    public function getTempFileNameWithoutIdentifier()
    {
        /** @var FileNameServiceInterface|\PHPUnit_Framework_MockObject_MockObject $fileNameService */
//        $fileNameService = $this->getAccessibleMock(
//            FileNameService::class,
//            [
//                'dummy',
//            ]
//        );

        $fileNameService = new FileNameService();

        \Codeception\Util\Debug::debug($fileNameService);
        exit;

        $fileNameService->expects(static::once())->method('getFileModificationTime')->willReturn(1506675716286);

        $tempFileName = $fileNameService->getTempFileName(
            'uploads/tx_orgadb/OFDImport/ofd.csv'
        );

    }
}
