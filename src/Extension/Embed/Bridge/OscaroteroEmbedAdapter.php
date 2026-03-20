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
namespace League\Common_Mark\Extension\Embed\Bridge;

use Embed\Embed as EmbedLib;
use League\Common_Mark\Exception\Missing_Dependency_Exception;
use League\Common_Mark\Extension\Embed\Embed;
use League\Common_Mark\Extension\Embed\Embed_Adapter_Interface;
final class Oscarotero_Embed_Adapter implements Embed_Adapter_Interface
{
    private Embed_Lib $embed_lib;
    public function __construct(?Embed_Lib $embed = null)
    {
        if ($embed === null) {
            if (!\class_exists(Embed_Lib::class)) {
                throw new Missing_Dependency_Exception('The embed/embed package is not installed. Please install it with Composer to use this adapter.');
            }
            $embed = new Embed_Lib();
        }
        $this->embed_lib = $embed;
    }
    /**
     * {@inheritDoc}
     */
    public function update_embeds(array $embeds): void
    {
        $extractors = $this->embed_lib->get_multi(...\array_map(static fn(Embed $embed): string => $embed->get_url(), $embeds));
        foreach ($extractors as $i => $extractor) {
            if ($extractor->code !== null) {
                $embeds[$i]->set_embed_code($extractor->code->html);
            }
        }
    }
}