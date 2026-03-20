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
namespace League\Common_Mark\Extension\Description_List;

use League\Common_Mark\Environment\Environment_Builder_Interface;
use League\Common_Mark\Event\Document_Parsed_Event;
use League\Common_Mark\Extension\Description_List\Event\Consecutive_Description_List_Merger;
use League\Common_Mark\Extension\Description_List\Event\Loose_Description_Handler;
use League\Common_Mark\Extension\Description_List\Node\Description;
use League\Common_Mark\Extension\Description_List\Node\Description_List;
use League\Common_Mark\Extension\Description_List\Node\Description_Term;
use League\Common_Mark\Extension\Description_List\Parser\Description_Start_Parser;
use League\Common_Mark\Extension\Description_List\Renderer\Description_List_Renderer;
use League\Common_Mark\Extension\Description_List\Renderer\Description_Renderer;
use League\Common_Mark\Extension\Description_List\Renderer\Description_Term_Renderer;
use League\Common_Mark\Extension\Extension_Interface;
final class Description_List_Extension implements Extension_Interface
{
    public function register(Environment_Builder_Interface $environment): void
    {
        $environment->add_block_start_parser(new Description_Start_Parser());
        $environment->add_event_listener(Document_Parsed_Event::class, new Loose_Description_Handler(), 1001);
        $environment->add_event_listener(Document_Parsed_Event::class, new Consecutive_Description_List_Merger(), 1000);
        $environment->add_renderer(Description_List::class, new Description_List_Renderer());
        $environment->add_renderer(Description_Term::class, new Description_Term_Renderer());
        $environment->add_renderer(Description::class, new Description_Renderer());
    }
}