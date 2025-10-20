<?php

declare(strict_types=1);

use TYPO3\CMS\Core\Cache\Backend\FileBackend;
use TYPO3\CMS\Core\Cache\Frontend\VariableFrontend;

defined('TYPO3') or die();

$cacheName = 'ot_icons';
$GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations'][$cacheName] ??= [];
$GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations'][$cacheName] = [
    'frontend' => VariableFrontend::class,
    'backend' => FileBackend::class,
];

$GLOBALS['TYPO3_CONF_VARS']['SYS']['fluid']['namespaces']['i'][] = 'OliverThiele\OtIcons\ViewHelpers';
