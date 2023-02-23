<?php

declare(strict_types=1);

namespace Mistralys\MarkupTextReplacer\MarkupParser\Parser\Node;

use Mistralys\MarkupTextReplacer\MarkupParser\AttributeParser;
use Mistralys\MarkupTextReplacer\MarkupParser\Parser\BaseNode;

class VoidTagNode extends BaseNode
{
    private string $tagName;
    private AttributeParser $attributes;

    public function __construct(string $tagName, string $tagMarkup)
    {
        $this->tagName = $tagName;
        $this->attributes = new AttributeParser($tagMarkup);
    }

    public function getTagName() : string
    {
        return $this->tagName;
    }

    public function getNodeType() : string
    {
        return self::NODE_TYPE_TAG_VOID;
    }

    public function getNodeInfo() : array
    {
        $info = parent::getNodeInfo();
        $info['tagName'] = $this->getTagName();

        return $info;
    }

    public function render() : string
    {
        return $this->attributes->render();
    }
}
