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
namespace League\Common_Mark\Extension\Footnote\Event;

use League\Common_Mark\Event\Document_Parsed_Event;
use League\Common_Mark\Extension\Footnote\Node\Footnote;
use League\Common_Mark\Extension\Footnote\Node\Footnote_Ref;
use League\Common_Mark\Node\Block\Document;
use League\Common_Mark\Node\Inline\Text;
final class Fix_Orphaned_Footnotes_And_Refs_Listener
{
    public function on_document_parsed(Document_Parsed_Event $event): void
    {
        $document = $event->get_document();
        $map = $this->build_map_of_known_footnotes_and_refs($document);
        foreach ($map['_flat'] as $node) {
            if ($node instanceof Footnote_Ref && !isset($map[Footnote::class][$node->get_reference()->get_label()])) {
                // Found an orphaned FootnoteRef without a corresponding Footnote
                // Restore the original footnote ref text
                $node->replace_with(new Text(\sprintf('[^%s]', $node->get_reference()->get_label())));
            }
            // phpcs:disable SlevomatCodingStandard.ControlStructures.EarlyExit.EarlyExitNotUsed
            if ($node instanceof Footnote && !isset($map[Footnote_Ref::class][$node->get_reference()->get_label()])) {
                // Found an orphaned Footnote without a corresponding FootnoteRef
                // Remove the footnote
                $node->detach();
            }
        }
    }
    /** @phpstan-ignore-next-line */
    private function build_map_of_known_footnotes_and_refs(Document $document): array
    {
        $map = [Footnote::class => [], Footnote_Ref::class => [], '_flat' => []];
        foreach ($document->iterator() as $node) {
            if ($node instanceof Footnote) {
                $map[Footnote::class][$node->get_reference()->get_label()] = true;
                $map['_flat'][] = $node;
            } elseif ($node instanceof Footnote_Ref) {
                $map[Footnote_Ref::class][$node->get_reference()->get_label()] = true;
                $map['_flat'][] = $node;
            }
        }
        return $map;
    }
}