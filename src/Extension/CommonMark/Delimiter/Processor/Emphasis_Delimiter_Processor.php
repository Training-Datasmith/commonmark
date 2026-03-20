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
namespace League\Common_Mark\Extension\Common_Mark\Delimiter\Processor;

use League\Common_Mark\Delimiter\Delimiter_Interface;
use League\Common_Mark\Delimiter\Processor\Cacheable_Delimiter_Processor_Interface;
use League\Common_Mark\Extension\Common_Mark\Node\Inline\Emphasis;
use League\Common_Mark\Extension\Common_Mark\Node\Inline\Strong;
use League\Common_Mark\Node\Inline\Abstract_String_Container;
use League\Config\Configuration_Aware_Interface;
use League\Config\Configuration_Interface;
final class Emphasis_Delimiter_Processor implements Cacheable_Delimiter_Processor_Interface, Configuration_Aware_Interface
{
    /** @psalm-readonly */
    private string $char;
    /** @psalm-readonly-allow-private-mutation */
    private Configuration_Interface $config;
    /**
     * @param string $char The emphasis character to use (typically '*' or '_')
     */
    public function __construct(string $char)
    {
        $this->char = $char;
    }
    public function get_opening_character(): string
    {
        return $this->char;
    }
    public function get_closing_character(): string
    {
        return $this->char;
    }
    public function get_min_length(): int
    {
        return 1;
    }
    public function get_delimiter_use(Delimiter_Interface $opener, Delimiter_Interface $closer): int
    {
        // "Multiple of 3" rule for internal delimiter runs
        if (($opener->can_close() || $closer->can_open()) && $closer->get_original_length() % 3 !== 0 && ($opener->get_original_length() + $closer->get_original_length()) % 3 === 0) {
            return 0;
        }
        // Calculate actual number of delimiters used from this closer
        if ($opener->get_length() >= 2 && $closer->get_length() >= 2) {
            if ($this->config->get('commonmark/enable_strong')) {
                return 2;
            }
            return 0;
        }
        if ($this->config->get('commonmark/enable_em')) {
            return 1;
        }
        return 0;
    }
    public function process(Abstract_String_Container $opener, Abstract_String_Container $closer, int $delimiter_use): void
    {
        if ($delimiter_use === 1) {
            $emphasis = new Emphasis($this->char);
        } elseif ($delimiter_use === 2) {
            $emphasis = new Strong($this->char . $this->char);
        } else {
            return;
        }
        $next = $opener->next();
        while ($next !== null && $next !== $closer) {
            $tmp = $next->next();
            $emphasis->append_child($next);
            $next = $tmp;
        }
        $opener->insert_after($emphasis);
    }
    public function set_configuration(Configuration_Interface $configuration): void
    {
        $this->config = $configuration;
    }
    public function get_cache_key(Delimiter_Interface $closer): string
    {
        return \sprintf('%s-%s-%d-%d', $this->char, $closer->can_open() ? 'canOpen' : 'cannotOpen', $closer->get_original_length() % 3, $closer->get_length());
    }
}