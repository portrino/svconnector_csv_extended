<?php

/***************************************************************
 * Extension Manager/Repository config file for ext "svconnector_csv_extended".
 *
 * Auto generated 29-09-2017 09:00
 *
 * Manual updates:
 * Only the data in the array - everything else is removed by next
 * writing. "version" and "dependencies" must not be touched!
 ***************************************************************/

$EM_CONF[$_EXTKEY] = [
    'title' => 'Connector service - CSV Extended',
    'description' => 'Extended Connector service for reading a CSV line per line',
    'category' => 'services',
    'author' => 'Andre Wuttig',
    'author_email' => 'wuttig@portrino.de',
    'author_company' => 'portrino GmbH',
    'state' => 'beta',
    'uploadfolder' => 0,
    'createDirs' => 'typo3temp/external_import',
    'clearCacheOnLoad' => 1,
    'version' => '1.0.0',
    'constraints' => [
        'depends' => [
            'typo3' => '7.6.13-8.7.99',
            'svconnector_csv' => '2.1.0-2.2.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
