<?php

declare (strict_types=1);
/*
 * This file is part of the league/commonmark package.
 *
 * (c) Colin O'Dell <colinodell@gmail.com>
 *
 * Original code based on the CommonMark JS reference parser (http://bitly.com/commonmark-js)
 *  - (c) John MacFarlane
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace League\Common_Mark\Extension\Smart_Punct;

use League\Common_Mark\Delimiter\Delimiter_Interface;
use League\Common_Mark\Delimiter\Processor\Delimiter_Processor_Interface;
use League\Common_Mark\Node\Inline\Abstract_String_Container;
final class Quote_Processor implements Delimiter_Processor_Interface
{
    /** @psalm-readonly */
    private string $normalized_character;
    /** @psalm-readonly */
    private string $opener_character;
    /** @psalm-readonly */
    private string $closer_character;
    private function __construct(string $char, string $opener, string $closer)
    {
        $this->normalized_character = $char;
        $this->opener_character = $opener;
        $this->closer_character = $closer;
    }
    public function get_opening_character(): string
    {
        return $this->normalized_character;
    }
    public function get_closing_character(): string
    {
        return $this->normalized_character;
    }
    public function get_min_length(): int
    {
        return 1;
    }
    public function get_delimiter_use(Delimiter_Interface $opener, Delimiter_Interface $closer): int
    {
        return 1;
    }
    public function process(Abstract_String_Container $opener, Abstract_String_Container $closer, int $delimiter_use): void
    {
        $opener->insert_after(new Quote($this->opener_character));
        $closer->insert_before(new Quote($this->closer_character));
    }
    /**
     * Create a double-quote processor
     */
    public static function create_double_quote_processor(string $opener = Quote::DOUBLE_QUOTE_OPENER, string $closer = Quote::DOUBLE_QUOTE_CLOSER): self
    {
        return new self(Quote::DOUBLE_QUOTE, $opener, $closer);
    }
    /**
     * Create a single-quote processor
     */
    public static function create_single_quote_processor(string $opener = Quote::SINGLE_QUOTE_OPENER, string $closer = Quote::SINGLE_QUOTE_CLOSER): self
    {
        return new self(Quote::SINGLE_QUOTE, $opener, $closer);
    }
}