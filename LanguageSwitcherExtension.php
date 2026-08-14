<?php

namespace Jankx\Extensions\LanguageSwitcher;

use Jankx\Extensions\AbstractExtension;

class LanguageSwitcherExtension extends AbstractExtension
{
    public function init(): void
    {
        $app = \Jankx\Facades\App::getInstance();
        $provider = new \Jankx\Extensions\LanguageSwitcher\Services\LanguageSwitcherServiceProvider($app);
        $app->register($provider);
        $provider->boot($app);
    }

    public function register_hooks(): void
    {
        add_action('jankx/gutenberg/register-blocks', [$this, 'register_extension_blocks'], 10, 2);
    }

    public function register_extension_blocks($repository, $app): void
    {
        $blocks = ["LanguageSwitcherBlock"];

        foreach ($blocks as $blockClass) {
            $fullClass = 'Jankx\Extensions\LanguageSwitcher\\Blocks\\' . $blockClass;
            $block = new $fullClass();
            $blockId = basename($block->getBlockId());
            $block->setBlockPath($this->get_extension_path() . '/assets/blocks/' . $blockId);
            $repository->registerBlock($block);
        }
    }
}
