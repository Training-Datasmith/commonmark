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
namespace League\Common_Mark\Extension\Footnote;

use League\Common_Mark\Environment\Environment_Builder_Interface;
use League\Common_Mark\Event\Document_Parsed_Event;
use League\Common_Mark\Extension\Configurable_Extension_Interface;
use League\Common_Mark\Extension\Footnote\Event\Anonymous_Footnotes_Listener;
use League\Common_Mark\Extension\Footnote\Event\Fix_Orphaned_Footnotes_And_Refs_Listener;
use League\Common_Mark\Extension\Footnote\Event\Gather_Footnotes_Listener;
use League\Common_Mark\Extension\Footnote\Event\Number_Footnotes_Listener;
use League\Common_Mark\Extension\Footnote\Node\Footnote;
use League\Common_Mark\Extension\Footnote\Node\Footnote_Backref;
use League\Common_Mark\Extension\Footnote\Node\Footnote_Container;
use League\Common_Mark\Extension\Footnote\Node\Footnote_Ref;
use League\Common_Mark\Extension\Footnote\Parser\Anonymous_Footnote_Ref_Parser;
use League\Common_Mark\Extension\Footnote\Parser\Footnote_Ref_Parser;
use League\Common_Mark\Extension\Footnote\Parser\Footnote_Start_Parser;
use League\Common_Mark\Extension\Footnote\Renderer\Footnote_Backref_Renderer;
use League\Common_Mark\Extension\Footnote\Renderer\Footnote_Container_Renderer;
use League\Common_Mark\Extension\Footnote\Renderer\Footnote_Ref_Renderer;
use League\Common_Mark\Extension\Footnote\Renderer\Footnote_Renderer;
use League\Config\Configuration_Builder_Interface;
use Nette\Schema\Expect;
final class Footnote_Extension implements Configurable_Extension_Interface
{
    public function configure_schema(Configuration_Builder_Interface $builder): void
    {
        $builder->add_schema('footnote', Expect::structure(['backref_class' => Expect::string('footnote-backref'), 'backref_symbol' => Expect::string('↩'), 'container_add_hr' => Expect::bool(true), 'container_class' => Expect::string('footnotes'), 'ref_class' => Expect::string('footnote-ref'), 'ref_id_prefix' => Expect::string('fnref:'), 'footnote_class' => Expect::string('footnote'), 'footnote_id_prefix' => Expect::string('fn:')]));
    }
    public function register(Environment_Builder_Interface $environment): void
    {
        $environment->add_block_start_parser(new Footnote_Start_Parser(), 51);
        $environment->add_inline_parser(new Anonymous_Footnote_Ref_Parser(), 35);
        $environment->add_inline_parser(new Footnote_Ref_Parser(), 51);
        $environment->add_renderer(Footnote_Container::class, new Footnote_Container_Renderer());
        $environment->add_renderer(Footnote::class, new Footnote_Renderer());
        $environment->add_renderer(Footnote_Ref::class, new Footnote_Ref_Renderer());
        $environment->add_renderer(Footnote_Backref::class, new Footnote_Backref_Renderer());
        $environment->add_event_listener(Document_Parsed_Event::class, [new Anonymous_Footnotes_Listener(), 'onDocumentParsed'], 40);
        $environment->add_event_listener(Document_Parsed_Event::class, [new Fix_Orphaned_Footnotes_And_Refs_Listener(), 'onDocumentParsed'], 30);
        $environment->add_event_listener(Document_Parsed_Event::class, [new Number_Footnotes_Listener(), 'onDocumentParsed'], 20);
        $environment->add_event_listener(Document_Parsed_Event::class, [new Gather_Footnotes_Listener(), 'onDocumentParsed'], 10);
    }
}