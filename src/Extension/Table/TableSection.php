<?php

declare (strict_types=1);
/*
 * This is part of the league/commonmark package.
 *
 * (c) Martin Hasoň <martin.hason@gmail.com>
 * (c) Webuni s.r.o. <info@webuni.cz>
 * (c) Colin O'Dell <colinodell@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace League\Common_Mark\Extension\Table;

use League\Common_Mark\Node\Block\Abstract_Block;
final class Table_Section extends Abstract_Block
{
    public const TYPE_HEAD = 'head';
    public const TYPE_BODY = 'body';
    /**
     * @psalm-var self::TYPE_*
     * @phpstan-var self::TYPE_*
     *
     * @psalm-readonly
     */
    private string $type;
    /**
     * @psalm-param self::TYPE_* $type
     *
     * @phpstan-param self::TYPE_* $type
     */
    public function __construct(string $type = self::TYPE_BODY)
    {
        parent::__construct();
        $this->type = $type;
    }
    /**
     * @psalm-return self::TYPE_*
     *
     * @phpstan-return self::TYPE_*
     */
    public function get_type(): string
    {
        return $this->type;
    }
    public function is_head(): bool
    {
        return $this->type === self::TYPE_HEAD;
    }
    public function is_body(): bool
    {
        return $this->type === self::TYPE_BODY;
    }
}