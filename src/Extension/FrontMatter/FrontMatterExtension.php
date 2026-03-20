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
namespace League\Common_Mark\Extension\Front_Matter;

use League\Common_Mark\Environment\Environment_Builder_Interface;
use League\Common_Mark\Event\Document_Pre_Parsed_Event;
use League\Common_Mark\Event\Document_Rendered_Event;
use League\Common_Mark\Extension\Extension_Interface;
use League\Common_Mark\Extension\Front_Matter\Data\Front_Matter_Data_Parser_Interface;
use League\Common_Mark\Extension\Front_Matter\Data\Lib_Yaml_Front_Matter_Parser;
use League\Common_Mark\Extension\Front_Matter\Data\Symfony_Yaml_Front_Matter_Parser;
use League\Common_Mark\Extension\Front_Matter\Listener\Front_Matter_Post_Render_Listener;
use League\Common_Mark\Extension\Front_Matter\Listener\Front_Matter_Pre_Parser;
final class Front_Matter_Extension implements Extension_Interface
{
    /** @psalm-readonly */
    private Front_Matter_Parser_Interface $front_matter_parser;
    public function __construct(?Front_Matter_Data_Parser_Interface $data_parser = null)
    {
        $this->front_matter_parser = new Front_Matter_Parser($data_parser ?? Lib_Yaml_Front_Matter_Parser::capable() ?? new Symfony_Yaml_Front_Matter_Parser());
    }
    public function get_front_matter_parser(): Front_Matter_Parser_Interface
    {
        return $this->front_matter_parser;
    }
    public function register(Environment_Builder_Interface $environment): void
    {
        $environment->add_event_listener(Document_Pre_Parsed_Event::class, new Front_Matter_Pre_Parser($this->front_matter_parser));
        $environment->add_event_listener(Document_Rendered_Event::class, new Front_Matter_Post_Render_Listener(), -500);
    }
}