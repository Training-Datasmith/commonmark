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
namespace League\Common_Mark\Extension\Footnote\Parser;

use League\Common_Mark\Extension\Footnote\Node\Footnote;
use League\Common_Mark\Node\Block\Abstract_Block;
use League\Common_Mark\Parser\Block\Abstract_Block_Continue_Parser;
use League\Common_Mark\Parser\Block\Block_Continue;
use League\Common_Mark\Parser\Block\Block_Continue_Parser_Interface;
use League\Common_Mark\Parser\Cursor;
use League\Common_Mark\Reference\Reference_Interface;
final class Footnote_Parser extends Abstract_Block_Continue_Parser
{
    /** @psalm-readonly */
    private Footnote $block;
    /** @psalm-readonly-allow-private-mutation */
    private ?int $indentation = null;
    public function __construct(Reference_Interface $reference)
    {
        $this->block = new Footnote($reference);
    }
    public function get_block(): Footnote
    {
        return $this->block;
    }
    public function try_continue(Cursor $cursor, Block_Continue_Parser_Interface $active_block_parser): ?Block_Continue
    {
        if ($cursor->is_blank()) {
            return Block_Continue::at($cursor);
        }
        if ($cursor->is_indented()) {
            $this->indentation ??= $cursor->get_indent();
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