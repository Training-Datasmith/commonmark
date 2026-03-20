<?php

/*
 * This file is part of the league/commonmark package.
 *
 * (c) Colin O'Dell <colinodell@gmail.com>
 * (c) 2015 Martin Hasoň <martin.hason@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare (strict_types=1);
namespace League\Common_Mark\Extension\Attributes;

use League\Common_Mark\Environment\Environment_Builder_Interface;
use League\Common_Mark\Event\Document_Parsed_Event;
use League\Common_Mark\Extension\Attributes\Event\Attributes_Listener;
use League\Common_Mark\Extension\Attributes\Parser\Attributes_Block_Start_Parser;
use League\Common_Mark\Extension\Attributes\Parser\Attributes_Inline_Parser;
use League\Common_Mark\Extension\Configurable_Extension_Interface;
use League\Config\Configuration_Builder_Interface;
use Nette\Schema\Expect;
final class Attributes_Extension implements Configurable_Extension_Interface
{
    public function configure_schema(Configuration_Builder_Interface $builder): void
    {
        $builder->add_schema('attributes', Expect::structure(['allow' => Expect::array_of('string')->default([])]));
    }
    public function register(Environment_Builder_Interface $environment): void
    {
        $allow_list = $environment->get_configuration()->get('attributes.allow');
        $allow_unsafe_links = $environment->get_configuration()->get('allow_unsafe_links');
        $environment->add_block_start_parser(new Attributes_Block_Start_Parser());
        $environment->add_inline_parser(new Attributes_Inline_Parser());
        $environment->add_event_listener(Document_Parsed_Event::class, [new Attributes_Listener($allow_list, $allow_unsafe_links), 'processDocument']);
    }
}