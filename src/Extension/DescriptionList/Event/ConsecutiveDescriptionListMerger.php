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
use League\Common_Mark\Extension\Description_List\Node\Description_List;
use League\Common_Mark\Node\Node_Iterator;
final class Consecutive_Description_List_Merger
{
    public function __invoke(Document_Parsed_Event $event): void
    {
        foreach ($event->get_document()->iterator(Node_Iterator::FLAG_BLOCKS_ONLY) as $node) {
            if (!$node instanceof Description_List) {
                continue;
            }
            if (!($prev = $node->previous()) instanceof Description_List) {
                continue;
            }
            // There's another description list behind this one; merge the current one into that
            foreach ($node->children() as $child) {
                $prev->append_child($child);
            }
            $node->detach();
        }
    }
}