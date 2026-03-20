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
namespace League\Common_Mark\Extension\Description_List\Parser;

use League\Common_Mark\Extension\Description_List\Node\Description;
use League\Common_Mark\Node\Block\Abstract_Block;
use League\Common_Mark\Parser\Block\Abstract_Block_Continue_Parser;
use League\Common_Mark\Parser\Block\Block_Continue;
use League\Common_Mark\Parser\Block\Block_Continue_Parser_Interface;
use League\Common_Mark\Parser\Cursor;
final class Description_Continue_Parser extends Abstract_Block_Continue_Parser
{
    private Description $block;
    private int $indentation;
    public function __construct(bool $tight, int $indentation)
    {
        $this->block = new Description($tight);
        $this->indentation = $indentation;
    }
    public function get_block(): Description
    {
        return $this->block;
    }
    public function try_continue(Cursor $cursor, Block_Continue_Parser_Interface $active_block_parser): ?Block_Continue
    {
        if ($cursor->is_blank()) {
            if ($this->block->first_child() === null) {
                // Blank line after empty item
                return Block_Continue::none();
            }
            $cursor->advance_to_next_non_space_or_tab();
            return Block_Continue::at($cursor);
        }
        if ($cursor->get_indent() >= $this->indentation) {
            $cursor->advance_by($this->indentation, true);
            return Block_Continue::at($cursor);
        }
        return Block_Continue::none();
    }
    public function is_container(): bool
    {
        return true;
    }
    public function can_contain(Abstract_Block $child_block): bool
    {
        return true;
    }
}