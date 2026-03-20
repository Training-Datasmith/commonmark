<?php

/*
 * This file is part of the league/commonmark package.
 *
 * (c) Colin O'Dell <colinodell@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare (strict_types=1);
namespace League\Common_Mark\Extension\Front_Matter\Listener;

use League\Common_Mark\Event\Document_Pre_Parsed_Event;
use League\Common_Mark\Extension\Front_Matter\Front_Matter_Parser_Interface;
final class Front_Matter_Pre_Parser
{
    private Front_Matter_Parser_Interface $parser;
    public function __construct(Front_Matter_Parser_Interface $parser)
    {
        $this->parser = $parser;
    }
    public function __invoke(Document_Pre_Parsed_Event $event): void
    {
        $content = $event->get_markdown()->get_content();
        $parsed = $this->parser->parse($content);
        $event->get_document()->data->set('front_matter', $parsed->get_front_matter());
        $event->replace_markdown($parsed);
    }
}