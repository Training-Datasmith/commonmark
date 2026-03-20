<?php

/*
 * This file is part of the league/commonmark package.
 *
 * (c) Colin O'Dell <colinodell@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare (strict_types=1);
namespace League\Common_Mark\Extension\Front_Matter\Exception;

use League\Common_Mark\Exception\Common_Mark_Exception;
class Invalid_Front_Matter_Exception extends \RuntimeException implements Common_Mark_Exception
{
    public static function wrap(\Throwable $t): self
    {
        return new Invalid_Front_Matter_Exception('Failed to parse front matter: ' . $t->get_message(), 0, $t);
    }
}