<?php
defined('TYPO3_MODE') || die();

$boot = function ($_EXTKEY) {

    if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('svconnector_csv')) {
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService(
            $_EXTKEY,
            // Service type
            'connector',
            // Service key
            'tx_svconnectorcsv_extended',
            [
                'title' => 'CSV connector extended',
                'description' => 'Connector service for reading CSV files line per line',

                'subtype' => 'csv_extended',

                'available' => true,
                'priority' => 51,
                'quality' => 51,

                'os' => '',
                'exec' => '',

                'className' => \Portrino\SvconnectorCsvExtended\Service\ConnectorCsvExtended::class
            ]
        );
    }

    if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('external_import')) {
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks'][\Portrino\SvconnectorCsvExtended\Task\AutomatedSyncTaskWithProgress::class] = [
            'extension' => 'external_import',
            'title' => 'LLL:EXT:' . $_EXTKEY. '/Resources/Private/Language/ExternalImport.xlf:scheduler.title',
            'description' => 'LLL:EXT:' . $_EXTKEY . '/Resources/Private/Language/ExternalImport.xlf:scheduler.description',
            'additionalFields' => \Cobweb\ExternalImport\Task\AutomatedSyncAdditionalFieldProvider::class
        ];

        $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][\Cobweb\ExternalImport\Importer::class] = [
            'className' => \Portrino\SvconnectorCsvExtended\Importer::class
        ];
    }
};

$boot($_EXTKEY);
unset($boot);
