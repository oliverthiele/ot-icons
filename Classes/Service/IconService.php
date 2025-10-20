<?php

declare(strict_types=1);

namespace OliverThiele\OtIcons\Service;

/**
 * Copyright notice
 * (c) 2025 Oliver Thiele <mail@oliver-thiele.de>, Web Development Oliver Thiele
 * All rights reserved
 * This script is part of the TYPO3 project. The TYPO3 project is
 * free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 * The GNU General Public License can be found at
 * http://www.gnu.org/copyleft/gpl.html.
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * This copyright notice MUST APPEAR in all copies of the script!
 */

use OliverThiele\OtIcons\Domain\Model\Icon;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use TYPO3\CMS\Core\Http\ServerRequestFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

/**
 * Class IconService
 *
 * Provides functionality to manage and retrieve SVG icons, including handling
 * of icon mappings, caching, and formatting in multiple styles.
 */
class IconService
{
    private FrontendInterface $cache;

    /** @var array<string, mixed> */
    private array $settings = [];

    /** @var array<string, string> */
    private array $iconCache = []; // Request-internal cache

    public function __construct(CacheManager $cacheManager)
    {
        $this->cache = $cacheManager->getCache('ot_icons');
        $this->setSettings();
    }

    private function setSettings(): void
    {
        /** @var ServerRequestInterface $request */
        $request = $GLOBALS['TYPO3_REQUEST'] ?? ServerRequestFactory::fromGlobals();
        $site = $request->getAttribute('site');

        $this->settings = [
            'defaultIconSet' => $site?->getSettings()->get('otIcons.defaultIconSet') ?? 'FontAwesome_7',
            'defaultIconStyle' => $site?->getSettings()->get('otIcons.defaultIconStyle') ?? '',
            'iconDirectory' => $site?->getSettings()->get('otIcons.iconDirectory'),
            'customMappingDirectory' => $site?->getSettings()->get('otIcons.customMappingDirectory') ?? '',
        ];
    }

    /**
     * Get the HTML code for an icon as a string.
     *
     * @param string $identifier
     * @param string $size
     * @param string $iconStyle
     * @param string $returnAs
     * @param string $id
     * @param string $additionalClasses
     * @param bool|null $ariaHidden
     * @param string $ariaLabel
     * @param string $ariaDescription
     * @param string $title
     * @param string $role
     * @return string
     * @throws \Random\RandomException
     */
    public function getIconString(
        string $identifier,
        string $size = '1x',
        string $iconStyle = '',
        string $returnAs = 'inline',
        string $id = '',
        string $additionalClasses = '',
        ?bool $ariaHidden = null,
        string $ariaLabel = '',
        string $ariaDescription = '',
        string $title = '',
        string $role = 'img'
    ): string {
        // Normalize ariaHidden for cache reproducibility
        $ariaKey = match ($ariaHidden) {
            true => 'T',
            false => 'F',
            null => 'N',
        };

        // If no ID is provided, generate a random one for the aria-labelledby attribute
        if ($id === '') {
            $id = 'icon-' . bin2hex(random_bytes(4));
        }

        // --- Load mapping early so version can be included in cache key ---
        $mapping = $this->loadMappingFile($this->settings['defaultIconSet']);

        $this->settings['defaultSubdirectory'] = $mapping['defaultSubdirectory'];
        $mappingVersion = (string)($mapping['version']);

        // --- Build cache key (includes mapping version and icon set) ---
        $cacheKey = md5(implode('|', [
            $this->settings['defaultIconSet'],
            $mappingVersion,
            $identifier,
            $size,
            $iconStyle,
            $returnAs,
            $id,
            $additionalClasses,
            $ariaKey,
            $ariaLabel,
            $ariaDescription,
            $title,
            $role,
        ]));

        // --- In-memory request cache ---
        if (isset($this->iconCache[$cacheKey])) {
            return $this->iconCache[$cacheKey];
        }

        // --- Map internal identifier ---
        $internalIdentifier = $this->mapIdentifier($identifier, $this->settings['defaultIconSet']);

        // --- Create the icon model and assign attributes ---
        $icon = new Icon($internalIdentifier, $this->settings);

        $icon->setSize($size);
        $icon->setAdditionalClasses($additionalClasses);
        $icon->setId($id);
        $icon->setAriaLabel($ariaLabel);
        $icon->setAriaDescription($ariaDescription);
        $icon->setTitle($title);
        $icon->setRole($role);
        $icon->setAriaHidden($ariaHidden);

        // --- Determine effective icon style ---
        if ($iconStyle === '') {
            $iconStyle = $this->settings['defaultIconStyle'] ?? '';
        }
        $icon->setIconStyle($iconStyle);

        // --- Render based on return mode ---
        $svg = match ($returnAs) {
            'localstorage', 'sprite' => $icon->getSpriteCode(),
            'base64' => $icon->getBase64(),
            default => $icon->getInline(),
        };

        // --- Store in the request cache and return ---
        return $this->iconCache[$cacheKey] = $svg;
    }

    /**
     * Map an internal identifier to an external identifier
     *
     * @param string $internalIdentifier
     * @param string $iconSet
     * @return string
     */
    public function mapIdentifier(string $internalIdentifier, string $iconSet = 'FontAwesome_7'): string
    {
        $cacheKey = 'iconMap_' . $iconSet;
        $data = $this->cache->get($cacheKey);

        if ($data === false) {
            $data = $this->loadMappingFile($iconSet);
            $this->cache->set($cacheKey, $data, [], 0);
        }

        $map = $data['map'] ?? [];

        return $map[$internalIdentifier] ?? $internalIdentifier;
    }

    private function loadMappingFile(string $iconSet): array
    {
        $absolute = GeneralUtility::getFileAbsFileName('EXT:ot_icons/Configuration/Mappings/' . $iconSet . '.php');
        if (!is_file($absolute)) {
            throw new \RuntimeException('Mapping file not found: ' . $absolute);
        }
        $data = require $absolute;

        // Optional: project-specific override via Site Settings
        $customMappingDir = $this->settings['customMappingDirectory'];

        if (!empty($customMappingDir)) {
            $customPath = GeneralUtility::getFileAbsFileName(
                rtrim($customMappingDir, '/') . '/' . $iconSet . '.php'
            );

            if (is_file($customPath)) {
                $custom = require $customPath;
                $data['map'] = array_merge($data['map'] ?? [], $custom['map'] ?? []);
            }
        }

        return [
            'prefix' => $data['config']['prefix'] ?? '',
            'version' => $data['config']['version'] ?? null,
            'defaultSubdirectory' => $data['config']['defaultSubdirectory'] ?? '',
            'map' => $data['map'] ?? [],
        ];
    }
}
