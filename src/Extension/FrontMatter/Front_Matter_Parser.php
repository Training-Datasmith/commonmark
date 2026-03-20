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
namespace League\Common_Mark\Extension\Front_Matter;

use League\Common_Mark\Extension\Front_Matter\Data\Front_Matter_Data_Parser_Interface;
use League\Common_Mark\Extension\Front_Matter\Exception\Invalid_Front_Matter_Exception;
use League\Common_Mark\Extension\Front_Matter\Input\Markdown_Input_With_Front_Matter;
use League\Common_Mark\Parser\Cursor;
final class Front_Matter_Parser implements Front_Matter_Parser_Interface
{
    /** @psalm-readonly */
    private Front_Matter_Data_Parser_Interface $front_matter_parser;
    private const REGEX_FRONT_MATTER = '/^---\R.*?\R---\R/s';
    public function __construct(Front_Matter_Data_Parser_Interface $front_matter_parser)
    {
        $this->front_matter_parser = $front_matter_parser;
    }
    /**
     * @throws InvalidFrontMatterException if the front matter cannot be parsed
     */
    public function parse(string $markdown_content): Markdown_Input_With_Front_Matter
    {
        $cursor = new Cursor($markdown_content);
        // Locate the front matter
        $front_matter = $cursor->match(self::REGEX_FRONT_MATTER);
        if ($front_matter === null) {
            return new Markdown_Input_With_Front_Matter($markdown_content);
        }
        // Trim the last line (ending ---s and newline)
        $front_matter = \preg_replace('/---\R$/', '', $front_matter);
        if ($front_matter === null) {
            return new Markdown_Input_With_Front_Matter($markdown_content);
        }
        // Parse the resulting YAML data
        $data = $this->front_matter_parser->parse($front_matter);
        // Advance through any remaining newlines which separated the front matter from the Markdown text
        $trailing_newlines = $cursor->match('/^\R+/');
        // Calculate how many lines the Markdown is offset from the front matter by counting the number of newlines
        // Don't forget to add 1 because we stripped one out when trimming the trailing delims
        $line_offset = \preg_match_all('/\R/', $front_matter . $trailing_newlines) + 1;
        return new Markdown_Input_With_Front_Matter($cursor->get_remainder(), $line_offset, $data);
    }
}