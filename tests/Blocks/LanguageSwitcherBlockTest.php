<?php

namespace Jankx\Extensions\LanguageSwitcher\Tests\Blocks;

use Jankx\Extensions\LanguageSwitcher\Blocks\LanguageSwitcherBlock;
use Jankx\Extensions\LanguageSwitcher\Services\LanguageSwitcherService;
use PHPUnit\Framework\TestCase;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class LanguageSwitcherBlockTest extends TestCase
{
    public function testConstructorAcceptsNullService()
    {
        $block = new LanguageSwitcherBlock(null, '/path/to/block');

        // Should not throw exception
        $this->assertInstanceOf(LanguageSwitcherBlock::class, $block);
    }

    public function testConstructorAcceptsService()
    {
        $mockService = $this->createMock(LanguageSwitcherService::class);
        $block = new LanguageSwitcherBlock($mockService, '/path/to/block');

        $this->assertInstanceOf(LanguageSwitcherBlock::class, $block);
    }

    public function testLazyInitializationCreatesService()
    {
        // Create block without service
        $block = new LanguageSwitcherBlock(null);

        // Mock the language service behavior via reflection
        // We can't directly test getLanguageService as it's protected
        // But we can verify render() handles missing Polylang gracefully

        $attributes = [
            'showFlags' => true,
            'showNames' => true,
            'showCurrent' => true,
            'displayType' => 'dropdown',
            'className' => 'test-class',
        ];

        // When pll_the_languages doesn't exist, should return placeholder
        $result = $block->render($attributes);

        $this->assertStringContainsString('Polylang plugin is not active', $result);
    }

    public function testGetBlockId()
    {
        $block = new LanguageSwitcherBlock();

        $this->assertEquals('jankx/language-switcher', $block->getBlockId());
    }
}
