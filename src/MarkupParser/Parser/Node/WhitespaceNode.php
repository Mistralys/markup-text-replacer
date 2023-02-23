<?php

declare(strict_types=1);

namespace Mistralys\MarkupTextReplacer\MarkupParser\Parser\Node;

class WhitespaceNode extends TextNode
{
    public function getNodeType() : string
    {
        return self::NODE_TYPE_TEXT_WHITESPACE;
    }
}
