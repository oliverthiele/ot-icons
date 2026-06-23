<?php

declare(strict_types=1);

namespace OliverThiele\OtIcons\UserFunc;

use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;

final class IconStyleDisplayCondition
{
    /**
     * @param array<string, mixed> $parameters
     */
    public function isAvailable(array $parameters): bool
    {
        $record = $parameters['record'] ?? [];
        $pageId = (int)($record['pid'] ?? 0);
        if ($pageId <= 0) {
            return false;
        }

        try {
            $siteFinder = GeneralUtility::makeInstance(SiteFinder::class);
            $site = $siteFinder->getSiteByPageId($pageId);
        } catch (\Exception) {
            return false;
        }

        $setting = (string)$site->getSettings()->get('otIcons.availableIconStyles');
        return $setting !== '';
    }
}
