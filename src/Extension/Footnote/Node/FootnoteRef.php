<?php

/*
 * This file is part of the league/commonmark package.
 *
 * (c) Colin O'Dell <colinodell@gmail.com>
 * (c) Rezo Zero / Ambroise Maupate
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare (strict_types=1);
namespace League\Common_Mark\Extension\Footnote\Node;

use League\Common_Mark\Node\Inline\Abstract_Inline;
use League\Common_Mark\Reference\Referenceable_Interface;
use League\Common_Mark\Reference\Reference_Interface;
final class Footnote_Ref extends Abstract_Inline implements Referenceable_Interface
{
    private Reference_Interface $reference;
    /** @psalm-readonly */
    private ?string $content = null;
    /**
     * @param array<mixed> $data
     */
    public function __construct(Reference_Interface $reference, ?string $content = null, array $data = [])
    {
        parent::__construct();
        $this->reference = $reference;
        $this->content = $content;
        if (\count($data) > 0) {
            $this->data->import($data);
        }
    }
    public function get_reference(): Reference_Interface
    {
        return $this->reference;
    }
    public function set_reference(Reference_Interface $reference): void
    {
        $this->reference = $reference;
    }
    public function get_content(): ?string
    {
        return $this->content;
    }
}