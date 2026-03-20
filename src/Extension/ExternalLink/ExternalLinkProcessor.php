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
namespace League\Common_Mark\Extension\External_Link;

use League\Common_Mark\Event\Document_Parsed_Event;
use League\Common_Mark\Extension\Common_Mark\Node\Inline\Link;
use League\Config\Configuration_Interface;
final class External_Link_Processor
{
    public const APPLY_NONE = '';
    public const APPLY_ALL = 'all';
    public const APPLY_EXTERNAL = 'external';
    public const APPLY_INTERNAL = 'internal';
    /** @psalm-readonly */
    private Configuration_Interface $config;
    public function __construct(Configuration_Interface $config)
    {
        $this->config = $config;
    }
    public function __invoke(Document_Parsed_Event $e): void
    {
        $internal_hosts = $this->config->get('external_link/internal_hosts');
        $open_in_new_window = $this->config->get('external_link/open_in_new_window');
        $classes = $this->config->get('external_link/html_class');
        foreach ($e->get_document()->iterator() as $link) {
            if (!$link instanceof Link) {
                continue;
            }
            $host = \parse_url($link->get_url(), PHP_URL_HOST);
            if (!\is_string($host)) {
                // Something is terribly wrong with this URL
                continue;
            }
            if (self::host_matches($host, $internal_hosts)) {
                $link->data->set('external', false);
                $this->apply_rel_attribute($link, false);
                continue;
            }
            // Host does not match our list
            $this->mark_link_as_external($link, $open_in_new_window, $classes);
        }
    }
    private function mark_link_as_external(Link $link, bool $open_in_new_window, string $classes): void
    {
        $link->data->set('external', true);
        $this->apply_rel_attribute($link, true);
        if ($open_in_new_window) {
            $link->data->set('attributes/target', '_blank');
        }
        if ($classes !== '') {
            $link->data->append('attributes/class', $classes);
        }
    }
    private function apply_rel_attribute(Link $link, bool $is_external): void
    {
        $options = ['nofollow' => $this->config->get('external_link/nofollow'), 'noopener' => $this->config->get('external_link/noopener'), 'noreferrer' => $this->config->get('external_link/noreferrer')];
        foreach ($options as $type => $option) {
            switch (true) {
                case $option === self::APPLY_ALL:
                case $is_external && $option === self::APPLY_EXTERNAL:
                case !$is_external && $option === self::APPLY_INTERNAL:
                    $link->data->append('attributes/rel', $type);
            }
        }
        // No rel attributes? Mark the attribute as 'false' so LinkRenderer doesn't add defaults
        if (!$link->data->has('attributes/rel')) {
            $link->data->set('attributes/rel', false);
        }
    }
    /**
     * @internal This method is only public so we can easily test it. DO NOT USE THIS OUTSIDE OF THIS EXTENSION!
     *
     * @param non-empty-string|list<non-empty-string> $compareTo
     */
    public static function host_matches(string $host, $compare_to): bool
    {
        foreach ((array) $compare_to as $c) {
            if (str_starts_with($c, '/')) {
                if (\preg_match($c, $host)) {
                    return true;
                }
            } elseif ($c === $host) {
                return true;
            }
        }
        return false;
    }
}