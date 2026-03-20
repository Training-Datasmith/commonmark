<?php

declare (strict_types=1);
/*
 * This file is part of the league/commonmark package.
 *
 * (c) Colin O'Dell <colinodell@gmail.com>
 *
 * Original code based on the CommonMark JS reference parser (https://bitly.com/commonmark-js)
 *  - (c) John MacFarlane
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace League\Common_Mark\Extension\Common_Mark;

use League\Common_Mark\Environment\Environment_Builder_Interface;
use League\Common_Mark\Extension\Common_Mark\Delimiter\Processor\Emphasis_Delimiter_Processor;
use League\Common_Mark\Extension\Configurable_Extension_Interface;
use League\Common_Mark\Node as CoreNode;
use League\Common_Mark\Parser as CoreParser;
use League\Common_Mark\Renderer as CoreRenderer;
use League\Config\Configuration_Builder_Interface;
use Nette\Schema\Expect;
final class Common_Mark_Core_Extension implements Configurable_Extension_Interface
{
    public function configure_schema(Configuration_Builder_Interface $builder): void
    {
        $builder->add_schema('commonmark', Expect::structure(['use_asterisk' => Expect::bool(true), 'use_underscore' => Expect::bool(true), 'enable_strong' => Expect::bool(true), 'enable_em' => Expect::bool(true), 'unordered_list_markers' => Expect::list_of('string')->min(1)->default(['*', '+', '-'])->merge_defaults(false)]));
    }
    // phpcs:disable Generic.Functions.FunctionCallArgumentSpacing.TooMuchSpaceAfterComma,Squiz.WhiteSpace.SemicolonSpacing.Incorrect
    public function register(Environment_Builder_Interface $environment): void
    {
        $environment->add_block_start_parser(new Parser\Block\Block_Quote_Start_Parser(), 70)->add_block_start_parser(new Parser\Block\Heading_Start_Parser(), 60)->add_block_start_parser(new Parser\Block\Fenced_Code_Start_Parser(), 50)->add_block_start_parser(new Parser\Block\Html_Block_Start_Parser(), 40)->add_block_start_parser(new Parser\Block\Thematic_Break_Start_Parser(), 20)->add_block_start_parser(new Parser\Block\List_Block_Start_Parser(), 10)->add_block_start_parser(new Parser\Block\Indented_Code_Start_Parser(), -100)->add_inline_parser(new Core_Parser\Inline\Newline_Parser(), 200)->add_inline_parser(new Parser\Inline\Backtick_Parser(), 150)->add_inline_parser(new Parser\Inline\Escapable_Parser(), 80)->add_inline_parser(new Parser\Inline\Entity_Parser(), 70)->add_inline_parser(new Parser\Inline\Autolink_Parser(), 50)->add_inline_parser(new Parser\Inline\Html_Inline_Parser(), 40)->add_inline_parser(new Parser\Inline\Close_Bracket_Parser(), 30)->add_inline_parser(new Parser\Inline\Open_Bracket_Parser(), 20)->add_inline_parser(new Parser\Inline\Bang_Parser(), 10)->add_renderer(Node\Block\Block_Quote::class, new Renderer\Block\Block_Quote_Renderer(), 0)->add_renderer(Core_Node\Block\Document::class, new Core_Renderer\Block\Document_Renderer(), 0)->add_renderer(Node\Block\Fenced_Code::class, new Renderer\Block\Fenced_Code_Renderer(), 0)->add_renderer(Node\Block\Heading::class, new Renderer\Block\Heading_Renderer(), 0)->add_renderer(Node\Block\Html_Block::class, new Renderer\Block\Html_Block_Renderer(), 0)->add_renderer(Node\Block\Indented_Code::class, new Renderer\Block\Indented_Code_Renderer(), 0)->add_renderer(Node\Block\List_Block::class, new Renderer\Block\List_Block_Renderer(), 0)->add_renderer(Node\Block\List_Item::class, new Renderer\Block\List_Item_Renderer(), 0)->add_renderer(Core_Node\Block\Paragraph::class, new Core_Renderer\Block\Paragraph_Renderer(), 0)->add_renderer(Node\Block\Thematic_Break::class, new Renderer\Block\Thematic_Break_Renderer(), 0)->add_renderer(Node\Inline\Code::class, new Renderer\Inline\Code_Renderer(), 0)->add_renderer(Node\Inline\Emphasis::class, new Renderer\Inline\Emphasis_Renderer(), 0)->add_renderer(Node\Inline\Html_Inline::class, new Renderer\Inline\Html_Inline_Renderer(), 0)->add_renderer(Node\Inline\Image::class, new Renderer\Inline\Image_Renderer(), 0)->add_renderer(Node\Inline\Link::class, new Renderer\Inline\Link_Renderer(), 0)->add_renderer(Core_Node\Inline\Newline::class, new Core_Renderer\Inline\Newline_Renderer(), 0)->add_renderer(Node\Inline\Strong::class, new Renderer\Inline\Strong_Renderer(), 0)->add_renderer(Core_Node\Inline\Text::class, new Core_Renderer\Inline\Text_Renderer(), 0);
        if ($environment->get_configuration()->get('commonmark/use_asterisk')) {
            $environment->add_delimiter_processor(new Emphasis_Delimiter_Processor('*'));
        }
        if ($environment->get_configuration()->get('commonmark/use_underscore')) {
            $environment->add_delimiter_processor(new Emphasis_Delimiter_Processor('_'));
        }
    }
}