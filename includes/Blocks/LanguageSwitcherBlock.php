<?php

namespace Jankx\Extensions\LanguageSwitcher\Blocks;

use Jankx\Extensions\LanguageSwitcher\Services\LanguageSwitcherService;
use Jankx\Gutenberg\Block;

/**
 * Language Switcher Block
 *
 * This block displays language switcher for Polylang plugin
 * with customizable display options.
 *
 * @package Jankx\Gutenberg\Blocks
 * @since 1.0.0
 */
class LanguageSwitcherBlock extends Block
{
    /**
     * Block ID
     *
     * @var string
     */
    protected $blockId = 'jankx/language-switcher';

    /**
     * Block attributes
     *
     * @var array
     */
    protected $attributes = [];

    /**
     * Language service
     *
     * @var \Jankx\Extensions\LanguageSwitcher\Services\LanguageSwitcherService
     */
    protected $languageService;

    /**
     * Constructor
     *
     * @param LanguageSwitcherService|null $languageService
     * @param string|null $blockPath
     */
    public function __construct(?LanguageSwitcherService $languageService = null, $blockPath = null)
    {
        parent::__construct($blockPath);
        $this->languageService = $languageService;
    }

    /**
     * Get language service (lazy initialization)
     *
     * @return LanguageSwitcherService
     */
    protected function getLanguageService(): LanguageSwitcherService
    {
        if ($this->languageService === null) {
            $this->languageService = \Jankx\Facades\App::get('language-switcher');
            if (empty($this->languageService->getLanguages())) {
                $this->languageService->init();
            }
        }

        return $this->languageService;
    }

    /**
     * Render the block content
     *
     * @param array $attributes Block attributes
     * @param string $content Block content
     * @return string Rendered HTML
     */
    public function render($attributes, $content = '')
    {
        $this->attributes = $attributes;

        $showCurrent = $attributes['showCurrent'] ?? true;
        $displayType = $attributes['displayType'] ?? 'dropdown';
        $displayMode = $attributes['displayMode'] ?? 'text';
        $iconPosition = $attributes['iconPosition'] ?? 'left';
        $languageIcons = $attributes['languageIcons'] ?? [];
        $className = $attributes['className'] ?? '';

        if (!function_exists('pll_the_languages')) {
            return $this->renderPlaceholder();
        }

        $languages = $this->getLanguageService()->getLanguages(true);

        if (empty($languages)) {
            return $this->renderPlaceholder();
        }

        $wrapperClasses = ['language-switcher-block'];
        $wrapperClasses[] = 'ls-mode-' . sanitize_html_class($displayMode);
        if ($displayMode === 'icon_text') {
            $wrapperClasses[] = 'ls-icon-pos-' . sanitize_html_class($iconPosition);
        }
        if (!empty($className)) {
            $wrapperClasses[] = $className;
        }

        $switcherHtml = $this->renderLanguageSwitcher(
            $languages,
            $displayType,
            $displayMode,
            $iconPosition,
            $languageIcons,
            $showCurrent
        );

        return sprintf(
            '<div class="%s">%s</div>',
            esc_attr(implode(' ', $wrapperClasses)),
            $switcherHtml
        );
    }

    /**
     * Render language icon (custom SVG or Polylang flag)
     *
     * @param array $langData Language data
     * @param string $displayMode Display mode
     * @param array $languageIcons Custom icons map
     * @return string HTML
     */
    protected function renderLanguageIcon($langData, $displayMode, $languageIcons = [])
    {
        $code = $langData['code'] ?? '';
        $name = $langData['name'] ?? '';
        $flag = $langData['flag'] ?? '';

        $customSvg = $languageIcons[$code] ?? '';

        if (!empty($customSvg) && is_string($customSvg)) {
            return '<span class="language-icon language-icon-custom">' . $customSvg . '</span>';
        }

        if (!empty($flag) && (filter_var($flag, FILTER_VALIDATE_URL) || strpos($flag, 'data:image/') === 0)) {
            return sprintf(
                '<img src="%s" alt="%s" class="language-flag">',
                esc_attr($flag),
                esc_attr($name)
            );
        }

        return '';
    }

    /**
     * Render language name
     *
     * @param array $langData Language data
     * @return string HTML
     */
    protected function renderLanguageName($langData)
    {
        $name = $langData['name'] ?? '';
        if (empty($name)) {
            return '';
        }

        return sprintf(
            '<span class="language-name">%s</span>',
            esc_html($name)
        );
    }

    /**
     * Render language content based on displayMode
     *
     * @param array $langData Language data
     * @param string $displayMode Display mode
     * @param string $iconPosition Icon position (left/right)
     * @param array $languageIcons Custom icons map
     * @return string HTML
     */
    protected function renderLanguageContent($langData, $displayMode, $iconPosition, $languageIcons = [])
    {
        $icon = $this->renderLanguageIcon($langData, $displayMode, $languageIcons);
        $name = $this->renderLanguageName($langData);

        switch ($displayMode) {
            case 'icon_only':
                return $icon ?: $name;

            case 'icon_text':
                if ($iconPosition === 'right') {
                    return $name . $icon;
                }
                return $icon . $name;

            case 'text':
            default:
                return $name ?: $icon;
        }
    }

    /**
     * Render language switcher based on display type
     *
     * @param array $languages Available languages
     * @param string $displayType Display type (dropdown/list/flags)
     * @param string $displayMode Display mode (text/icon_only/icon_text)
     * @param string $iconPosition Icon position (left/right)
     * @param array $languageIcons Custom icons map
     * @param bool $showCurrent Show current language
     * @return string HTML
     */
    protected function renderLanguageSwitcher($languages, $displayType, $displayMode, $iconPosition, $languageIcons, $showCurrent)
    {
        if ($displayType === 'list') {
            return $this->renderList($languages, $displayMode, $iconPosition, $languageIcons, $showCurrent);
        } elseif ($displayType === 'flags') {
            return $this->renderFlags($languages, $displayMode, $iconPosition, $languageIcons, $showCurrent);
        }

        return $this->renderDropdown($languages, $displayMode, $iconPosition, $languageIcons, $showCurrent);
    }

    /**
     * Render dropdown style
     *
     * @param array $languages Available languages
     * @param string $displayMode Display mode
     * @param string $iconPosition Icon position
     * @param array $languageIcons Custom icons map
     * @param bool $showCurrent Show current language in dropdown
     * @return string HTML
     */
    protected function renderDropdown($languages, $displayMode, $iconPosition, $languageIcons, $showCurrent)
    {
        $currentLangData = $this->getLanguageService()->getCurrentLanguage();
        $currentLangData = apply_filters(
            'jankx/languages/current-language/data',
            $currentLangData
        );

        if (!is_array($currentLangData) || empty($currentLangData['code'])) {
            $currentLangData = null;
        }

        $languages = apply_filters('jankx/languages/data', $languages);

        if (!$showCurrent && $currentLangData) {
            $languages = array_filter($languages, function ($langData) use ($currentLangData) {
                return isset($langData['code']) && $langData['code'] !== $currentLangData['code'];
            });
        }

        $dropdownIcon = apply_filters('jankx/languages/switcher/dropdown/icon', '▼');
        $html = '<div class="language-switcher-dropdown-wrapper">';
        $html .= '<button class="language-switcher-dropdown" type="button">';

        if ($currentLangData) {
            $html .= $this->renderLanguageContent($currentLangData, $displayMode, $iconPosition, $languageIcons);
        }

        $html .= '<span class="language-arrow">' . $dropdownIcon . '</span>';
        $html .= '</button>';

        $html .= '<ul class="language-switcher-dropdown-menu">';
        foreach ($languages as $langData) {
            if (!is_array($langData) || empty($langData['code'])) {
                continue;
            }

            $isCurrent = $currentLangData && $langData['code'] === $currentLangData['code'];
            $itemClasses = ['language-dropdown-item'];
            if ($isCurrent) {
                $itemClasses[] = 'current-language';
            }

            $html .= sprintf('<li class="%s">', esc_attr(implode(' ', $itemClasses)));
            $html .= sprintf('<a href="%s" class="language-dropdown-link">', esc_url($langData['url']));
            $html .= $this->renderLanguageContent($langData, $displayMode, $iconPosition, $languageIcons);
            $html .= '</a></li>';
        }
        $html .= '</ul></div>';

        return $html;
    }

    /**
     * Render list style
     *
     * @param array $languages Available languages
     * @param string $displayMode Display mode
     * @param string $iconPosition Icon position
     * @param array $languageIcons Custom icons map
     * @param bool $showCurrent Show current language in list
     * @return string HTML
     */
    protected function renderList($languages, $displayMode, $iconPosition, $languageIcons, $showCurrent)
    {
        $currentLangData = $this->getLanguageService()->getCurrentLanguage();

        if (!is_array($currentLangData) || empty($currentLangData['code'])) {
            $currentLangData = null;
        }

        if (!$showCurrent && $currentLangData) {
            $languages = array_filter($languages, function ($langData) use ($currentLangData) {
                return isset($langData['code']) && $langData['code'] !== $currentLangData['code'];
            });
        }

        $html = '<ul class="language-switcher-list">';
        foreach ($languages as $langData) {
            if (!is_array($langData) || empty($langData['code'])) {
                continue;
            }

            $isCurrent = $currentLangData && $langData['code'] === $currentLangData['code'];
            $itemClasses = ['language-item'];
            if ($isCurrent) {
                $itemClasses[] = 'current-language';
            }

            $html .= sprintf('<li class="%s">', esc_attr(implode(' ', $itemClasses)));
            $html .= sprintf('<a href="%s" class="language-link">', esc_url($langData['url']));
            $html .= $this->renderLanguageContent($langData, $displayMode, $iconPosition, $languageIcons);
            $html .= '</a></li>';
        }
        $html .= '</ul>';

        return $html;
    }

    /**
     * Render flags only style
     *
     * @param array $languages Available languages
     * @param string $displayMode Display mode
     * @param string $iconPosition Icon position
     * @param array $languageIcons Custom icons map
     * @param bool $showCurrent Show current language in flags
     * @return string HTML
     */
    protected function renderFlags($languages, $displayMode, $iconPosition, $languageIcons, $showCurrent)
    {
        $currentLangData = $this->getLanguageService()->getCurrentLanguage();

        if (!is_array($currentLangData) || empty($currentLangData['code'])) {
            $currentLangData = null;
        }

        if (!$showCurrent && $currentLangData) {
            $languages = array_filter($languages, function ($langData) use ($currentLangData) {
                return isset($langData['code']) && $langData['code'] !== $currentLangData['code'];
            });
        }

        $html = '<div class="language-switcher-flags">';
        foreach ($languages as $langData) {
            if (!is_array($langData) || empty($langData['code'])) {
                continue;
            }

            $isCurrent = $currentLangData && $langData['code'] === $currentLangData['code'];
            $itemClasses = ['language-flag-item'];
            if ($isCurrent) {
                $itemClasses[] = 'current-language';
            }

            $html .= sprintf('<div class="%s">', esc_attr(implode(' ', $itemClasses)));
            $html .= sprintf(
                '<a href="%s" class="language-flag-link" title="%s">',
                esc_url($langData['url']),
                esc_attr($langData['name'] ?? '')
            );
            $html .= $this->renderLanguageContent($langData, $displayMode, $iconPosition, $languageIcons);
            $html .= '</a></div>';
        }
        $html .= '</div>';

        return $html;
    }

    /**
     * Render placeholder when Polylang is not available
     *
     * @return string
     */
    protected function renderPlaceholder()
    {
        return '<div class="polylang-placeholder">' .
               '<p>' . __('Polylang plugin is not active. Language switcher cannot be displayed.', 'jankx') . '</p>' .
               '</div>';
    }
}
