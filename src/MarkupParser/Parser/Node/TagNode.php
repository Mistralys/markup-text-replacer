<?php

declare(strict_types=1);

namespace Mistralys\MarkupTextReplacer\MarkupParser\Parser\Node;

use Mistralys\MarkupTextReplacer\MarkupParser\AttributeParser;
use Mistralys\MarkupTextReplacer\MarkupParser\Parser\BaseTreeNode;

class TagNode extends BaseTreeNode
{
    private string $closingMarkup = '';
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

    public function getNodeInfo() : array
    {
        $info = parent::getNodeInfo();
        $info['tagName'] = $this->getTagName();

        return $info;
    }

    public function registerClosingMarkup(string $closingMarkup) : void
    {
        $this->closingMarkup = $closingMarkup;
    }

    protected function renderOpeningMarkup() : string
    {
        return $this->attributes->render();
    }

    protected function renderClosingMarkup() : string
    {
        if(!empty($this->closingMarkup)) {
            return $this->closingMarkup;
        }

        // Handle the case where no closing tag was captured
        // by the parser, either because the HTML was invalid,
        // or if the node was created manually.
        return '</'.$this->getTagName().'>';
    }

    public function getNodeType() : string
    {
        return self::NODE_TYPE_TAG;
    }
}
