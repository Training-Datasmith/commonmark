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
namespace League\Common_Mark\Extension\Inlines_Only;

use League\Common_Mark as Core;
use League\Common_Mark\Environment\Environment_Builder_Interface;
use League\Common_Mark\Extension\Common_Mark;
use League\Common_Mark\Extension\Common_Mark\Delimiter\Processor\Emphasis_Delimiter_Processor;
use League\Common_Mark\Extension\Configurable_Extension_Interface;
use League\Config\Configuration_Builder_Interface;
use Nette\Schema\Expect;
final class Inlines_Only_Extension implements Configurable_Extension_Interface
{
    public function configure_schema(Configuration_Builder_Interface $builder): void
    {
        $builder->add_schema('commonmark', Expect::structure(['use_asterisk' => Expect::bool(true), 'use_underscore' => Expect::bool(true), 'enable_strong' => Expect::bool(true), 'enable_em' => Expect::bool(true)]));
    }
    // phpcs:disable Generic.Functions.FunctionCallArgumentSpacing.TooMuchSpaceAfterComma,Squiz.WhiteSpace.SemicolonSpacing.Incorrect
    public function register(Environment_Builder_Interface $environment): void
    {
        $child_renderer = new Child_Renderer();
        $environment->add_inline_parser(new Core\Parser\Inline\Newline_Parser(), 200)->add_inline_parser(new Common_Mark\Parser\Inline\Backtick_Parser(), 150)->add_inline_parser(new Common_Mark\Parser\Inline\Escapable_Parser(), 80)->add_inline_parser(new Common_Mark\Parser\Inline\Entity_Parser(), 70)->add_inline_parser(new Common_Mark\Parser\Inline\Autolink_Parser(), 50)->add_inline_parser(new Common_Mark\Parser\Inline\Html_Inline_Parser(), 40)->add_inline_parser(new Common_Mark\Parser\Inline\Close_Bracket_Parser(), 30)->add_inline_parser(new Common_Mark\Parser\Inline\Open_Bracket_Parser(), 20)->add_inline_parser(new Common_Mark\Parser\Inline\Bang_Parser(), 10)->add_renderer(Core\Node\Block\Document::class, $child_renderer, 0)->add_renderer(Core\Node\Block\Paragraph::class, $child_renderer, 0)->add_renderer(Common_Mark\Node\Inline\Code::class, new Common_Mark\Renderer\Inline\Code_Renderer(), 0)->add_renderer(Common_Mark\Node\Inline\Emphasis::class, new Common_Mark\Renderer\Inline\Emphasis_Renderer(), 0)->add_renderer(Common_Mark\Node\Inline\Html_Inline::class, new Common_Mark\Renderer\Inline\Html_Inline_Renderer(), 0)->add_renderer(Common_Mark\Node\Inline\Image::class, new Common_Mark\Renderer\Inline\Image_Renderer(), 0)->add_renderer(Common_Mark\Node\Inline\Link::class, new Common_Mark\Renderer\Inline\Link_Renderer(), 0)->add_renderer(Core\Node\Inline\Newline::class, new Core\Renderer\Inline\Newline_Renderer(), 0)->add_renderer(Common_Mark\Node\Inline\Strong::class, new Common_Mark\Renderer\Inline\Strong_Renderer(), 0)->add_renderer(Core\Node\Inline\Text::class, new Core\Renderer\Inline\Text_Renderer(), 0);
        if ($environment->get_configuration()->get('commonmark/use_asterisk')) {
            $environment->add_delimiter_processor(new Emphasis_Delimiter_Processor('*'));
        }
        if ($environment->get_configuration()->get('commonmark/use_underscore')) {
            $environment->add_delimiter_processor(new Emphasis_Delimiter_Processor('_'));
        }
    }
}