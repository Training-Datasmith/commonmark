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

use League\Common_Mark\Node\Block\Document;
/**
 * Event dispatched just before rendering begins
 */
final class Document_Pre_Render_Event extends Abstract_Event
{
    /** @psalm-readonly */
    private Document $document;
    /** @psalm-readonly */
    private string $format;
    public function __construct(Document $document, string $format)
    {
        $this->document = $document;
        $this->format = $format;
    }
    public function get_document(): Document
    {
        return $this->document;
    }
    public function get_format(): string
    {
        return $this->format;
    }
}