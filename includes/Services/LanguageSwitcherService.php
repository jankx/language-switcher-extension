<?php

namespace Jankx\Extensions\LanguageSwitcher\Services;

/**
 * Language Switcher Service
 *
 * Provides language data for Polylang integration
 * Replaces the core App\Services\LanguageSwitcherService
 */
class LanguageSwitcherService
{
    /**
     * @var array
     */
    protected $languages = [];

    /**
     * @var string
     */
    protected $currentLanguage = '';

    protected $currentLanguageCode = null;

    /**
     * Boot service
     *
     * @return void
     */
    public function boot(): void
    {
        $this->initLanguages();
    }

    /**
     * Khởi tạo language switcher
     *
     * @return void
     */
    protected function initLanguages(): void
    {
        if (!function_exists('pll_the_languages')) {
            return;
        }

        $this->currentLanguageCode = pll_current_language('slug');
    }

    /**
     * Lấy danh sách ngôn ngữ
     *
     * @param bool $withUrl Có lấy URL không
     * @return array
     */
    public function getLanguages(bool $withUrl = false): array
    {
        if (!function_exists('pll_the_languages')) {
            return [];
        }

        $args = [
            'raw' => 1,
            'hide_if_empty' => 0,
        ];

        $languages = pll_the_languages($args);

        if (empty($languages) || !is_array($languages)) {
            return [];
        }

        if ($withUrl) {
            foreach ($languages as &$language) {
                $language['url'] = $this->getLanguageUrl($language['slug']);
            }
        }

        return $languages;
    }

    /**
     * Lấy URL của ngôn ngữ
     *
     * @param string $languageCode
     * @return string
     */
    protected function getLanguageUrl(string $languageCode): string
    {
        if (!function_exists('pll_home_url')) {
            return home_url();
        }

        return pll_home_url($languageCode);
    }

    /**
     * Lấy ngôn ngữ hiện tại
     *
     * @return array|null
     */
    public function getCurrentLanguage(): ?array
    {
        if (!function_exists('pll_current_language')) {
            return null;
        }

        $languages = $this->getLanguages(true);

        foreach ($languages as $language) {
            if ($language['slug'] === $this->currentLanguageCode) {
                return $language;
            }
        }

        return null;
    }

    /**
     * Lấy mã ngôn ngữ hiện tại
     *
     * @return string
     */
    public function getCurrentLanguageCode(): string
    {
        return $this->currentLanguageCode ?? '';
    }

    /**
     * Render language switcher
     *
     * @param array $args
     * @return string
     */
    public function render(array $args = []): string
    {
        if (!function_exists('pll_the_languages')) {
            return '';
        }

        $defaults = [
            'dropdown' => 0,
            'show_names' => 1,
            'show_flags' => 1,
            'hide_if_empty' => 0,
        ];

        $args = wp_parse_args($args, $defaults);

        return pll_the_languages($args);
    }
}
