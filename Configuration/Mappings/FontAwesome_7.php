<?php

declare(strict_types=1);

/**
 * This mapping is incomplete. For further mappings, please create a pull request.
 *
 * ot-icons/Configuration/Mappings/FontAwesome_7.php
 */

return [
    'config' => [
        'prefix' => 'fa-',
        'version' => '7',
        'defaultSubdirectory' => 'solid/',
    ],
    'styleDirectories' => [
        'solid' => 'solid/',
        'regular' => 'regular/',
        'light' => 'light/',
        'thin' => 'thin/',
        'brands' => 'brands/',
        'duotone' => 'duotone/',
        'sharp-light' => 'sharp-light/',
        'sharp-regular' => 'sharp-regular/',
        'sharp-solid' => 'sharp-solid/',
        'sharp-thin' => 'sharp-thin/',
    ],
    // Fallback for unsupported icons
    'map' => [
        // internal identifier => external library identifier
        'chevron-up-square' => 'square-chevron-up',
        'chevron-left-square' => 'square-chevron-left',
        'chevron-right-square' => 'square-chevron-right',
        'chevron-down-square' => 'square-chevron-down',
        // Further deviating mappings …
    ],
];
