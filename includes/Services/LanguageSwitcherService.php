<?php

namespace Jankx\Extensions\LanguageSwitcher\Services;

/**
 * Language Switcher Service
 *
 * Provides language data for Polylang integration.
 *
 * @package Jankx\Extensions\LanguageSwitcher\Services
 * @since 1.0.0
 */
class LanguageSwitcherService
{
    /**
     * Current language code
     *
     * @var string|null
     */
    protected $currentLanguageCode;

    /**
     * Boot service
     *
     * @return void
     */
    public function boot(): void
    {
        if (function_exists('pll_current_language')) {
            $currentLanguage = pll_current_language('slug');
            $this->currentLanguageCode = is_string($currentLanguage) && $currentLanguage !== '' ? $currentLanguage : null;
        }
    }

    /**
     * Get available languages
     *
     * @param bool $withUrl Whether to resolve a URL for each language
     * @return array List of normalized language data
     */
    public function getLanguages(bool $withUrl = false): array
    {
        if (!function_exists('pll_the_languages')) {
            return [];
        }

        $languages = pll_the_languages([
            'raw' => 1,
            'hide_if_empty' => 0,
        ]);

        if (empty($languages) || !is_array($languages)) {
            return [];
        }

        $result = [];
        foreach ($languages as $slug => $language) {
            $url = !empty($language['url']) ? $language['url'] : '';
            if ($withUrl && empty($url) && function_exists('pll_home_url')) {
                $url = pll_home_url($slug);
            }
            if (empty($url)) {
                $url = home_url();
            }

            $result[] = [
                'code' => $slug,
                'slug' => $slug,
                'name' => !empty($language['name']) ? $language['name'] : $slug,
                'url' => $url,
                'flag' => !empty($language['flag']) ? $language['flag'] : '',
                'current' => !empty($language['current_lang']),
            ];
        }

        return $result;
    }

    /**
     * Get current language data
     *
     * @return array|null
     */
    public function getCurrentLanguage(): ?array
    {
        $languages = $this->getLanguages(true);
        if (empty($languages)) {
            return null;
        }

        foreach ($languages as $language) {
            if (!empty($language['current'])) {
                return $language;
            }
        }

        $currentLanguageCode = $this->getCurrentLanguageCode();
        if ($currentLanguageCode !== '') {
            foreach ($languages as $language) {
                if ($language['code'] === $currentLanguageCode) {
                    return $language;
                }
            }
        }

        return null;
    }

    /**
     * Get current language code
     *
     * @return string
     */
    public function getCurrentLanguageCode(): string
    {
        if ($this->currentLanguageCode === null) {
            $this->boot();
        }

        return $this->currentLanguageCode ?? '';
    }
}
