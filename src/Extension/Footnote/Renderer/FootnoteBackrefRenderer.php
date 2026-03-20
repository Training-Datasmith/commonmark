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
namespace League\Common_Mark\Extension\Footnote\Renderer;

use League\Common_Mark\Extension\Footnote\Node\Footnote_Backref;
use League\Common_Mark\Node\Node;
use League\Common_Mark\Renderer\Child_Node_Renderer_Interface;
use League\Common_Mark\Renderer\Node_Renderer_Interface;
use League\Common_Mark\Util\Html_Element;
use League\Common_Mark\Xml\Xml_Node_Renderer_Interface;
use League\Config\Configuration_Aware_Interface;
use League\Config\Configuration_Interface;
final class Footnote_Backref_Renderer implements Node_Renderer_Interface, Xml_Node_Renderer_Interface, Configuration_Aware_Interface
{
    public const DEFAULT_SYMBOL = '↩';
    private Configuration_Interface $config;
    /**
     * @param FootnoteBackref $node
     *
     * {@inheritDoc}
     *
     * @psalm-suppress MoreSpecificImplementedParamType
     */
    public function render(Node $node, Child_Node_Renderer_Interface $child_renderer): string
    {
        Footnote_Backref::assert_instance_of($node);
        $attrs = $node->data->get_data('attributes');
        $attrs->append('class', $this->config->get('footnote/backref_class'));
        $attrs->set('rev', 'footnote');
        $attrs->set('href', \mb_strtolower($node->get_reference()->get_destination(), 'UTF-8'));
        $attrs->set('role', 'doc-backlink');
        $symbol = $this->config->get('footnote/backref_symbol');
        \assert(\is_string($symbol));
        return '&nbsp;' . new Html_Element('a', $attrs->export(), \htmlspecialchars($symbol), true);
    }
    public function set_configuration(Configuration_Interface $configuration): void
    {
        $this->config = $configuration;
    }
    public function get_xml_tag_name(Node $node): string
    {
        return 'footnote_backref';
    }
    /**
     * @param FootnoteBackref $node
     *
     * @return array<string, scalar>
     *
     * @psalm-suppress MoreSpecificImplementedParamType
     */
    public function get_xml_attributes(Node $node): array
    {
        Footnote_Backref::assert_instance_of($node);
        return ['reference' => $node->get_reference()->get_label()];
    }
}