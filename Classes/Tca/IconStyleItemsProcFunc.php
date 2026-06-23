<?php

declare(strict_types=1);

namespace OliverThiele\OtIcons\Tca;

use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;

final class IconStyleItemsProcFunc
{
    /**
     * @param array<string, mixed> $configuration
     */
    public function getItems(array &$configuration): void
    {
        $availableStyles = $this->resolveAvailableStyles($configuration);

        if ($availableStyles === []) {
            return;
        }

        foreach ($availableStyles as $style) {
            $configuration['items'][] = [
                'label' => ucfirst($style),
                'value' => $style,
            ];
        }
    }

    /**
     * @param array<string, mixed> $configuration
     * @return list<string>
     */
    private function resolveAvailableStyles(array $configuration): array
    {
        $pageId = (int)($configuration['row']['pid'] ?? 0);
        if ($pageId < 0) {
            $pageId = abs($pageId);
        }
        if ($pageId === 0) {
            return [];
        }

        try {
            $siteFinder = GeneralUtility::makeInstance(SiteFinder::class);
            $site = $siteFinder->getSiteByPageId($pageId);
        } catch (\Exception) {
            return [];
        }

        $setting = (string)$site->getSettings()->get('otIcons.availableIconStyles');
        if ($setting === '') {
            return [];
        }

        $styles = array_map('trim', explode(',', $setting));
        return array_values(array_filter($styles, static fn(string $value): bool => $value !== ''));
    }
}
