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
namespace League\Common_Mark\Extension\Footnote\Event;

use League\Common_Mark\Event\Document_Parsed_Event;
use League\Common_Mark\Extension\Footnote\Node\Footnote;
use League\Common_Mark\Extension\Footnote\Node\Footnote_Backref;
use League\Common_Mark\Extension\Footnote\Node\Footnote_Container;
use League\Common_Mark\Node\Block\Document;
use League\Common_Mark\Node\Node_Iterator;
use League\Common_Mark\Reference\Reference;
use League\Config\Configuration_Aware_Interface;
use League\Config\Configuration_Interface;
final class Gather_Footnotes_Listener implements Configuration_Aware_Interface
{
    private Configuration_Interface $config;
    public function on_document_parsed(Document_Parsed_Event $event): void
    {
        $document = $event->get_document();
        $footnotes = [];
        foreach ($document->iterator(Node_Iterator::FLAG_BLOCKS_ONLY) as $node) {
            if (!$node instanceof Footnote) {
                continue;
            }
            // Look for existing reference with footnote label
            $ref = $document->get_reference_map()->get($node->get_reference()->get_label());
            if ($ref !== null) {
                // Use numeric title to get footnotes order
                $footnotes[(int) $ref->get_title()] = $node;
            } else {
                // Footnote call is missing, append footnote at the end
                $footnotes[\PHP_INT_MAX] = $node;
            }
            $key = '#' . $this->config->get('footnote/footnote_id_prefix') . $node->get_reference()->get_destination();
            if ($document->data->has($key)) {
                $this->create_backrefs($node, $document->data->get($key));
            }
        }
        // Only add a footnote container if there are any
        if (\count($footnotes) === 0) {
            return;
        }
        $container = $this->get_footnotes_container($document);
        \ksort($footnotes);
        foreach ($footnotes as $footnote) {
            $container->append_child($footnote);
        }
    }
    private function get_footnotes_container(Document $document): Footnote_Container
    {
        $footnote_container = new Footnote_Container();
        $document->append_child($footnote_container);
        return $footnote_container;
    }
    /**
     * Look for all footnote refs pointing to this footnote and create each footnote backrefs.
     *
     * @param Footnote    $node     The target footnote
     * @param Reference[] $backrefs References to create backrefs for
     */
    private function create_backrefs(Footnote $node, array $backrefs): void
    {
        // Backrefs should be added to the child paragraph
        $target = $node->last_child();
        if ($target === null) {
            // This should never happen, but you never know
            $target = $node;
        }
        foreach ($backrefs as $backref) {
            $target->append_child(new Footnote_Backref(new Reference($backref->get_label(), '#' . $this->config->get('footnote/ref_id_prefix') . $backref->get_label(), $backref->get_title())));
        }
    }
    public function set_configuration(Configuration_Interface $configuration): void
    {
        $this->config = $configuration;
    }
}