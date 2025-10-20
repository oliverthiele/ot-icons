<?php

declare(strict_types=1);

namespace OliverThiele\OtIcons\Domain\Model;

use TYPO3\CMS\Core\Utility\GeneralUtility;

final class Icon
{
    // protected string $identifier; set via constructor

    protected string $size;

    private string $iconSizeString = '';

    private string $iconSizeEm = '';

    protected string $id;

    protected string $additionalClasses = '';

    /**
     * @var string $iconStyle The style of the icon, e.g. "solid", "regular", "light", "duotone", "thin", "fill", "brands"
     */
    protected string $iconStyle = '';

    private string $subdirectory = '';

    protected ?bool $ariaHidden = null;

    protected string $ariaLabel = '';
    protected string $ariaDescription = '';

    protected ?string $role = null;

    protected string $title = '';

    private const ALLOWED_ROLES = ['img', 'presentation', 'none', 'button', 'link', 'graphics-symbol'];

    private string $defaultSubdirectory = '';

    public function __construct(
        private readonly string $identifier,
        private readonly array $settings
    ) {
        $this->defaultSubdirectory = $settings['defaultSubdirectory'] ?? '';
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function getSize(): string
    {
        return $this->size;
    }

    public function setSize(string $size): void
    {
        switch ($size) {
            case 'xs':
                $iconSizeString = 'ot-xs';
                $iconSizeEm = '.75em';
                break;
            case 'sm':
                $iconSizeString = 'ot-sm';
                $iconSizeEm = '.875em';
                break;
            case 'lg':
                $iconSizeString = 'ot-lg';
                $iconSizeEm = '1.33em';
                break;
            case '1x':
                $iconSizeString = 'ot-1x';
                $iconSizeEm = '1em';
                break;
            case '2x':
                $iconSizeString = 'ot-2x';
                $iconSizeEm = '2em';
                break;
            case '3x':
                $iconSizeString = 'ot-3x';
                $iconSizeEm = '3em';
                break;
            case '4x':
                $iconSizeString = 'ot-4x';
                $iconSizeEm = '4em';
                break;
            case '5x':
                $iconSizeString = 'ot-5x';
                $iconSizeEm = '5em';
                break;
            case '6x':
                $iconSizeString = 'ot-6x';
                $iconSizeEm = '6em';
                break;
            case '7x':
                $iconSizeString = 'ot-7x';
                $iconSizeEm = '7em';
                break;
            case '8x':
                $iconSizeString = 'ot-8x';
                $iconSizeEm = '8em';
                break;
            case '9x':
                $iconSizeString = 'ot-9x';
                $iconSizeEm = '9em';
                break;
            case '10x':
                $iconSizeString = 'ot-10x';
                $iconSizeEm = '10em';
                break;
            default:
                $iconSizeString = 'ot-1x';
                $iconSizeEm = '100%';
        }

        $this->setIconSizeString(' ' . $iconSizeString);
        $this->setIconSizeEm($iconSizeEm);

        $this->size = $size;
    }

    public function getIconSizeString(): string
    {
        return $this->iconSizeString;
    }

    public function setIconSizeString(string $iconSizeString): void
    {
        $this->iconSizeString = $iconSizeString;
    }

    public function getIconSizeEm(): string
    {
        return $this->iconSizeEm;
    }

    public function setIconSizeEm(string $iconSizeEm): void
    {
        $this->iconSizeEm = $iconSizeEm;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getIdStringForSvg(): string
    {
        if (!empty($this->getId())) {
            return ' id="' . $this->id . '"';
        }
        return '';
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    public function getAdditionalClasses(): string
    {
        if ($this->additionalClasses !== '') {
            return ' ' . $this->additionalClasses;
        }
        return $this->additionalClasses;
    }

    public function setAdditionalClasses(string $additionalClasses): void
    {
        $this->additionalClasses = $additionalClasses;
    }

    public function getIconStyle(): string
    {
        return $this->iconStyle;
    }

    public function setIconStyle(string $iconStyle): void
    {
        // Alias for ViewHelpers
        $subDirectory = match ($iconStyle) {
            'l' => 'light/',
            'r' => 'regular/',
            't' => 'thin/',
            'b' => 'brands/',
            'd' => 'duotone/',
            's-l' => 'sharp-light/',
            's-r' => 'sharp-regular/',
            's-s' => 'sharp-solid/',
            's-t' => 'sharp-thin/',
            default => '',
        };

        // If no style is set, use default from the mapping file.
        if ($subDirectory === '' && $iconStyle === '' && $this->defaultSubdirectory !== '') {
            $subDirectory = $this->defaultSubdirectory;
        }
        // if the user writes their own style but does not use an alias
        if ($subDirectory === '' && $iconStyle !== '') {
            $subDirectory = $iconStyle . '/';
        }

        $this->setSubdirectory($subDirectory);
        $this->iconStyle = $iconStyle;
    }

    public function getSubdirectory(): string
    {
        return $this->subdirectory;
    }

    public function setSubdirectory(string $subdirectory): void
    {
        $this->subdirectory = $subdirectory;
    }

    public function isAriaHidden(): ?bool
    {
        return $this->ariaHidden;
    }

    public function setAriaHidden(bool $ariaHidden): void
    {
        $this->ariaHidden = $ariaHidden;

        if ($ariaHidden === true && $this->role !== null) {
            // role is automatically removed with aria-hidden
            $this->role = null;
        }
    }

    public function getAriaLabel(): string
    {
        return $this->ariaLabel;
    }

    public function setAriaLabel(string $ariaLabel): void
    {
        $this->ariaLabel = $ariaLabel;
    }

    public function getAriaDescription(): string
    {
        return $this->ariaDescription;
    }

    public function setAriaDescription(string $ariaDescription): void
    {
        $this->ariaDescription = $ariaDescription;
    }

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function setRole(?string $role): void
    {
        if ($role !== null && !in_array($role, self::ALLOWED_ROLES, true)) {
            // silently ignore or throw exception
            $role = 'img';
        }
        $this->role = $role;
    }

    public function getAriaHiddenAttribute(): string
    {
        if ($this->ariaHidden === true) {
            return ' aria-hidden="true"';
        }
        return '';
    }

    public function getRoleAttribute(): string
    {
        if ($this->ariaHidden === true) {
            // If aria-hidden is active → no role output
            return '';
        }

        if (!empty($this->role)) {
            return ' role="' . htmlspecialchars($this->role, ENT_QUOTES) . '"';
        }

        // Standard role only when the icon is visible
        return ' role="img"';
    }

    public function isDecorative(): bool
    {
        return $this->ariaHidden === true;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getInline(): string
    {
        $svg = $this->loadIconFromDirectory();

        if (!$svg) {
            return '';
        }

        $addToSvgTag = '';
        $titleAndDescriptionTags = '';

        // prepare common vars, keep defaults to avoid notices
        $id = $this->getId();

        $titleId = '';
        $descriptionId = '';
        $titleTag = '';
        $descriptionTag = '';
        $ariaLabelledBy = '';
        $ariaDescribedBy = '';
        $ariaLabelAndDescription = '';

        if ($this->isAriaHidden() === true) {
            // decorative: hide from a11y tree
            $addToSvgTag = ' aria-hidden="true" focusable="false"';

            // optional browser tooltip (not announced by SR due to aria-hidden)
            if ($this->getTitle() !== '') {
                $titleAndDescriptionTags = '<title>' . htmlspecialchars(
                    $this->getTitle(),
                    ENT_QUOTES | ENT_SUBSTITUTE,
                    'UTF-8'
                ) . '</title>';
            }
        } else {
            // accessible icon
            // Preferred pattern: <title id="..."> + aria-labelledby, optional <desc id="..."> + aria-describedby
            $labelText = $this->getAriaLabel();
            $descText = $this->getAriaDescription();

            if ($labelText !== '') {
                $titleId = $id . '-title';
                $titleTag = '<title id="' . $titleId . '">' . htmlspecialchars(
                    $labelText,
                    ENT_QUOTES | ENT_SUBSTITUTE,
                    'UTF-8'
                ) . '</title>';
                $ariaLabelledBy = ' aria-labelledby="' . $titleId . '"';
            } elseif ($this->getTitle() !== '') {
                // fall back: if no aria-label was provided, use "title" as accessible name
                $titleId = $id . '-title';
                $titleTag = '<title id="' . $titleId . '">' . htmlspecialchars(
                    (string)$this->getTitle(),
                    ENT_QUOTES | ENT_SUBSTITUTE,
                    'UTF-8'
                ) . '</title>';
                $ariaLabelledBy = ' aria-labelledby="' . $titleId . '"';
            }

            if ($descText !== '') {
                $descriptionId = $id . '-desc';
                $descriptionTag = '<desc id="' . $descriptionId . '">' . htmlspecialchars(
                    $descText,
                    ENT_QUOTES | ENT_SUBSTITUTE,
                    'UTF-8'
                ) . '</desc>';
                $ariaDescribedBy = ' aria-describedby="' . $descriptionId . '"';
            }

            // // If neither label nor title was provided, allow plain aria-label as a fallback
            // if ($titleId === '' && $labelText !== '') {
            //     // In most cases we already handled this via <title> + labelledby,
            //     // but if you prefer aria-label over <title>, you could swap logic here.
            //     // $ariaLabelAndDescription = ' aria-label="' . htmlspecialchars($labelText, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '"';
            // }

            // Combine all non-empty parts cleanly
            $addToSvgTag = trim(
                $this->getRoleAttribute() . // only add role="..." if necessary
                $ariaLabelledBy .
                $ariaDescribedBy .
                $ariaLabelAndDescription
            );
            if ($addToSvgTag !== '') {
                $addToSvgTag = ' ' . $addToSvgTag;
            }

            $titleAndDescriptionTags = $titleTag . $descriptionTag;
        }

        $iconString = str_replace(
            '<svg ',
            '<svg' . $this->getIdStringForSvg() . $addToSvgTag . ' class="ot-inline-icon ot-icon-id-' .
            $this->getIdentifier() . $this->getIconSizeString() . $this->getAdditionalClasses() . '" ',
            $svg
        );
        return self::insertTitleAndDescriptionTags($iconString, $titleAndDescriptionTags);
    }

    public function getBase64(): string
    {
        // todo return 'data:image/svg+xml;base64,' . base64_encode($this->getInline());
        return 'base64';
    }

    public function getCssClass(): string
    {
        // todo
        return 'todo: inline';
    }

    public function getSpriteCode(): string
    {
        return '<svg class="' . $this->getCssClass() . '"><use xlink:href="#' . $this->identifier . '"></use></svg>';
    }

    private function loadIconFromDirectory(): ?string
    {
        $basePath = rtrim($this->settings['iconDirectory'], '/');
        $subDir = ltrim($this->getSubdirectory(), '/');
        $directory = $basePath . '/' . $subDir;

        if (str_starts_with($directory, 'EXT:')) {
            $directory = GeneralUtility::getFileAbsFileName($directory);
        }

        $iconPath = $directory . $this->getIdentifier() . '.svg';

        // 1️⃣ Primary path: current style (solid, regular, etc.)
        if (is_file($iconPath)) {
            $img = @file_get_contents($iconPath);
            if ($img !== false) {
                return $img;
            }
        }

        // 2️⃣ Fallback: Brands directory at base level (not within "regular/")
        $brandPath = rtrim($basePath, '/') . '/brands/' . $this->getIdentifier() . '.svg';
        if (str_starts_with($brandPath, 'EXT:')) {
            $brandPath = GeneralUtility::getFileAbsFileName($brandPath);
        }
        if (is_file($brandPath)) {
            $img = @file_get_contents($brandPath);
            if ($img !== false) {
                return $img;
            }
        }

        // SVG not found
        return '<svg class="ot-inline-icon ot-2x" xmlns="http://www.w3.org/2000/svg" style="fill: red;" viewBox="0 0 576 512">
                <title>SVG with Identifier ' . $this->getIdentifier() . ' not found in path: ' . $iconPath . '</title>
                <path d="M569.517 440.013C587.975 472.007 564.806 512 527.94 512H48.054c-36.937 0-59.999-40.055-41.577-71.987L246.423 23.985c18.467-32.009 64.72-31.951 83.154 0l239.94 416.028zM288 354c-25.405 0-46 20.595-46 46s20.595 46 46 46 46-20.595 46-46-20.595-46-46-46zm-43.673-165.346l7.418 136c.347 6.364 5.609 11.346 11.982 11.346h48.546c6.373 0 11.635-4.982 11.982-11.346l7.418-136c.375-6.874-5.098-12.654-11.982-12.654h-63.383c-6.884 0-12.356 5.78-11.981 12.654z"/>
            </svg>';
    }

    /**
     * @param string $iconString
     * @param string $titleAndDescriptionTags
     * @return string
     */
    private static function insertTitleAndDescriptionTags(string $iconString, string $titleAndDescriptionTags): string
    {
        return preg_replace(
            '/\<svg (.*)\>(.*)<\/svg>/mU',
            '<svg ${1}>' . $titleAndDescriptionTags . '${2}</svg>',
            $iconString
        );
    }
}
