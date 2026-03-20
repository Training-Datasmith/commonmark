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
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace League\Common_Mark\Extension\Common_Mark\Renderer\Inline;

use League\Common_Mark\Extension\Common_Mark\Node\Inline\Image;
use League\Common_Mark\Node\Inline\Newline;
use League\Common_Mark\Node\Node;
use League\Common_Mark\Node\Node_Iterator;
use League\Common_Mark\Node\String_Container_Interface;
use League\Common_Mark\Renderer\Child_Node_Renderer_Interface;
use League\Common_Mark\Renderer\Node_Renderer_Interface;
use League\Common_Mark\Util\Html_Element;
use League\Common_Mark\Util\Regex_Helper;
use League\Common_Mark\Xml\Xml_Node_Renderer_Interface;
use League\Config\Configuration_Aware_Interface;
use League\Config\Configuration_Interface;
final class Image_Renderer implements Node_Renderer_Interface, Xml_Node_Renderer_Interface, Configuration_Aware_Interface
{
    /** @psalm-readonly-allow-private-mutation */
    private Configuration_Interface $config;
    /**
     * @param Image $node
     *
     * {@inheritDoc}
     *
     * @psalm-suppress MoreSpecificImplementedParamType
     */
    public function render(Node $node, Child_Node_Renderer_Interface $child_renderer): \Stringable
    {
        Image::assert_instance_of($node);
        $attrs = $node->data->get('attributes');
        $forbid_unsafe_links = !$this->config->get('allow_unsafe_links');
        if ($forbid_unsafe_links && Regex_Helper::is_link_potentially_unsafe($node->get_url())) {
            $attrs['src'] = '';
        } else {
            $attrs['src'] = $node->get_url();
        }
        $attrs['alt'] = $this->get_alt_text($node);
        if (($title = $node->get_title()) !== null) {
            $attrs['title'] = $title;
        }
        return new Html_Element('img', $attrs, '', true);
    }
    public function set_configuration(Configuration_Interface $configuration): void
    {
        $this->config = $configuration;
    }
    public function get_xml_tag_name(Node $node): string
    {
        return 'image';
    }
    /**
     * @param Image $node
     *
     * @return array<string, scalar>
     *
     * @psalm-suppress MoreSpecificImplementedParamType
     */
    public function get_xml_attributes(Node $node): array
    {
        Image::assert_instance_of($node);
        return ['destination' => $node->get_url(), 'title' => $node->get_title() ?? ''];
    }
    private function get_alt_text(Image $node): string
    {
        $alt_text = '';
        foreach (new Node_Iterator($node) as $n) {
            if ($n instanceof String_Container_Interface) {
                $alt_text .= $n->get_literal();
            } elseif ($n instanceof Newline) {
                $alt_text .= "\n";
            }
        }
        return $alt_text;
    }
}