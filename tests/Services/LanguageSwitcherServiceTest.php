<?php

namespace Jankx\Extensions\LanguageSwitcher\Tests\Services;

use Jankx\Extensions\LanguageSwitcher\Services\LanguageSwitcherService;
use PHPUnit\Framework\TestCase;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class LanguageSwitcherServiceTest extends TestCase
{
    protected $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new LanguageSwitcherService();
    }

    protected function tearDown(): void
    {
        unset($GLOBALS['mock_pll_current_language']);
        unset($GLOBALS['mock_pll_languages']);
        parent::tearDown();
    }

    public function testBootInitializesCurrentLanguage()
    {
        $GLOBALS['mock_pll_current_language'] = 'vi';

        $this->service->boot();

        $this->assertEquals('vi', $this->service->getCurrentLanguageCode());
    }

    public function testGetLanguagesReturnsEmptyWhenPolylangNotActive()
    {
        // When pll_the_languages doesn't exist
        $languages = $this->service->getLanguages();

        $this->assertIsArray($languages);
        $this->assertEmpty($languages);
    }

    public function testGetLanguagesWithPolylang()
    {
        $GLOBALS['mock_pll_languages'] = [
            [
                'slug' => 'vi',
                'name' => 'Tiếng Việt',
                'flag' => 'http://example.com/vi.png',
                'url' => 'http://example.com/vi/',
            ],
            [
                'slug' => 'en',
                'name' => 'English',
                'flag' => 'http://example.com/en.png',
                'url' => 'http://example.com/en/',
            ],
        ];

        $languages = $this->service->getLanguages();

        $this->assertCount(2, $languages);
        $this->assertEquals('vi', $languages[0]['slug']);
        $this->assertEquals('Tiếng Việt', $languages[0]['name']);
    }

    public function testGetLanguagesWithUrls()
    {
        $GLOBALS['mock_pll_languages'] = [
            ['slug' => 'vi', 'name' => 'Tiếng Việt'],
        ];

        $languages = $this->service->getLanguages(true);

        $this->assertNotEmpty($languages[0]['url']);
    }

    public function testGetCurrentLanguageReturnsNullWhenPolylangNotActive()
    {
        $current = $this->service->getCurrentLanguage();

        $this->assertNull($current);
    }

    public function testGetCurrentLanguage()
    {
        $GLOBALS['mock_pll_current_language'] = 'vi';
        $GLOBALS['mock_pll_languages'] = [
            [
                'slug' => 'vi',
                'name' => 'Tiếng Việt',
                'flag' => 'http://example.com/vi.png',
            ],
        ];

        $this->service->boot();
        $current = $this->service->getCurrentLanguage();

        $this->assertIsArray($current);
        $this->assertEquals('vi', $current['slug']);
        $this->assertEquals('Tiếng Việt', $current['name']);
    }

    public function testGetCurrentLanguageCode()
    {
        $GLOBALS['mock_pll_current_language'] = 'en';

        $this->service->boot();

        $this->assertEquals('en', $this->service->getCurrentLanguageCode());
    }

    public function testGetCurrentLanguageCodeReturnsEmptyWhenNotBooted()
    {
        $this->assertEquals('', $this->service->getCurrentLanguageCode());
    }
}
