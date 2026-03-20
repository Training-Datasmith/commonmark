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
namespace League\Common_Mark\Extension;

use League\Common_Mark\Environment\Environment_Builder_Interface;
use League\Common_Mark\Extension\Autolink\Autolink_Extension;
use League\Common_Mark\Extension\Disallowed_Raw_Html\Disallowed_Raw_Html_Extension;
use League\Common_Mark\Extension\Strikethrough\Strikethrough_Extension;
use League\Common_Mark\Extension\Table\Table_Extension;
use League\Common_Mark\Extension\Task_List\Task_List_Extension;
final class Github_Flavored_Markdown_Extension implements Extension_Interface
{
    public function register(Environment_Builder_Interface $environment): void
    {
        $environment->add_extension(new Autolink_Extension());
        $environment->add_extension(new Disallowed_Raw_Html_Extension());
        $environment->add_extension(new Strikethrough_Extension());
        $environment->add_extension(new Table_Extension());
        $environment->add_extension(new Task_List_Extension());
    }
}