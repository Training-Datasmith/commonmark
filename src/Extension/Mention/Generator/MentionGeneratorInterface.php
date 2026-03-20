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
use League\Common_Mark\Node\Inline\Abstract_Inline;
interface Mention_Generator_Interface
{
    public function generate_mention(Mention $mention): ?Abstract_Inline;
}