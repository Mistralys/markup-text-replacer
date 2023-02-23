<?php

declare(strict_types=1);

namespace Mistralys\MarkupTextReplacer\MarkupParser\Parser;

abstract class BaseTreeNode extends BaseNode
{
    /**
     * @var BaseNode[]
     */
    private array $nodes = array();

    public function appendChildNode(BaseNode $node) : self
    {
        $this->nodes[] = $node;
        return $this;
    }

    /**
     * @return BaseNode[]
     */
    public function getChildNodes() : array
    {
        return $this->nodes;
    }

    public function hasChildNodes() : bool
    {
        return !empty($this->nodes);
    }

    public function renderNodeTree(array &$tree=array()) : void
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
                    $node->renderNodeTree($subTree);
                }
                else
                {
                    $subTree[] = $node->getNodeInfo();
                }
            }

            $entry['children'] = $subTree;
        }

        $tree[] = $entry;
    }
}
