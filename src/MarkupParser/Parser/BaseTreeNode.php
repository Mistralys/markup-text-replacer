<?php

declare(strict_types=1);

namespace Mistralys\MarkupTextReplacer\MarkupParser\Parser;

use Mistralys\MarkupTextReplacer\MarkupParser\Parser\Node\WhitespaceNode;

abstract class BaseTreeNode extends BaseNode
{
    /**
     * @var BaseNode[]
     */
    private array $nodes = array();

    public function appendNode(BaseNode $node) : self
    {
        $this->nodes[] = $node;
        return $this;
    }

    /**
     * @return BaseNode[]
     */
    public function getChildNodes(bool $includeWhitespace=true) : array
    {
        if($includeWhitespace)
        {
            return $this->nodes;
        }

        $result = array();
        foreach($this->nodes as $node)
        {
            if(!$node instanceof WhitespaceNode)
            {
                $result[] = $node;
            }
        }

        return $result;
    }

    public function hasChildNodes(bool $includeWhitespace=true) : bool
    {
        $childNodes = $this->getChildNodes($includeWhitespace);
        return !empty($childNodes);
    }

    public function countChildNodes(bool $includeWhitespace=true) : int
    {
        $childNodes = $this->getChildNodes($includeWhitespace);
        return count($childNodes);
    }

    public function render() : string
    {
        return
            $this->renderOpeningMarkup().
            $this->renderChildNodesMarkup().
            $this->renderClosingMarkup();
    }

    abstract protected function renderOpeningMarkup() : string;

    abstract protected function renderClosingMarkup() : string;

    private function renderChildNodesMarkup() : string
    {
        $markup = '';

        foreach($this->nodes as $node)
        {
            $markup .= $node->render();
        }

        return $markup;
    }

    public function renderNodeTree(array $tree=array()) : array
    {
        $childNodes = $this->getChildNodes();

        $entry = $this->getNodeInfo();

        if(!empty($childNodes))
        {
            $subTree = array();

            foreach ($childNodes as $node)
            {
                if ($node instanceof self)
                {
                    $subTree[] = $node->renderNodeTree();
                }
                else
                {
                    $subTree[] = $node->getNodeInfo();
                }
            }

            $entry['children'] = $subTree;
        }

        $tree[] = $entry;

        return $tree;
    }
}
