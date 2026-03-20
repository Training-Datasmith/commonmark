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

use League\Common_Mark\Delimiter\Delimiter_Interface;
use League\Common_Mark\Node\Inline\Abstract_String_Container;
/**
 * Interface for a delimiter processor
 */
interface Delimiter_Processor_Interface
{
    /**
     * Returns the character that marks the beginning of a delimited node.
     *
     * This must not clash with any other processors being added to the environment.
     */
    public function get_opening_character(): string;
    /**
     * Returns the character that marks the ending of a delimited node.
     *
     * This must not clash with any other processors being added to the environment.
     *
     * Note that for a symmetric delimiter such as "*", this is the same as the opening.
     */
    public function get_closing_character(): string;
    /**
     * Minimum number of delimiter characters that are needed to active this.
     *
     * Must be at least 1.
     */
    public function get_min_length(): int;
    /**
     * Determine how many (if any) of the delimiter characters should be used.
     *
     * This allows implementations to decide how many characters to be used
     * based on the properties of the delimiter runs. An implementation can also
     * return 0 when it doesn't want to allow this particular combination of
     * delimiter runs.
     *
     * IMPORTANT: Unless this method returns the same hard-coded value in all cases,
     * you MUST implement the CacheableDelimiterProcessorInterface interface instead.
     *
     * @param DelimiterInterface $opener The opening delimiter run
     * @param DelimiterInterface $closer The closing delimiter run
     */
    public function get_delimiter_use(Delimiter_Interface $opener, Delimiter_Interface $closer): int;
    /**
     * Process the matched delimiters, e.g. by wrapping the nodes between opener
     * and closer in a new node, or appending a new node after the opener.
     *
     * Note that removal of the delimiter from the delimiter nodes and detaching
     * them is done by the caller.
     *
     * @param AbstractStringContainer $opener       The node that contained the opening delimiter
     * @param AbstractStringContainer $closer       The node that contained the closing delimiter
     * @param int                     $delimiterUse The number of delimiters that were used
     */
    public function process(Abstract_String_Container $opener, Abstract_String_Container $closer, int $delimiter_use): void;
}