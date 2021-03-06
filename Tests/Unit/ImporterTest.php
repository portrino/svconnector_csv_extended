<?php

namespace Portrino\SvconnectorCsvExtended\Tests\Unit;

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

use Cobweb\ExternalImport\Domain\Repository\ConfigurationRepository;
use Nimut\TestingFramework\MockObject\AccessibleMockObjectInterface;
use Nimut\TestingFramework\TestCase\UnitTestCase;
use PHPUnit_Framework_MockObject_MockObject;
use Portrino\SvconnectorCsvExtended\Importer;
use Portrino\SvconnectorCsvExtended\Service\CycleService;

/**
 * Class CycledDataFetcherTest
 * @package Portrino\SvconnectorCsvExtended\Tests\Unit
 */
class ImporterTest extends UnitTestCase
{
    /**
     * @var Importer|PHPUnit_Framework_MockObject_MockObject|AccessibleMockObjectInterface
     */
    protected $importer;

    /**
     *
     */
    protected function setUp()
    {
        parent::setUp();

        $this->importer = $this->getAccessibleMock(
            Importer::class,
            [
                'synchronizeData'
            ],
            [],
            '',
            false
        );

        $this->importer
            ->expects(static::any())
            ->method('synchronizeData')
            ->willReturn('success');
    }

    protected function tearDown()
    {
        parent::tearDown();

        unset($GLOBALS['TCA']);
    }


    /**
     * @test
     */
    public function getProgressForTable()
    {
        $table = 'tx_foo_bar';
        $index = 0;

        $parameters = [
            'filename' => 'EXT:svconnector_csv_extended/Test/Unit/Fixtures/test.csv',
            'rows_per_cycle' => 2
        ];

        $externalConfig = [
            'connector' => 'csv_extended',
            'parameters' => $parameters
        ];

        $GLOBALS['TCA'][$table]['ctrl']['external'][$index] = $externalConfig;

        $configurationRepository = $this->getMock(
            ConfigurationRepository::class,
            [
                'findByTableAndIndex',
            ]
        );

        $configurationRepository
            ->expects(static::any())
            ->method('findByTableAndIndex')
            ->with($table, $index)
            ->willReturn($externalConfig);

        $this->importer->_set('configurationRepository', $configurationRepository);

        $cycleService = $this->getMock(
            CycleService::class,
            [
                'hasCycleBehaviour',
                'getProgress'
            ]
        );

        $cycleService
            ->expects(static::any())
            ->method('hasCycleBehaviour')
            ->with($parameters)
            ->willReturn(true);

        $cycleService
            ->expects(static::any())
            ->method('getProgress')
            ->with($parameters)
            ->willReturn(66.66);

        $this->importer->_set('cycleService', $cycleService);

        $progress = $this->importer->getProgressForTable($table, $index);
        static::assertEquals(66.66, $progress);
    }

    /**
     * @test
     */
    public function getProgressForAllTables()
    {
        $table1 = 'tx_test_one';
        $index1 = 0;

        $parameters1 = [
            'filename' => 'EXT:svconnector_csv_extended/Test/Unit/Fixtures/test.csv',
            'rows_per_cycle' => 2,
            'rows_per_cycle_identifier' => 'id1'
        ];

        $externalConfig1 = [
            'connector' => 'csv_extended',
            'parameters' => $parameters1,
            'priority' => 1,
        ];

        $GLOBALS['TCA'][$table1]['ctrl']['external'][$index1] = $externalConfig1;

        $table2 = 'tx_test_two';
        $index2 = 0;

        $parameters2 = [
            'filename' => 'EXT:svconnector_csv_extended/Test/Unit/Fixtures/test.csv',
            'rows_per_cycle' => 2,
            'rows_per_cycle_identifier' => 'id2'
        ];

        $externalConfig2 = [
            'connector' => 'csv_extended',
            'parameters' => $parameters2,
            'priority' => 2,
        ];

        $GLOBALS['TCA'][$table2]['ctrl']['external'][$index2] = $externalConfig2;

        $configurationRepository = $this->getMock(
            ConfigurationRepository::class,
            [
                'findByTableAndIndex',
            ]
        );

        $configurationRepository
            ->expects(static::any())
            ->method('findByTableAndIndex')
            ->will(
                static::returnValueMap(
                    [
                        [$table1, $index1, $externalConfig1],
                        [$table2, $index2, $externalConfig2]
                    ]
                )
            );

        $this->importer->_set('configurationRepository', $configurationRepository);

        $cycleService = $this->getMock(
            CycleService::class,
            [
                'hasCycleBehaviour',
                'getProgress'
            ]
        );

        $cycleService
            ->expects(static::any())
            ->method('hasCycleBehaviour')
            ->will(
                static::returnValueMap(
                    [
                        [$parameters1, true],
                        [$parameters2, true]
                    ]
                )
            );

        $cycleService
            ->expects(static::any())
            ->method('getProgress')
            ->will(
                static::returnValueMap(
                    [
                        [$parameters1, 50],
                        [$parameters2, 0]
                    ]
                )
            );

        $this->importer->_set('cycleService', $cycleService);


        $progress = $this->importer->getProgressForAllTables();
        static::assertEquals(25, $progress);
    }

    /**
     * @test
     */
    public function synchronizeAllTablesCycled()
    {
        $table1 = 'tx_test_one';
        $index1 = 0;

        $parameters1 = [
            'filename' => 'EXT:svconnector_csv_extended/Test/Unit/Fixtures/test.csv',
            'rows_per_cycle' => 2,
            'rows_per_cycle_identifier' => 'id1'
        ];

        $externalConfig1 = [
            'connector' => 'csv_extended',
            'parameters' => $parameters1,
            'priority' => 1,
        ];

        $GLOBALS['TCA'][$table1]['ctrl']['external'][$index1] = $externalConfig1;

        $table2 = 'tx_test_two';
        $index2 = 0;

        $parameters2 = [
            'filename' => 'EXT:svconnector_csv_extended/Test/Unit/Fixtures/test.csv',
            'rows_per_cycle' => 2,
            'rows_per_cycle_identifier' => 'id2'
        ];

        $externalConfig2 = [
            'connector' => 'csv_extended',
            'parameters' => $parameters2,
            'priority' => 2,
        ];

        $GLOBALS['TCA'][$table2]['ctrl']['external'][$index2] = $externalConfig2;

        $configurationRepository = $this->getMock(
            ConfigurationRepository::class,
            [
                'findByTableAndIndex',
            ]
        );

        $configurationRepository
            ->expects(static::any())
            ->method('findByTableAndIndex')
            ->will(
                static::returnValueMap(
                    [
                        [$table1, $index1, $externalConfig1],
                        [$table2, $index2, $externalConfig2]
                    ]
                )
            );

        $this->importer->_set('configurationRepository', $configurationRepository);

        $cycleService = $this->getMock(
            CycleService::class,
            [
                'hasCycleBehaviour',
                'getProgress'
            ]
        );

        $cycleService
            ->expects(static::any())
            ->method('hasCycleBehaviour')
            ->will(
                static::returnValueMap(
                    [
                        [$parameters1, true],
                        [$parameters2, true]
                    ]
                )
            );

        $cycleService
            ->expects(static::any())
            ->method('getProgress')
            ->will(
                static::returnValueMap(
                    [
                        [$parameters1, 50],
                        [$parameters2, 0]
                    ]
                )
            );

        $this->importer->_set('cycleService', $cycleService);

        $messages = $this->importer->synchronizeAllTables();
        static::assertContains('success', $messages);
    }

    /**
     * @test
     */
    public function synchronizeAllTablesNonCycled()
    {
        $table1 = 'tx_test_one';
        $index1 = 0;

        $parameters1 = [
            'filename' => 'EXT:svconnector_csv_extended/Test/Unit/Fixtures/test.csv',
            'rows_per_cycle' => 2,
            'rows_per_cycle_identifier' => 'id1'
        ];

        $externalConfig1 = [
            'connector' => 'csv_extended',
            'parameters' => $parameters1,
            'priority' => 1,
        ];

        $GLOBALS['TCA'][$table1]['ctrl']['external'][$index1] = $externalConfig1;

        $table2 = 'tx_test_two';
        $index2 = 0;

        $parameters2 = [
            'filename' => 'EXT:svconnector_csv_extended/Test/Unit/Fixtures/test.csv',
            'rows_per_cycle' => 2,
            'rows_per_cycle_identifier' => 'id2'
        ];

        $externalConfig2 = [
            'connector' => 'csv_extended',
            'parameters' => $parameters2,
            'priority' => 2,
        ];

        $GLOBALS['TCA'][$table2]['ctrl']['external'][$index2] = $externalConfig2;

        $configurationRepository = $this->getMock(
            ConfigurationRepository::class,
            [
                'findByTableAndIndex',
            ]
        );

        $configurationRepository
            ->expects(static::any())
            ->method('findByTableAndIndex')
            ->will(
                static::returnValueMap(
                    [
                        [$table1, $index1, $externalConfig1],
                        [$table2, $index2, $externalConfig2]
                    ]
                )
            );

        $this->importer->_set('configurationRepository', $configurationRepository);

        $cycleService = $this->getMock(
            CycleService::class,
            [
                'hasCycleBehaviour',
                'getProgress'
            ]
        );

        $cycleService
            ->expects(static::any())
            ->method('hasCycleBehaviour')
            ->will(
                static::returnValueMap(
                    [
                        [$parameters1, true],
                        [$parameters2, true]
                    ]
                )
            );

        $cycleService
            ->expects(static::any())
            ->method('getProgress')
            ->will(
                static::returnValueMap(
                    [
                        [$parameters1, false],
                        [$parameters2, false]
                    ]
                )
            );

        $this->importer->_set('cycleService', $cycleService);

        $messages = $this->importer->synchronizeAllTables();

        static::assertEquals(2, count($messages));
        static::assertContains('success', $messages);
    }
}
