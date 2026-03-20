<?php

declare (strict_types=1);
/*
 * This file is part of the league/commonmark package.
 *
 * (c) Colin O'Dell <colinodell@gmail.com>
 *
 * Original code based on the CommonMark JS reference parser (https://bitly.com/commonmark-js)
 *  - (c) John MacFarlane
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace League\Common_Mark\Extension\Mention;

use League\Common_Mark\Extension\Common_Mark\Node\Inline\Link;
use League\Common_Mark\Node\Inline\Text;
class Mention extends Link
{
    private string $name;
    private string $prefix;
    private string $identifier;
    public function __construct(string $name, string $prefix, string $identifier, ?string $label = null)
    {
        $this->name = $name;
        $this->prefix = $prefix;
        $this->identifier = $identifier;
        parent::__construct('', $label ?? \sprintf('%s%s', $prefix, $identifier));
    }
    public function get_label(): ?string
    {
        if (($label_node = $this->find_label_node()) === null) {
            return null;
        }
        return $label_node->get_literal();
    }
    public function get_identifier(): string
    {
        return $this->identifier;
    }
    public function get_name(): ?string
    {
        return $this->name;
    }
    public function get_prefix(): string
    {
        return $this->prefix;
    }
    public function has_url(): bool
    {
        return $this->url !== '';
    }
    /**
     * @return $this
     */
    public function set_label(string $label): self
    {
        if (($label_node = $this->find_label_node()) === null) {
            $label_node = new Text();
            $this->prepend_child($label_node);
        }
        $label_node->set_literal($label);
        return $this;
    }
    private function find_label_node(): ?Text
    {
        foreach ($this->children() as $child) {
            if ($child instanceof Text) {
                return $child;
            }
        }
        return null;
    }
}