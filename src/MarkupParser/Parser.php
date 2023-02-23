<?php

declare(strict_types=1);

namespace Mistralys\MarkupTextReplacer\MarkupParser;

use Mistralys\MarkupTextReplacer\MarkupParser\Parser\BaseNode;
use Mistralys\MarkupTextReplacer\MarkupParser\Parser\BaseTreeNode;
use Mistralys\MarkupTextReplacer\MarkupParser\Parser\Node\CommentNode;
use Mistralys\MarkupTextReplacer\MarkupParser\Parser\Node\DoctypeNode;
use Mistralys\MarkupTextReplacer\MarkupParser\Parser\Node\TagNode;
use Mistralys\MarkupTextReplacer\MarkupParser\Parser\Node\TextNode;
use Mistralys\MarkupTextReplacer\MarkupParser\Parser\Node\VoidTagNode;
use Mistralys\MarkupTextReplacer\MarkupParser\Parser\Node\WhitespaceNode;
use SebastianBergmann\CodeCoverage\ParserException;

class Parser extends BaseTreeNode
{
    private ?BaseTreeNode $openTag = null;

    /**
     * @var string[]
     */
    private static array $voidTagNames = array(
        'area',
        'base',
        'br',
        'col',
        'embed',
        'hr',
        'iframe',
        'img',
        'input',
        'link',
        'meta',
        'param',
        'source',
        'track',
        'wbr',
        'command',
        'keygen',
        'menuitem',
    );

    /**
     * @var TagNode[]
     */
    private array $tagStack = array();
    private Tokenizer $tokenizer;
    private bool $parsed = false;

    private function __construct(Tokenizer $tokenizer)
    {
        $this->tokenizer = $tokenizer;
    }

    public static function create(string $html) : Parser
    {
        return new Parser((new Lexer($html))->createTokenizer());
    }

    public function parse() : self
    {
        if($this->parsed === true) {
            return $this;
        }

        $this->parsed = true;

        $tokens = $this->tokenizer->getTokens();

        $this->logTrivial('Parsing [%s] tokens.', count($tokens));

        foreach($tokens as $token)
        {
            $this->parseToken($token);
        }

        return $this;
    }

    /**
     * @param array{type:string,content:string}|array{type:string,content:string,tagName:string} $token
     * @return void
     */
    private function parseToken(array $token) : void
    {
        $node = $this->createNodeForToken($token['type'], $token['content'], $token['tagName'] ?? null);

        if($node === null)
        {
            return;
        }

        if($this->openTag !== null)
        {
            $this->openTag->appendNode($node);
        }
        else
        {
            $this->appendNode($node);
        }
    }

    private function createNodeForToken(string $type, string $content, ?string $tagName) : ?BaseNode
    {
        if($type === Tokenizer::TOKEN_COMMENT)
        {
            $this->logTrivial('Creating a comment node.');
            return new CommentNode($content);
        }

        if($type === Tokenizer::TOKEN_DOCTYPE)
        {
            $this->logTrivial('Creating a doctype node.');
            return new DoctypeNode($content);
        }

        if($type === Tokenizer::TOKEN_TEXT)
        {
            if(trim($content) === '') {
                $this->logTrivial('Creating a whitespace node.');
                return new WhitespaceNode($content);
            }

            $this->logTrivial('Creating a text node.');
            return new TextNode($content);
        }

        if($type === Tokenizer::TOKEN_OPENING_TAG)
        {
            if(self::isVoidTag($tagName))
            {
                $this->logTrivial('Creating a void [%s] tag node.', $tagName);
                return new VoidTagNode($tagName, $content);
            }

            $this->logTrivial('Creating a [%s] tag node.', $tagName);

            $node = new TagNode($tagName, $content);
            $this->tagStack[] = $node;

            if(isset($this->openTag))
            {
                $this->openTag->appendNode($node);
            }
            else
            {
                $this->appendNode($node);
            }

            $this->openTag = $node;

            return null;
        }

        if($type === Tokenizer::TOKEN_CLOSING_TAG)
        {
            // Closing tag, but no open tag - ignore.
            if(empty($this->tagStack))
            {
                $this->logImportant('Found closing tag [%s], but no tag to close!', $tagName);
                return null;
            }

            // Get the last node
            $node = array_pop($this->tagStack);

            // Is this a matching closing tag for the open tag?
            if($tagName === $node->getTagName())
            {
                $this->logTrivial('Closed the [%s] tag.', $tagName);
                $node->registerClosingMarkup($content);
            }
            else
            {
                $this->logImportant('Ignoring closing tag [%s], open tag is [%s].', $tagName, $node->getTagName());
                // Put the node back on the stack
                $this->tagStack[] = $node;
            }

            // Switch the open tag to the next open one in the stack
            if(!empty($this->tagStack)) {
                $this->openTag = $this->tagStack[array_key_last($this->tagStack)];
                $this->logTrivial('Tag [%s] is the next open one to close.', $this->openTag->getTagName());
            } else {
                $this->logTrivial('No open tags left on the stack.');
            }

            return null;
        }

        return null;
    }

    public static function isVoidTag(string $tagName) : bool
    {
        return in_array(strtolower($tagName), self::$voidTagNames);
    }

    public function getNodeType() : string
    {
        return self::NODE_TYPE_DOCUMENT_ROOT;
    }

    protected function renderOpeningMarkup() : string
    {
        return '';
    }

    protected function renderClosingMarkup() : string
    {
        return '';
    }
}
