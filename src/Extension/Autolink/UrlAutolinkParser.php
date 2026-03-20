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
namespace League\Common_Mark\Extension\Autolink;

use League\Common_Mark\Extension\Common_Mark\Node\Inline\Link;
use League\Common_Mark\Parser\Inline\Inline_Parser_Interface;
use League\Common_Mark\Parser\Inline\Inline_Parser_Match;
use League\Common_Mark\Parser\Inline_Parser_Context;
final class Url_Autolink_Parser implements Inline_Parser_Interface
{
    private const ALLOWED_AFTER = [null, ' ', "\t", "\n", "\v", "\f", "\r", '*', '_', '~', '('];
    // RegEx adapted from https://github.com/symfony/symfony/blob/6.3/src/Symfony/Component/Validator/Constraints/UrlValidator.php
    private const REGEX = '~^
        (
            # Must start with a supported scheme + auth, or "www"
            (?:
                (?:%s)://                                                                            # protocol
                (?:(?:(?:[\_\.\pL\pN-]|%%[0-9A-Fa-f]{2})+:)?((?:[\_\.\pL\pN-]|%%[0-9A-Fa-f]{2})+)@)? # basic auth
            |www\.)
            (?:
                (?:
                    (?:xn--[a-z0-9-]++\.)*+xn--[a-z0-9-]++            # a domain name using punycode
                        |
                    (?:[\pL\pN\pS\pM\-\_]++\.){1,127}[\pL\pN\pM]++    # a multi-level domain name; total length must be 253 bytes or less
                        |
                    [a-z0-9\-\_]++                                    # a single-level domain name
                )\.?
                    |                                                 # or
                \d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}                    # an IP address
                    |                                                 # or
                \[
                    (?:(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){6})(?:(?:(?:(?:(?:[0-9a-f]{1,4})):(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:::(?:(?:(?:[0-9a-f]{1,4})):){5})(?:(?:(?:(?:(?:[0-9a-f]{1,4})):(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:(?:(?:(?:[0-9a-f]{1,4})))?::(?:(?:(?:[0-9a-f]{1,4})):){4})(?:(?:(?:(?:(?:[0-9a-f]{1,4})):(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){0,1}(?:(?:[0-9a-f]{1,4})))?::(?:(?:(?:[0-9a-f]{1,4})):){3})(?:(?:(?:(?:(?:[0-9a-f]{1,4})):(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){0,2}(?:(?:[0-9a-f]{1,4})))?::(?:(?:(?:[0-9a-f]{1,4})):){2})(?:(?:(?:(?:(?:[0-9a-f]{1,4})):(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){0,3}(?:(?:[0-9a-f]{1,4})))?::(?:(?:[0-9a-f]{1,4})):)(?:(?:(?:(?:(?:[0-9a-f]{1,4})):(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){0,4}(?:(?:[0-9a-f]{1,4})))?::)(?:(?:(?:(?:(?:[0-9a-f]{1,4})):(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){0,5}(?:(?:[0-9a-f]{1,4})))?::)(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){0,6}(?:(?:[0-9a-f]{1,4})))?::))))
                \]  # an IPv6 address
            )
            (?::[0-9]+)?                              # a port (optional)
            (?:/ (?:[\pL\pN\-._\~!$&\'()*+,;=:@]|%%[0-9A-Fa-f]{2})* )*        # a path
            (?:\? (?:[\pL\pN\-._\~!$&\'\[\]()*+,;=:@/?]|%%[0-9A-Fa-f]{2})* )? # a query (optional)
            (?:\# (?:[\pL\pN\-._\~!$&\'()*+,;=:@/?]|%%[0-9A-Fa-f]{2})* )?     # a fragment (optional)
        )~ixu';
    /**
     * @var string[]
     *
     * @psalm-readonly
     */
    private array $prefixes = ['www.'];
    /**
     * @psalm-var non-empty-string
     *
     * @psalm-readonly
     */
    private string $final_regex;
    private string $default_protocol;
    /**
     * @param array<int, string> $allowedProtocols
     */
    public function __construct(array $allowed_protocols = ['http', 'https', 'ftp'], string $default_protocol = 'http')
    {
        /**
         * @psalm-suppress PropertyTypeCoercion
         */
        $this->final_regex = \sprintf(self::REGEX, \implode('|', $allowed_protocols));
        foreach ($allowed_protocols as $protocol) {
            $this->prefixes[] = $protocol . '://';
        }
        $this->default_protocol = $default_protocol;
    }
    public function get_match_definition(): Inline_Parser_Match
    {
        return Inline_Parser_Match::one_of(...$this->prefixes);
    }
    public function parse(Inline_Parser_Context $inline_context): bool
    {
        $cursor = $inline_context->get_cursor();
        // Autolinks can only come at the beginning of a line, after whitespace, or certain delimiting characters
        $previous_char = $cursor->peek(-1);
        if (!\in_array($previous_char, self::ALLOWED_AFTER, true)) {
            return false;
        }
        // Check if we have a valid URL
        if (!\preg_match($this->final_regex, $cursor->get_remainder(), $matches)) {
            return false;
        }
        $url = $matches[0];
        // Does the URL end with punctuation that should be stripped?
        if (\preg_match('/(.+?)([?!.,:*_~]+)$/', $url, $matches)) {
            // Add the punctuation later
            $url = $matches[1];
        }
        // Does the URL end with something that looks like an entity reference?
        if (\preg_match('/(.+)(&[A-Za-z0-9]+;)$/', $url, $matches)) {
            $url = $matches[1];
        }
        // Does the URL need unmatched parens chopped off?
        if (str_ends_with($url, ')') && ($diff = self::diff_parens($url)) > 0) {
            $url = \substr($url, 0, -$diff);
        }
        $cursor->advance_by(\mb_strlen($url, 'UTF-8'));
        // Auto-prefix 'http(s)://' onto 'www' URLs
        if (str_starts_with($url, 'www.')) {
            $inline_context->get_container()->append_child(new Link($this->default_protocol . '://' . $url, $url));
            return true;
        }
        $inline_context->get_container()->append_child(new Link($url, $url));
        return true;
    }
    /**
     * @psalm-pure
     */
    private static function diff_parens(string $content): int
    {
        // Scan the entire autolink for the total number of parentheses.
        // If there is a greater number of closing parentheses than opening ones,
        // we don’t consider ANY of the last characters as part of the autolink,
        // in order to facilitate including an autolink inside a parenthesis.
        \preg_match_all('/[()]/', $content, $matches);
        $char_count = ['(' => 0, ')' => 0];
        foreach ($matches[0] as $char) {
            $char_count[$char]++;
        }
        return $char_count[')'] - $char_count['('];
    }
}