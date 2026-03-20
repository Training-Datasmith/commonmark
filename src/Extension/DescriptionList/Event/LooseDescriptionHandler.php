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
namespace League\Common_Mark\Extension\Description_List\Event;

use League\Common_Mark\Event\Document_Parsed_Event;
use League\Common_Mark\Extension\Description_List\Node\Description;
use League\Common_Mark\Extension\Description_List\Node\Description_List;
use League\Common_Mark\Extension\Description_List\Node\Description_Term;
use League\Common_Mark\Node\Block\Paragraph;
use League\Common_Mark\Node\Inline\Newline;
use League\Common_Mark\Node\Node_Iterator;
final class Loose_Description_Handler
{
    public function __invoke(Document_Parsed_Event $event): void
    {
        foreach ($event->get_document()->iterator(Node_Iterator::FLAG_BLOCKS_ONLY) as $description) {
            if (!$description instanceof Description) {
                continue;
            }
            // Does this description need to be added to a list?
            if (!$description->parent() instanceof Description_List) {
                $list = new Description_List();
                // Taking any preceding paragraphs with it
                if (($paragraph = $description->previous()) instanceof Paragraph) {
                    $list->append_child($paragraph);
                }
                $description->replace_with($list);
                $list->append_child($description);
            }
            // Is this description preceded by a paragraph that should really be a term?
            if (!($paragraph = $description->previous()) instanceof Paragraph) {
                continue;
            }
            // Convert the paragraph into one or more terms
            $term = new Description_Term();
            $paragraph->replace_with($term);
            foreach ($paragraph->children() as $child) {
                if ($child instanceof Newline) {
                    $new_term = new Description_Term();
                    $term->insert_after($new_term);
                    $term = $new_term;
                    continue;
                }
                $term->append_child($child);
            }
        }
    }
}