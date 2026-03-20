<?php

declare (strict_types=1);
/*
 * This file is part of the league/commonmark package.
 *
 * (c) Colin O'Dell <colinodell@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace League\Common_Mark\Extension\Disallowed_Raw_Html;

use League\Common_Mark\Environment\Environment_Builder_Interface;
use League\Common_Mark\Extension\Common_Mark\Node\Block\Html_Block;
use League\Common_Mark\Extension\Common_Mark\Node\Inline\Html_Inline;
use League\Common_Mark\Extension\Common_Mark\Renderer\Block\Html_Block_Renderer;
use League\Common_Mark\Extension\Common_Mark\Renderer\Inline\Html_Inline_Renderer;
use League\Common_Mark\Extension\Configurable_Extension_Interface;
use League\Config\Configuration_Builder_Interface;
use Nette\Schema\Expect;
final class Disallowed_Raw_Html_Extension implements Configurable_Extension_Interface
{
    private const DEFAULT_DISALLOWED_TAGS = ['title', 'textarea', 'style', 'xmp', 'iframe', 'noembed', 'noframes', 'script', 'plaintext'];
    public function configure_schema(Configuration_Builder_Interface $builder): void
    {
        $builder->add_schema('disallowed_raw_html', Expect::structure(['disallowed_tags' => Expect::list_of('string')->default(self::DEFAULT_DISALLOWED_TAGS)->merge_defaults(false)]));
    }
    public function register(Environment_Builder_Interface $environment): void
    {
        $environment->add_renderer(Html_Block::class, new Disallowed_Raw_Html_Renderer(new Html_Block_Renderer()), 50);
        $environment->add_renderer(Html_Inline::class, new Disallowed_Raw_Html_Renderer(new Html_Inline_Renderer()), 50);
    }
}