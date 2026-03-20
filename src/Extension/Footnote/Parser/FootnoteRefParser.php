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

use League\Common_Mark\Extension\Footnote\Node\Footnote_Ref;
use League\Common_Mark\Parser\Inline\Inline_Parser_Interface;
use League\Common_Mark\Parser\Inline\Inline_Parser_Match;
use League\Common_Mark\Parser\Inline_Parser_Context;
use League\Common_Mark\Reference\Reference;
use League\Config\Configuration_Aware_Interface;
use League\Config\Configuration_Interface;
final class Footnote_Ref_Parser implements Inline_Parser_Interface, Configuration_Aware_Interface
{
    private Configuration_Interface $config;
    public function get_match_definition(): Inline_Parser_Match
    {
        return Inline_Parser_Match::regex('\[\^([^\s\]]+)\]');
    }
    public function parse(Inline_Parser_Context $inline_context): bool
    {
        $inline_context->get_cursor()->advance_by($inline_context->get_full_match_length());
        [$label] = $inline_context->get_sub_matches();
        $inline_context->get_container()->append_child(new Footnote_Ref($this->create_reference($label)));
        return true;
    }
    private function create_reference(string $label): Reference
    {
        return new Reference($label, '#' . $this->config->get('footnote/footnote_id_prefix') . $label, $label);
    }
    public function set_configuration(Configuration_Interface $configuration): void
    {
        $this->config = $configuration;
    }
}