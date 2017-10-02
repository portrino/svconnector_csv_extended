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
 * Class BaseServiceTest
 * @package Portrino\SvconnectorCsvExtended\Tests\Unit\Service
 */
abstract class BaseServiceTest extends \Nimut\TestingFramework\TestCase\UnitTestCase
{
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
        $this->fixturePath =  PATH_site . 'typo3conf/ext/svconnector_csv_extended/Tests/Unit/Fixtures/';
    }

    protected function tearDown()
    {
        parent::tearDown();
    }
}
