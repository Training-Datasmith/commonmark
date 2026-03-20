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

use League\Common_Mark\Exception\LogicException;
use League\Common_Mark\Extension\Mention\Mention;
use League\Common_Mark\Node\Inline\Abstract_Inline;
final class Callback_Generator implements Mention_Generator_Interface
{
    /**
     * A callback function which sets the URL on the passed mention and returns the mention, return a new AbstractInline based object or null if the mention is not a match
     *
     * @var callable(Mention): ?AbstractInline
     */
    private $callback;
    public function __construct(callable $callback)
    {
        $this->callback = $callback;
    }
    /**
     * @throws LogicException
     */
    public function generate_mention(Mention $mention): ?Abstract_Inline
    {
        $result = \call_user_func($this->callback, $mention);
        if ($result === null) {
            return null;
        }
        if ($result instanceof Abstract_Inline && !$result instanceof Mention) {
            return $result;
        }
        if ($result->has_url()) {
            return $mention;
        }
        throw new LogicException('CallbackGenerator callable must set the URL on the passed mention and return the mention, return a new AbstractInline based object or null if the mention is not a match');
    }
}