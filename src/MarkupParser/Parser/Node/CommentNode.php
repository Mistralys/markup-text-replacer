<?php

declare(strict_types=1);

namespace Mistralys\MarkupTextReplacer\MarkupParser\Parser\Node;

use Mistralys\MarkupTextReplacer\MarkupParser\Parser\BaseNode;

class CommentNode extends BaseNode
{
    private string $text;

    public function __construct(string $text)
    {
        $this->text = $text;
    }

    public function render() : string
    {
        return $this->text;
    }

    public function getNodeType() : string
    {
        return self::NODE_TYPE_COMMENT;
    }
}
