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
use League\Common_Mark\Extension\Footnote\Node\Footnote_Ref;
use League\Common_Mark\Node\Block\Paragraph;
use League\Common_Mark\Node\Inline\Text;
use League\Common_Mark\Reference\Reference;
use League\Config\Configuration_Aware_Interface;
use League\Config\Configuration_Interface;
final class Anonymous_Footnotes_Listener implements Configuration_Aware_Interface
{
    private Configuration_Interface $config;
    public function on_document_parsed(Document_Parsed_Event $event): void
    {
        $document = $event->get_document();
        foreach ($document->iterator() as $node) {
            if (!$node instanceof Footnote_Ref) {
                continue;
            }
            if (($text = $node->get_content()) === null) {
                continue;
            }
            // Anonymous footnote needs to create a footnote from its content
            $existing_reference = $node->get_reference();
            $new_reference = new Reference($existing_reference->get_label(), '#' . $this->config->get('footnote/ref_id_prefix') . $existing_reference->get_label(), $existing_reference->get_title());
            $paragraph = new Paragraph();
            $paragraph->append_child(new Text($text));
            $paragraph->append_child(new Footnote_Backref($new_reference));
            $footnote = new Footnote($new_reference);
            $footnote->append_child($paragraph);
            $document->append_child($footnote);
        }
    }
    public function set_configuration(Configuration_Interface $configuration): void
    {
        $this->config = $configuration;
    }
}