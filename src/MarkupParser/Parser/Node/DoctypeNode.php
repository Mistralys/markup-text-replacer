<?php

declare(strict_types=1);

namespace Mistralys\MarkupTextReplacer\MarkupParser\Parser\Node;

use Mistralys\MarkupTextReplacer\MarkupParser\Parser\BaseNode;

class DoctypeNode extends BaseNode
{
    private string $markup;

    public function __construct(string $markup)
    {
        $this->markup = $markup;
    }

    public function render() : string
    {
        return $this->markup;
    }

    public function getNodeType() : string
    {
        return self::NODE_TYPE_DOCTYPE;
    }
}
