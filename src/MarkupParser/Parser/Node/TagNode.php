<?php

declare(strict_types=1);

namespace Mistralys\MarkupTextReplacer\MarkupParser\Parser\Node;

use Mistralys\MarkupTextReplacer\MarkupParser\Parser\BaseTreeNode;

class TagNode extends BaseTreeNode
{
    private string $tagName;
    private string $openingMarkup;
    private string $closingMarkup;

    public function __construct(string $tagName, string $openingMarkup)
    {
        $this->tagName = $tagName;
        $this->openingMarkup = $openingMarkup;
    }

    public function getTagName() : string
    {
        return $this->tagName;
    }

    public function setClosingMarkup(string $closingMarkup) : void
    {
        $this->closingMarkup = $closingMarkup;
    }

    public function getNodeType() : string
    {
        return self::NODE_TYPE_TAG;
    }

    public function getNodeInfo() : array
    {
        $info = parent::getNodeInfo();
        $info['tagName'] = $this->getTagName();

        return $info;
    }
}
