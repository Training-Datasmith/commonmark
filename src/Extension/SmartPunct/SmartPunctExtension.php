<?php

declare (strict_types=1);
/*
 * This file is part of the league/commonmark package.
 *
 * (c) Colin O'Dell <colinodell@gmail.com>
 *
 * Original code based on the CommonMark JS reference parser (http://bitly.com/commonmark-js)
 *  - (c) John MacFarlane
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace League\Common_Mark\Extension\Smart_Punct;

use League\Common_Mark\Environment\Environment_Builder_Interface;
use League\Common_Mark\Event\Document_Parsed_Event;
use League\Common_Mark\Extension\Configurable_Extension_Interface;
use League\Common_Mark\Node\Block\Document;
use League\Common_Mark\Node\Block\Paragraph;
use League\Common_Mark\Node\Inline\Text;
use League\Common_Mark\Renderer\Block as CoreBlockRenderer;
use League\Common_Mark\Renderer\Inline as CoreInlineRenderer;
use League\Config\Configuration_Builder_Interface;
use Nette\Schema\Expect;
final class Smart_Punct_Extension implements Configurable_Extension_Interface
{
    public function configure_schema(Configuration_Builder_Interface $builder): void
    {
        $builder->add_schema('smartpunct', Expect::structure(['double_quote_opener' => Expect::string(Quote::DOUBLE_QUOTE_OPENER), 'double_quote_closer' => Expect::string(Quote::DOUBLE_QUOTE_CLOSER), 'single_quote_opener' => Expect::string(Quote::SINGLE_QUOTE_OPENER), 'single_quote_closer' => Expect::string(Quote::SINGLE_QUOTE_CLOSER)]));
    }
    public function register(Environment_Builder_Interface $environment): void
    {
        $environment->add_inline_parser(new Quote_Parser(), 10)->add_inline_parser(new Dash_Parser(), 0)->add_inline_parser(new Ellipses_Parser(), 0)->add_delimiter_processor(Quote_Processor::create_double_quote_processor($environment->get_configuration()->get('smartpunct/double_quote_opener'), $environment->get_configuration()->get('smartpunct/double_quote_closer')))->add_delimiter_processor(Quote_Processor::create_single_quote_processor($environment->get_configuration()->get('smartpunct/single_quote_opener'), $environment->get_configuration()->get('smartpunct/single_quote_closer')))->add_event_listener(Document_Parsed_Event::class, new Replace_Unpaired_Quotes_Listener())->add_renderer(Document::class, new Core_Block_Renderer\Document_Renderer(), 0)->add_renderer(Paragraph::class, new Core_Block_Renderer\Paragraph_Renderer(), 0)->add_renderer(Text::class, new Core_Inline_Renderer\Text_Renderer(), 0);
    }
}