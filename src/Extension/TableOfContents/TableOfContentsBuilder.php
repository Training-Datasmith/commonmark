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
namespace League\Common_Mark\Extension\Table_Of_Contents;

use League\Common_Mark\Event\Document_Parsed_Event;
use League\Common_Mark\Extension\Common_Mark\Node\Block\Heading;
use League\Common_Mark\Extension\Heading_Permalink\Heading_Permalink;
use League\Common_Mark\Extension\Table_Of_Contents\Node\Table_Of_Contents;
use League\Common_Mark\Extension\Table_Of_Contents\Node\Table_Of_Contents_Placeholder;
use League\Common_Mark\Node\Block\Document;
use League\Common_Mark\Node\Node_Iterator;
use League\Config\Configuration_Aware_Interface;
use League\Config\Configuration_Interface;
use League\Config\Exception\Invalid_Configuration_Exception;
final class Table_Of_Contents_Builder implements Configuration_Aware_Interface
{
    public const POSITION_TOP = 'top';
    public const POSITION_BEFORE_HEADINGS = 'before-headings';
    public const POSITION_PLACEHOLDER = 'placeholder';
    /** @psalm-readonly-allow-private-mutation */
    private Configuration_Interface $config;
    public function on_document_parsed(Document_Parsed_Event $event): void
    {
        $document = $event->get_document();
        $generator = new Table_Of_Contents_Generator((string) $this->config->get('table_of_contents/style'), (string) $this->config->get('table_of_contents/normalize'), (int) $this->config->get('table_of_contents/min_heading_level'), (int) $this->config->get('table_of_contents/max_heading_level'), (string) $this->config->get('heading_permalink/fragment_prefix'));
        $toc = $generator->generate($document);
        if ($toc === null) {
            // No linkable headers exist, so no TOC could be generated
            return;
        }
        // Add custom CSS class(es), if defined
        $class = $this->config->get('table_of_contents/html_class');
        if ($class !== null) {
            $toc->data->append('attributes/class', $class);
        }
        // Add the TOC to the Document
        $position = $this->config->get('table_of_contents/position');
        if ($position === self::POSITION_TOP) {
            $document->prepend_child($toc);
        } elseif ($position === self::POSITION_BEFORE_HEADINGS) {
            $this->insert_before_first_linked_heading($document, $toc);
        } elseif ($position === self::POSITION_PLACEHOLDER) {
            $this->replace_placeholders($document, $toc);
        } else {
            throw Invalid_Configuration_Exception::for_config_option('table_of_contents/position', $position);
        }
    }
    private function insert_before_first_linked_heading(Document $document, Table_Of_Contents $toc): void
    {
        foreach ($document->iterator(Node_Iterator::FLAG_BLOCKS_ONLY) as $node) {
            if (!$node instanceof Heading) {
                continue;
            }
            foreach ($node->children() as $child) {
                if ($child instanceof Heading_Permalink) {
                    $node->insert_before($toc);
                    return;
                }
            }
        }
    }
    private function replace_placeholders(Document $document, Table_Of_Contents $toc): void
    {
        foreach ($document->iterator(Node_Iterator::FLAG_BLOCKS_ONLY) as $node) {
            // Add the block once we find a placeholder
            if (!$node instanceof Table_Of_Contents_Placeholder) {
                continue;
            }
            $node->replace_with(clone $toc);
        }
    }
    public function set_configuration(Configuration_Interface $configuration): void
    {
        $this->config = $configuration;
    }
}