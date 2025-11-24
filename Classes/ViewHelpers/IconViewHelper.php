<?php

declare(strict_types=1);

/**
 * Copyright notice
 *
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

namespace OliverThiele\OtIcons\ViewHelpers;

use OliverThiele\OtIcons\Service\IconService;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Exception;

/**
 * IconViewHelper is responsible for rendering icons with various customizable options.
 * It supports configuring size, style, accessibility properties, and output format.
 */
class IconViewHelper extends AbstractViewHelper
{
    public function __construct(
        private readonly IconService $iconService
    ) {
    }

    protected $escapeOutput = false;

    /**
     * Initialize arguments.
     *
     * @throws Exception
     * @throws \InvalidArgumentException
     */
    public function initializeArguments(): void
    {
        parent::initializeArguments();

        // Icon file / style
        $this->registerArgument('identifier', 'string', 'string with the icon identifier');
        $this->registerArgument('size', 'string', 'string with size of the icon. (2x, 3x, …)');
        $this->registerArgument('iconStyle', 'string', 'string with icon style (s/r/l/b/d/…)');
        $this->registerArgument(
            'returnAs',
            'string',
            'Return SVG as inline SVG, LocalStorage sprite (alias "sprite")[inline,localStorage,sprite]',
            false,
            'inline'
        );

        // CSS / JS
        $this->registerArgument('id', 'string', 'HTMl attribute id');
        $this->registerArgument('additionalClasses', 'string', 'String with additional CSS classes');

        // Accessible SVG
        $this->registerArgument('aria-hidden', 'bool', 'When true, aria-hidden="true" is added.');
        $this->registerArgument('aria-label', 'string', 'The label of the SVG.');
        $this->registerArgument('aria-description', 'string', 'The description of the SVG.');
        $this->registerArgument('role', 'string', 'The role of the img.', false, 'img');
        $this->registerArgument('title', 'string', 'if aria-hidden is true, the title can be set for :hover');
    }

    public function render(): string
    {
        $identifier = $this->arguments['identifier'] ?? $this->renderChildren();

        if ($identifier === null || trim((string)$identifier) === '') {
            throw new \InvalidArgumentException(
                'The IconViewHelper expects either the argument "identifier" or the content to be set.',
                1758545418,
            );
        }

        $ariaHidden = $this->arguments['aria-hidden'] ?? null;

        return $this->iconService->getIconString(
            (string)$identifier,
            (string)$this->arguments['size'],
            (string)$this->arguments['iconStyle'],
            (string)$this->arguments['returnAs'],
            (string)$this->arguments['id'],
            (string)$this->arguments['additionalClasses'],
            $ariaHidden,
            (string)$this->arguments['aria-label'],
            (string)$this->arguments['aria-description'],
            (string)$this->arguments['title'],
            (string)$this->arguments['role'],
        );
    }
}
