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
namespace League\Common_Mark\Extension\Autolink;

use League\Common_Mark\Environment\Environment_Builder_Interface;
use League\Common_Mark\Extension\Configurable_Extension_Interface;
use League\Config\Configuration_Builder_Interface;
use Nette\Schema\Expect;
final class Autolink_Extension implements Configurable_Extension_Interface
{
    public function configure_schema(Configuration_Builder_Interface $builder): void
    {
        $builder->add_schema('autolink', Expect::structure(['allowed_protocols' => Expect::list_of('string')->default(['http', 'https', 'ftp'])->merge_defaults(false), 'default_protocol' => Expect::string()->default('http')]));
    }
    public function register(Environment_Builder_Interface $environment): void
    {
        $environment->add_inline_parser(new Email_Autolink_Parser());
        $environment->add_inline_parser(new Url_Autolink_Parser($environment->get_configuration()->get('autolink.allowed_protocols'), $environment->get_configuration()->get('autolink.default_protocol')));
    }
}