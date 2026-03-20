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
namespace League\Common_Mark\Extension\Mention\Generator;

use League\Common_Mark\Extension\Mention\Mention;
final class String_Template_Link_Generator implements Mention_Generator_Interface
{
    private string $url_template;
    public function __construct(string $url_template)
    {
        $this->url_template = $url_template;
    }
    public function generate_mention(Mention $mention): \League\Common_Mark\Extension\Mention\Mention
    {
        $mention->set_url(\sprintf($this->url_template, $mention->get_identifier()));
        return $mention;
    }
}