<?php

/*
 * This file is part of the league/commonmark package.
 *
 * (c) Colin O'Dell <colinodell@gmail.com>
 * (c) Rezo Zero / Ambroise Maupate
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare (strict_types=1);
namespace League\Common_Mark\Extension\Footnote\Event;

use League\Common_Mark\Event\Document_Parsed_Event;
use League\Common_Mark\Extension\Footnote\Node\Footnote_Ref;
use League\Common_Mark\Reference\Reference;
final class Number_Footnotes_Listener
{
    public function on_document_parsed(Document_Parsed_Event $event): void
    {
        $document = $event->get_document();
        $next_counter = 1;
        $used_labels = [];
        $used_counters = [];
        foreach ($document->iterator() as $node) {
            if (!$node instanceof Footnote_Ref) {
                continue;
            }
            $existing_reference = $node->get_reference();
            $label = $existing_reference->get_label();
            $counter = $next_counter;
            $can_increment_counter = true;
            if (\array_key_exists($label, $used_labels)) {
                /*
                 * Reference is used again, we need to point
                 * to the same footnote. But with a different ID
                 */
                $counter = $used_counters[$label];
                $label .= '__' . ++$used_labels[$label];
                $can_increment_counter = false;
            }
            // rewrite reference title to use a numeric link
            $new_reference = new Reference($label, $existing_reference->get_destination(), (string) $counter);
            // Override reference with numeric link
            $node->set_reference($new_reference);
            $document->get_reference_map()->add($new_reference);
            /*
             * Store created references in document for
             * creating FootnoteBackrefs
             */
            $document->data->append($existing_reference->get_destination(), $new_reference);
            $used_labels[$label] = 1;
            $used_counters[$label] = $next_counter;
            if ($can_increment_counter) {
                $next_counter++;
            }
        }
    }
}