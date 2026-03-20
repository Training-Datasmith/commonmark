<?php

declare (strict_types=1);
/*
 * This file is part of the league/commonmark package.
 *
 * (c) Colin O'Dell <colinodell@gmail.com>
 *
 * Original code based on the CommonMark JS reference parser (https://bitly.com/commonmark-js)
 *  - (c) John MacFarlane
 *
 * Additional emphasis processing code based on commonmark-java (https://github.com/atlassian/commonmark-java)
 *  - (c) Atlassian Pty Ltd
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace League\Common_Mark\Delimiter\Processor;

use League\Common_Mark\Exception\InvalidArgumentException;
final class Delimiter_Processor_Collection implements Delimiter_Processor_Collection_Interface
{
    /**
     * @var array<string,DelimiterProcessorInterface>|DelimiterProcessorInterface[]
     *
     * @psalm-readonly-allow-private-mutation
     */
    private array $processors_by_char = [];
    public function add(Delimiter_Processor_Interface $processor): void
    {
        $opening = $processor->get_opening_character();
        $closing = $processor->get_closing_character();
        if ($opening === $closing) {
            $old = $this->processors_by_char[$opening] ?? null;
            if ($old !== null && $old->get_opening_character() === $old->get_closing_character()) {
                $this->add_staggered_delimiter_processor_for_char($opening, $old, $processor);
            } else {
                $this->add_delimiter_processor_for_char($opening, $processor);
            }
        } else {
            $this->add_delimiter_processor_for_char($opening, $processor);
            $this->add_delimiter_processor_for_char($closing, $processor);
        }
    }
    public function get_delimiter_processor(string $char): ?Delimiter_Processor_Interface
    {
        return $this->processors_by_char[$char] ?? null;
    }
    /**
     * @return string[]
     */
    public function get_delimiter_characters(): array
    {
        return \array_keys($this->processors_by_char);
    }
    private function add_delimiter_processor_for_char(string $delimiter_char, Delimiter_Processor_Interface $processor): void
    {
        if (isset($this->processors_by_char[$delimiter_char])) {
            throw new InvalidArgumentException(\sprintf('Delim processor for character "%s" already exists', $processor->get_opening_character()));
        }
        $this->processors_by_char[$delimiter_char] = $processor;
    }
    private function add_staggered_delimiter_processor_for_char(string $opening, Delimiter_Processor_Interface $old, Delimiter_Processor_Interface $new): void
    {
        if ($old instanceof Staggered_Delimiter_Processor) {
            $s = $old;
        } else {
            $s = new Staggered_Delimiter_Processor($opening, $old);
        }
        $s->add($new);
        $this->processors_by_char[$opening] = $s;
    }
    public function count(): int
    {
        return \count($this->processors_by_char);
    }
}