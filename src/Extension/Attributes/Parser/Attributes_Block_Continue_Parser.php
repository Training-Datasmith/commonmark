<?php

/*
 * This file is part of the league/commonmark package.
 *
 * (c) Colin O'Dell <colinodell@gmail.com>
 * (c) 2015 Martin Hasoň <martin.hason@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare (strict_types=1);
namespace League\Common_Mark\Extension\Attributes\Parser;

use League\Common_Mark\Extension\Attributes\Node\Attributes;
use League\Common_Mark\Extension\Attributes\Util\Attributes_Helper;
use League\Common_Mark\Node\Block\Abstract_Block;
use League\Common_Mark\Parser\Block\Abstract_Block_Continue_Parser;
use League\Common_Mark\Parser\Block\Block_Continue;
use League\Common_Mark\Parser\Block\Block_Continue_Parser_Interface;
use League\Common_Mark\Parser\Cursor;
final class Attributes_Block_Continue_Parser extends Abstract_Block_Continue_Parser
{
    private Attributes $block;
    private Abstract_Block $container;
    private bool $has_subsequent_line = false;
    /**
     * @param array<string, mixed> $attributes The attributes identified by the block start parser
     * @param AbstractBlock        $container  The node we were in when these attributes were discovered
     */
    public function __construct(array $attributes, Abstract_Block $container)
    {
        $this->block = new Attributes($attributes);
        $this->container = $container;
    }
    public function get_block(): \League\Common_Mark\Extension\Attributes\Node\Attributes
    {
        return $this->block;
    }
    public function try_continue(Cursor $cursor, Block_Continue_Parser_Interface $active_block_parser): ?Block_Continue
    {
        $this->has_subsequent_line = true;
        $cursor->advance_to_next_non_space_or_tab();
        // Does this next line also have attributes?
        $attributes = Attributes_Helper::parse_attributes($cursor);
        $cursor->advance_to_next_non_space_or_tab();
        if ($cursor->is_at_end() && $attributes !== []) {
            // It does! Merge them into what we parsed previously
            $this->block->set_attributes(Attributes_Helper::merge_attributes($this->block->get_attributes(), $attributes));
            // Tell the core parser we've consumed everything
            return Block_Continue::at($cursor);
        }
        // Okay, so there are no attributes on the next line
        // If this next line is blank we know we can't target the next node, it must be a previous one
        if ($cursor->is_blank()) {
            $this->block->set_target(Attributes::TARGET_PREVIOUS);
        }
        return Block_Continue::none();
    }
    public function close_block(): void
    {
        // Attributes appearing at the very end of the document won't have any last lines to check
        // so we can make that determination here
        if (!$this->has_subsequent_line) {
            $this->block->set_target(Attributes::TARGET_PREVIOUS);
        }
        // We know this block must apply to the "previous" block, but that could be a sibling or parent,
        // so we check the containing block to see which one it might be.
        if ($this->block->get_target() === Attributes::TARGET_PREVIOUS && $this->block->parent() === $this->container) {
            $this->block->set_target(Attributes::TARGET_PARENT);
        }
    }
}