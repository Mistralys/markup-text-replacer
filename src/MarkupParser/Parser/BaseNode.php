<?php

declare(strict_types=1);

namespace Mistralys\MarkupTextReplacer\MarkupParser\Parser;

use Mistralys\MarkupTextReplacer\Interfaces\LoggerInterface;
use Mistralys\MarkupTextReplacer\Interfaces\LoggerTrait;

abstract class BaseNode implements LoggerInterface
{
    use LoggerTrait;

    public const NODE_TYPE_COMMENT = 'comment';
    public const NODE_TYPE_DOCTYPE = 'doctype';
    public const NODE_TYPE_TEXT = 'text';
    public const NODE_TYPE_TEXT_WHITESPACE = 'text-whitespace';
    public const NODE_TYPE_TAG = 'tag';
    public const NODE_TYPE_TAG_VOID = 'tag-void';
    public const NODE_TYPE_DOCUMENT_ROOT = 'document-root';

    abstract public function getNodeType() : string;

    public function getNodeInfo() : array
    {
        return array(
            'type' => $this->getNodeType()
        );
    }
}
