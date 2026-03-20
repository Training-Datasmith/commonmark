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
namespace League\Common_Mark\Extension\Embed;

use League\Common_Mark\Event\Document_Parsed_Event;
use League\Common_Mark\Extension\Common_Mark\Node\Inline\Link;
use League\Common_Mark\Node\Block\Paragraph;
use League\Common_Mark\Node\Inline\Text;
use League\Common_Mark\Node\Node_Iterator;
final class Embed_Processor
{
    public const FALLBACK_REMOVE = 'remove';
    public const FALLBACK_LINK = 'link';
    private Embed_Adapter_Interface $adapter;
    private string $fallback;
    public function __construct(Embed_Adapter_Interface $adapter, string $fallback = self::FALLBACK_REMOVE)
    {
        $this->adapter = $adapter;
        $this->fallback = $fallback;
    }
    public function __invoke(Document_Parsed_Event $event): void
    {
        $document = $event->get_document();
        $embeds = [];
        foreach (new Node_Iterator($document) as $node) {
            if (!$node instanceof Embed) {
                continue;
            }
            if ($node->parent() !== $document) {
                $replacement = new Paragraph();
                $replacement->append_child(new Text($node->get_url()));
                $node->replace_with($replacement);
            } else {
                $embeds[] = $node;
            }
        }
        if ($embeds) {
            $this->adapter->update_embeds($embeds);
        }
        foreach ($embeds as $embed) {
            if ($embed->get_embed_code() !== null) {
                continue;
            }
            if ($this->fallback === self::FALLBACK_REMOVE) {
                $embed->detach();
            } elseif ($this->fallback === self::FALLBACK_LINK) {
                $paragraph = new Paragraph();
                $paragraph->append_child(new Link($embed->get_url(), $embed->get_url()));
                $embed->replace_with($paragraph);
            }
        }
    }
}