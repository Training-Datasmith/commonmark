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
namespace League\Common_Mark\Extension\Heading_Permalink;

use League\Common_Mark\Environment\Environment_Aware_Interface;
use League\Common_Mark\Environment\Environment_Interface;
use League\Common_Mark\Event\Document_Parsed_Event;
use League\Common_Mark\Extension\Common_Mark\Node\Block\Heading;
use League\Common_Mark\Node\Node_Iterator;
use League\Common_Mark\Node\Raw_Markup_Container_Interface;
use League\Common_Mark\Node\String_Container_Helper;
use League\Common_Mark\Normalizer\Text_Normalizer_Interface;
use League\Config\Configuration_Interface;
use League\Config\Exception\Invalid_Configuration_Exception;
/**
 * Searches the Document for Heading elements and adds HeadingPermalinks to each one
 */
final class Heading_Permalink_Processor implements Environment_Aware_Interface
{
    public const INSERT_BEFORE = 'before';
    public const INSERT_AFTER = 'after';
    public const INSERT_NONE = 'none';
    /** @psalm-readonly-allow-private-mutation */
    private Text_Normalizer_Interface $slug_normalizer;
    /** @psalm-readonly-allow-private-mutation */
    private Configuration_Interface $config;
    public function set_environment(Environment_Interface $environment): void
    {
        $this->config = $environment->get_configuration();
        $this->slug_normalizer = $environment->get_slug_normalizer();
    }
    public function __invoke(Document_Parsed_Event $e): void
    {
        $min = (int) $this->config->get('heading_permalink/min_heading_level');
        $max = (int) $this->config->get('heading_permalink/max_heading_level');
        $apply_to_heading = (bool) $this->config->get('heading_permalink/apply_id_to_heading');
        $id_prefix = (string) $this->config->get('heading_permalink/id_prefix');
        $slug_length = (int) $this->config->get('slug_normalizer/max_length');
        $heading_class = (string) $this->config->get('heading_permalink/heading_class');
        if ($id_prefix !== '') {
            $id_prefix .= '-';
        }
        foreach ($e->get_document()->iterator(Node_Iterator::FLAG_BLOCKS_ONLY) as $node) {
            if ($node instanceof Heading && $node->get_level() >= $min && $node->get_level() <= $max) {
                $this->add_heading_link($node, $slug_length, $id_prefix, $apply_to_heading, $heading_class);
            }
        }
    }
    private function add_heading_link(Heading $heading, int $slug_length, string $id_prefix, bool $apply_to_heading, string $heading_class): void
    {
        $text = String_Container_Helper::get_child_text($heading, [Raw_Markup_Container_Interface::class]);
        $slug = $this->slug_normalizer->normalize($text, ['node' => $heading, 'length' => $slug_length]);
        if ($apply_to_heading) {
            $heading->data->set('attributes/id', $id_prefix . $slug);
        }
        if ($heading_class !== '') {
            $heading->data->append('attributes/class', $heading_class);
        }
        $heading_link_anchor = new Heading_Permalink($slug);
        switch ($this->config->get('heading_permalink/insert')) {
            case self::INSERT_BEFORE:
                $heading->prepend_child($heading_link_anchor);
                return;
            case self::INSERT_AFTER:
                $heading->append_child($heading_link_anchor);
                return;
            case self::INSERT_NONE:
                return;
            default:
                throw new Invalid_Configuration_Exception("Invalid configuration value for heading_permalink/insert; expected 'before', 'after', or 'none'");
        }
    }
}