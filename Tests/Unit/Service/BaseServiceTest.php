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
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class BaseServiceTest
 * @package Portrino\SvconnectorCsvExtended\Tests\Unit\Service
 */
abstract class BaseServiceTest extends UnitTestCase
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
        $this->tempPath = GeneralUtility::getFileAbsFileName('typo3temp') . '/external_import/';
        $this->fixturePath = 'EXT:svconnector_csv_extended/Tests/Unit/Fixtures/';
    }

    /**
     *
     */
    protected function tearDown()
    {
        parent::tearDown();
    }
}
