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
namespace League\Common_Mark\Extension\Embed;

/**
 * Interface for a service which updates the embed code(s) for the given array of embeds
 */
interface Embed_Adapter_Interface
{
    /**
     * @param Embed[] $embeds
     */
    public function update_embeds(array $embeds): void;
}