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

use League\Common_Mark\Environment\Environment_Aware_Interface;
use League\Common_Mark\Environment\Environment_Interface;
use League\Common_Mark\Extension\Footnote\Node\Footnote_Ref;
use League\Common_Mark\Normalizer\Text_Normalizer_Interface;
use League\Common_Mark\Parser\Inline\Inline_Parser_Interface;
use League\Common_Mark\Parser\Inline\Inline_Parser_Match;
use League\Common_Mark\Parser\Inline_Parser_Context;
use League\Common_Mark\Reference\Reference;
use League\Config\Configuration_Interface;
final class Anonymous_Footnote_Ref_Parser implements Inline_Parser_Interface, Environment_Aware_Interface
{
    private Configuration_Interface $config;
    /** @psalm-readonly-allow-private-mutation */
    private Text_Normalizer_Interface $slug_normalizer;
    public function get_match_definition(): Inline_Parser_Match
    {
        return Inline_Parser_Match::regex('\^\[([^\]]+)\]');
    }
    public function parse(Inline_Parser_Context $inline_context): bool
    {
        $inline_context->get_cursor()->advance_by($inline_context->get_full_match_length());
        [$label] = $inline_context->get_sub_matches();
        $reference = $this->create_reference($label);
        $inline_context->get_container()->append_child(new Footnote_Ref($reference, $label));
        return true;
    }
    private function create_reference(string $label): Reference
    {
        $ref_label = $this->slug_normalizer->normalize($label, ['length' => 20]);
        return new Reference($ref_label, '#' . $this->config->get('footnote/footnote_id_prefix') . $ref_label, $label);
    }
    public function set_environment(Environment_Interface $environment): void
    {
        $this->config = $environment->get_configuration();
        $this->slug_normalizer = $environment->get_slug_normalizer();
    }
}