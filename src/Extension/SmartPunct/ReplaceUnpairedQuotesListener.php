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
namespace League\Common_Mark\Extension\Smart_Punct;

use League\Common_Mark\Event\Document_Parsed_Event;
use League\Common_Mark\Node\Inline\Adjacent_Text_Merger;
use League\Common_Mark\Node\Inline\Text;
use League\Common_Mark\Node\Query;
/**
 * Identifies any lingering Quote nodes that were missing pairs and converts them into Text nodes
 */
final class Replace_Unpaired_Quotes_Listener
{
    public function __invoke(Document_Parsed_Event $event): void
    {
        $query = (new Query())->where(Query::type(Quote::class));
        foreach ($query->find_all($event->get_document()) as $quote) {
            \assert($quote instanceof Quote);
            $literal = $quote->get_literal();
            if ($literal === Quote::SINGLE_QUOTE) {
                $literal = Quote::SINGLE_QUOTE_CLOSER;
            } elseif ($literal === Quote::DOUBLE_QUOTE) {
                $literal = Quote::DOUBLE_QUOTE_OPENER;
            }
            $quote->replace_with($new = new Text($literal));
            Adjacent_Text_Merger::merge_with_directly_adjacent_nodes($new);
        }
    }
}