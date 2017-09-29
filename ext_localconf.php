<?php
defined('TYPO3_MODE') || die();

$boot = function ($_EXTKEY) {

    if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('svconnector_csv')) {
//        $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][\Cobweb\SvconnectorCsv\Service\ConnectorCsv::class] = [
//            'className' => \Portrino\SvconnectorCsvExtended\Service\ConnectorCsvExtended::class
//        ];
        if (!is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['svconnector_csv']['fetchData'])) {
            $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['svconnector_csv']['fetchData'] = [];
        }
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['svconnector_csv']['fetchData'][] =
            \Portrino\SvconnectorCsvExtended\Service\CycledDataFetcher::class;
    }

};

$boot($_EXTKEY);
unset($boot);











