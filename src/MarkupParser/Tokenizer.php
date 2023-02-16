<?php
/**
 * @package MarkupTextReplacer
 * @subpackage Parser
 * @see \Mistralys\MarkupTextReplacer\MarkupParser\Tokenizer
 */

declare(strict_types=1);

namespace Mistralys\MarkupTextReplacer\MarkupParser;

/**
 * The tokenizer uses the characters list generated
 * by the lexer to create a list of logical HTML tokens.
 * For each token, it puts the list of individual
 * characters back into the original strings.
 *
 * There are still no structural or validity checks
 * done at this stage. This happens next in the
 * {@see}
 *
 * @package MarkupTextReplacer
 * @subpackage Parser
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class Tokenizer
{
    public const TOKEN_TEXT = 'text';
    public const TOKEN_OPENING_TAG = 'opening-tag';
    public const TOKEN_CLOSING_TAG = 'closing-tag';
    public const TOKEN_DOCTYPE = 'doctype';
    public const TOKEN_COMMENT = 'comment';

    /**
     * @var array<int,array{type:string,char:string}|array{type:string,char:string,tagName:string}>
     */
    private array $stack = array();

    private string $openTag = '';

    /**
     * @var array<int,array{type:string,content:string}|array{type:string,content:string,tagName:string}>
     */
    private array $tokens = array();

    /**
     * @var array<string,string>
     */
    private static array $typeMethods = array(
        Lexer::CHAR_OPEN_COMMENT => array(self::class, 'handleOpenComment')[1],
        Lexer::CHAR_OPEN_OPENING_TAG => array(self::class, 'handleOpenOpeningTag')[1],
        Lexer::CHAR_OPEN_CLOSING_TAG => array(self::class, 'handleOpenClosingTag')[1],
        Lexer::CHAR_OPEN_DOCTYPE => array(self::class, 'handleOpenDoctype')[1],
        Lexer::CHAR_CLOSE_DOCTYPE => array(self::class, 'handleCloseDoctype')[1],
        Lexer::CHAR_CLOSE_TAG => array(self::class, 'handleCloseTag')[1],
        Lexer::CHAR_CLOSE_COMMENT => array(self::class, 'handleCloseComment')[1],
        Lexer::CHAR_MISC_CHARACTER => array(self::class, 'handleMiscCharacter')[1],
    );

    public function __construct(Lexer $lexer)
    {
        $chars = $lexer->getChars();

        foreach($chars as $charDef)
        {
            $method = self::$typeMethods[$charDef['type']] ?? null;

            if($method !== null) {
                $this->$method($charDef);
            }
        }
    }

    /**
     * @return array<int,array{type:string,content:string}|array{type:string,content:string,tagName:string}>
     */
    public function getTokens() : array
    {
        return $this->tokens;
    }

    private function storeStack(string $type, ?string $tagName=null) : void
    {
        if(empty($this->stack)) {
            return;
        }

        $content = '';
        foreach($this->stack as $charDef) {
            $content .= $charDef['char'];
        }

        $token = array(
            'type' => $type,
            'content' => $content
        );

        if($tagName !== null) {
            $token['tagName'] = $tagName;
        }

        $this->tokens[] = $token;

        $this->stack = array();
    }

    private function handleOpenComment(array $charDef) : void
    {
        $this->storeStack(self::TOKEN_TEXT);

        $this->stack[] = $charDef;
    }

    private function handleCloseComment(array $charDef) : void
    {
        $this->stack[] = $charDef;

        $this->storeStack(self::TOKEN_COMMENT);
    }

    private function handleOpenOpeningTag(array $charDef) : void
    {
        $this->storeStack(self::TOKEN_TEXT);

        $this->openTag = self::TOKEN_OPENING_TAG;

        $this->stack[] = $charDef;
    }

    private function handleOpenClosingTag(array $charDef) : void
    {
        $this->storeStack(self::TOKEN_TEXT);

        $this->openTag = self::TOKEN_CLOSING_TAG;

        $this->stack[] = $charDef;
    }

    private function handleOpenDoctype(array $charDef) : void
    {
        $this->storeStack(self::TOKEN_TEXT);

        $this->stack[] = $charDef;
    }

    private function handleCloseTag(array $charDef) : void
    {
        $this->stack[] = $charDef;

        $this->storeStack($this->openTag, $this->stack[0]['tagName']);
    }

    private function handleCloseDoctype(array $charDef) : void
    {
        $this->stack[] = $charDef;

        $this->storeStack(self::TOKEN_DOCTYPE);
    }

    private function handleMiscCharacter(array $charDef) : void
    {
        $this->stack[] = $charDef;
    }
}
