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
namespace League\Common_Mark\Event;

use League\Common_Mark\Input\Markdown_Input_Interface;
use League\Common_Mark\Node\Block\Document;
/**
 * Event dispatched when the document is about to be parsed
 */
final class Document_Pre_Parsed_Event extends Abstract_Event
{
    /** @psalm-readonly */
    private Document $document;
    private Markdown_Input_Interface $markdown;
    public function __construct(Document $document, Markdown_Input_Interface $markdown)
    {
        $this->document = $document;
        $this->markdown = $markdown;
    }
    public function get_document(): Document
    {
        return $this->document;
    }
    public function get_markdown(): Markdown_Input_Interface
    {
        return $this->markdown;
    }
    public function replace_markdown(Markdown_Input_Interface $markdown_input): void
    {
        $this->markdown = $markdown_input;
    }
}