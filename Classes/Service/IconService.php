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
use TYPO3\CMS\Core\Log\Logger;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class IconService
 *
 * Provides functionality to manage and retrieve SVG icons, including handling
 * of icon mappings, caching, and formatting in multiple styles.
 */
class IconService
{
    private FrontendInterface $cache;

    private Logger $logger;

    /** @var array<string, mixed> */
    private array $settings = [];

    /** @var array<string, string> */
    private array $iconCache = []; // Request-internal cache

    public function __construct(CacheManager $cacheManager)
    {
        $this->cache = $cacheManager->getCache('ot_icons');
        $this->logger = GeneralUtility::makeInstance(LogManager::class)->getLogger(__CLASS__);
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
        // --- Normalize ariaHidden for reproducible cache keys ---
        $ariaKey = match ($ariaHidden) {
            true => 'T',
            false => 'F',
            null => 'N',
        };

        // --- Generate unique ID if not provided ---
        if ($id === '') {
            $id = 'icon-' . bin2hex(random_bytes(4));
        }

        // --- Load mapping data (cached) for this icon set ---
        $mapping = $this->getMappingData($this->settings['defaultIconSet']);

        $this->settings['defaultSubdirectory'] = $mapping['defaultSubdirectory'];
        $mappingVersion = (string)($mapping['version']);

        // --- Build cache key including mapping version for safety ---
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

        // --- In-memory cache (per request) ---
        if (isset($this->iconCache[$cacheKey])) {
            return $this->iconCache[$cacheKey];
        }

        // --- Map identifier via cached mapping data ---
        $internalIdentifier = $mapping['map'][$identifier] ?? $identifier;

        // --- Create Icon model ---
        $icon = new Icon($internalIdentifier, $this->settings);

        $icon->setSize($size);
        $icon->setAdditionalClasses($additionalClasses);
        $icon->setId($id);
        $icon->setAriaLabel($ariaLabel);
        $icon->setAriaDescription($ariaDescription);
        $icon->setTitle($title);
        $icon->setRole($role);
        $icon->setAriaHidden($ariaHidden);

        // --- Resolve effective icon style ---
        if ($iconStyle === '') {
            $iconStyle = $this->settings['defaultIconStyle'] ?? '';
        }
        $icon->setIconStyle($iconStyle);

        // --- Render SVG based on return mode ---
        $svg = match ($returnAs) {
            'localstorage', 'sprite' => $icon->getSpriteCode(),
            'base64' => $icon->getBase64(),
            default => $icon->getInline(),
        };

        // --- Store result in request cache and return ---
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
        $data = $this->getMappingData($iconSet);
        $map = $data['map'];

        return $map[$internalIdentifier] ?? $internalIdentifier;
    }

    /**
     * Retrieve the complete mapping data for an icon set, including overrides.
     *
     * This method uses TYPO3 caching for performance and merges optional
     * integrator-provided mappings from SiteConfiguration setting:
     *   otIcons.customMappingDirectory
     *
     * @param string $iconSet The icon set identifier, e.g. "FontAwesome_7"
     * @return array{
     *      prefix: string,
     *      version: string,
     *      defaultSubdirectory: string,
     *      map: array<string, string>
     *  } Structured mapping configuration with keys:
     *  - prefix (string)
     *  - version (string|int|null)
     *  - defaultSubdirectory (string)
     *  - map (array)
     */
    private function getMappingData(string $iconSet): array
    {
        $logger = $this->logger;

        $cacheKey = 'iconMap_' . $iconSet;
        $data = $this->cache->get($cacheKey);

        // ✅ Return cached result if available
        if ($data !== false) {
            return $data;
        }

        // 🗂 Load default mapping from the extension itself
        $data = $this->loadMappingFile($iconSet);

        // ⚙️ Merge optional custom mapping if configured
        $customMappingPath = $this->settings['customMappingDirectory'] ?? '';
        if ($customMappingPath !== '') {
            $customFile = GeneralUtility::getFileAbsFileName(
                rtrim($customMappingPath, '/') . '/' . $iconSet . '.php'
            );
            if (is_file($customFile)) {
                try {
                    /** @noinspection PhpIncludeInspection */
                    $customData = include $customFile;
                } catch (\Throwable $e) {
                    $logger->warning(
                        sprintf(
                            'Error including custom mapping file for icon set "%s": %s (%s)',
                            $iconSet,
                            $e->getMessage(),
                            $customFile
                        )
                    );
                    $customData = null;
                }

                if (!is_array($customData)) {
                    $logger->warning(
                        sprintf(
                            'Invalid custom mapping file for icon set "%s": expected array, got %s',
                            $iconSet,
                            get_debug_type($customData)
                        )
                    );
                } else {
                    if (isset($customData['config'])) {
                        $data['config'] = array_merge($data['config'], $customData['config']);
                    }
                    if (isset($customData['map'])) {
                        $data['map'] = array_merge($data['map'], $customData['map']);
                    }
                    $logger->info(
                        sprintf(
                            'Custom mapping for icon set "%s" successfully loaded from %s',
                            $iconSet,
                            $customFile
                        )
                    );
                }
            } else {
                $logger->warning(
                    sprintf(
                        'Custom mapping file not found for icon set "%s" at path "%s".',
                        $iconSet,
                        $customFile
                    )
                );
            }
        }

        // 🧠 Normalize structure for consistency
        $normalized = [
            'prefix' => $data['config']['prefix'] ?? '',
            'version' => (string)($data['config']['version'] ?? ''),
            'defaultSubdirectory' => $data['config']['defaultSubdirectory'] ?? '',
            'map' => $data['map'],
        ];

        // 💾 Store in TYPO3 cache for reuse
        $this->cache->set($cacheKey, $normalized, [], 0);

        return $normalized;
    }


    /**
     * Load a mapping file for a given icon set.
     *
     * @param string $iconSet The name of the icon set (e.g. "FontAwesome_7")
     * @return array{
     *      config: array{prefix: string, version: string, defaultSubdirectory: string},
     *      map: array<string,string>
     *  } The normalized mapping configuration, or an empty structure on error
     */

    private function loadMappingFile(string $iconSet): array
    {
        $logger = $this->logger;

        $defaultPath = 'EXT:ot_icons/Configuration/Mappings/' . $iconSet . '.php';
        $absPath = GeneralUtility::getFileAbsFileName($defaultPath);

        $empty = [
            'config' => [
                'prefix' => '',
                'version' => '',
                'defaultSubdirectory' => '',
            ],
            'map' => [],
        ];

        if (!is_file($absPath)) {
            $logger->notice(sprintf('Mapping file not found for icon set "%s" at path "%s".', $iconSet, $absPath));
            return $empty;
        }

        try {
            /** @noinspection PhpIncludeInspection */
            $data = include $absPath;
        } catch (\Throwable $e) {
            $logger->warning(
                sprintf(
                    'Error including mapping file for icon set "%s": %s (%s)',
                    $iconSet,
                    $e->getMessage(),
                    $absPath
                )
            );
            return $empty;
        }

        if (!is_array($data)) {
            $logger->warning(
                sprintf(
                    'Invalid mapping file structure for icon set "%s": expected array, got %s',
                    $iconSet,
                    get_debug_type($data)
                )
            );
            return $empty;
        }

        return [
            'config' => [
                'prefix' => (string)($data['config']['prefix'] ?? ''),
                'version' => (string)($data['config']['version'] ?? ''),
                'defaultSubdirectory' => (string)($data['config']['defaultSubdirectory'] ?? ''),
            ],
            'map' => is_array($data['map'] ?? null) ? $data['map'] : [],
        ];
    }

}
