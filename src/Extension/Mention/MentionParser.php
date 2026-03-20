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
namespace League\Common_Mark\Extension\Mention;

use League\Common_Mark\Extension\Mention\Generator\Callback_Generator;
use League\Common_Mark\Extension\Mention\Generator\Mention_Generator_Interface;
use League\Common_Mark\Extension\Mention\Generator\String_Template_Link_Generator;
use League\Common_Mark\Parser\Inline\Inline_Parser_Interface;
use League\Common_Mark\Parser\Inline\Inline_Parser_Match;
use League\Common_Mark\Parser\Inline_Parser_Context;
final class Mention_Parser implements Inline_Parser_Interface
{
    /** @psalm-readonly */
    private string $name;
    /** @psalm-readonly */
    private string $prefix;
    /** @psalm-readonly */
    private string $identifier_pattern;
    /** @psalm-readonly */
    private Mention_Generator_Interface $mention_generator;
    public function __construct(string $name, string $prefix, string $identifier_pattern, Mention_Generator_Interface $mention_generator)
    {
        $this->name = $name;
        $this->prefix = $prefix;
        $this->identifier_pattern = $identifier_pattern;
        $this->mention_generator = $mention_generator;
    }
    public function get_match_definition(): Inline_Parser_Match
    {
        return Inline_Parser_Match::join(Inline_Parser_Match::string($this->prefix), Inline_Parser_Match::regex($this->identifier_pattern));
    }
    public function parse(Inline_Parser_Context $inline_context): bool
    {
        $cursor = $inline_context->get_cursor();
        // The prefix must not have any other characters immediately prior
        $previous_char = $cursor->peek(-1);
        if ($previous_char !== null && \preg_match('/\w/', $previous_char)) {
            // peek() doesn't modify the cursor, so no need to restore state first
            return false;
        }
        [$prefix, $identifier] = $inline_context->get_sub_matches();
        $mention = $this->mention_generator->generate_mention(new Mention($this->name, $prefix, $identifier));
        if ($mention === null) {
            return false;
        }
        $cursor->advance_by($inline_context->get_full_match_length());
        $inline_context->get_container()->append_child($mention);
        return true;
    }
    public static function create_with_string_template(string $name, string $prefix, string $mention_regex, string $url_template): Mention_Parser
    {
        return new self($name, $prefix, $mention_regex, new String_Template_Link_Generator($url_template));
    }
    public static function create_with_callback(string $name, string $prefix, string $mention_regex, callable $callback): Mention_Parser
    {
        return new self($name, $prefix, $mention_regex, new Callback_Generator($callback));
    }
}