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

class Domain_Filtering_Adapter implements Embed_Adapter_Interface
{
    private Embed_Adapter_Interface $decorated;
    /** @psalm-var non-empty-string */
    private string $regex;
    /**
     * @param string[] $allowedDomains
     */
    public function __construct(Embed_Adapter_Interface $decorated, array $allowed_domains)
    {
        $this->decorated = $decorated;
        $this->regex = self::create_regex($allowed_domains);
    }
    /**
     * {@inheritDoc}
     */
    public function update_embeds(array $embeds): void
    {
        $this->decorated->update_embeds(\array_values(\array_filter($embeds, fn(Embed $embed): bool => \preg_match($this->regex, $embed->get_url()) === 1)));
    }
    /**
     * @param string[] $allowedDomains
     *
     * @psalm-return non-empty-string
     */
    private static function create_regex(array $allowed_domains): string
    {
        $allowed_domains = \array_map('preg_quote', $allowed_domains);
        return '/^(?:https?:\/\/)?(?:[^.]+\.)*(' . \implode('|', $allowed_domains) . ')/';
    }
}