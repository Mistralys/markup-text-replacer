<?php

declare(strict_types=1);

namespace Mistralys\MarkupTextReplacer\MarkupParser;

use AppUtils\ConvertHelper;
use Mistralys\MarkupTextReplacer\Interfaces\LoggerInterface;
use Mistralys\MarkupTextReplacer\Interfaces\LoggerTrait;

class Lexer implements LoggerInterface
{
    use LoggerTrait;

    public const CHAR_MISC_CHARACTER = 'char';
    public const CHAR_CLOSE_COMMENT = 'close-comment';
    public const CHAR_CLOSE_DOCTYPE = 'close-doctype';
    public const CHAR_OPEN_DOCTYPE = 'open-doctype';
    public const CHAR_OPEN_CLOSING_TAG = 'open-closing-tag';
    public const CHAR_OPEN_OPENING_TAG = 'open-opening-tag';
    public const CHAR_CLOSE_TAG = 'close-tag';

    /**
     * @var string[]
     */
    private array $chars;

    private array $lexedChars = array();

    public function __construct(string $markup)
    {
        $this->chars = ConvertHelper::string2array($markup);
        $this->parse();
    }

    /**
     * @return array<int,array{type:string,char:string}|array{type:string,char:string,tagName:string}>
     */
    public function getLexedChars() : array
    {
        return $this->lexedChars;
    }

    private function parse() : void
    {
        $open = false;
        $total = count($this->chars);
        $inComment = false;
        $inDoctype = false;
        $doctypeDone = false;
        $nameStack = array();
        $openTagName = '';

        for($i=0; $i < $total; $i++)
        {
            $char = $this->chars[$i];
            $logPrefix = sprintf('Parse [%04d:%s] |', $i, ConvertHelper::hidden2visible($char));

            // If in a comment, continue capturing characters
            // until we reach the closing tag - excluding closing
            // tags that do not match "-->".
            if($inComment)
            {
                if($char === '>' && $this->lookBack($i-1, '--') !== -1)
                {
                    $this->logTrivial('%s Closing the comment.', $logPrefix);

                    $inComment = false;
                    $this->addLexedType(self::CHAR_CLOSE_COMMENT, $char);
                    continue;
                }

                $this->logTrivial('%s Adding to the comment.', $logPrefix);

                $this->addMiscCharacter($char);
                continue;
            }

            if($inDoctype)
            {
                if($char === '>')
                {
                    $this->logTrivial('%s Closing the doctype.', $logPrefix);

                    $inDoctype = false;
                    $doctypeDone = true;
                    $this->addLexedType(self::CHAR_CLOSE_DOCTYPE, $char);
                    continue;
                }

                $this->logTrivial('%s Adding to the doctype tag.', $logPrefix);

                $this->addMiscCharacter($char);
                continue;
            }

            if($char === '<')
            {
                // Try to detect the doctype. We're doing this first, as
                // it is always right at the start - until we're sure it
                // can not appear anymore.
                if(!$doctypeDone && $this->lookAhead($i+1, '!DOCTYPE') !== -1)
                {
                    $this->logTrivial('%s OpenBracket | Detected the doctype tag.', $logPrefix);

                    $this->addLexedType(self::CHAR_OPEN_DOCTYPE, $char);
                    $inDoctype = true;
                    continue;
                }

                // If there's a slash next, we are looking at a closing
                // tag, e.g. </p>.
                $slashPosition = $this->lookAhead($i+1, '/');
                if($slashPosition !== -1)
                {
                    $name = $this->detectTagName($slashPosition+1);

                    if($name !== '')
                    {
                        $this->logTrivial('%s OpenBracket | Detected the [%s] closing tag.', $logPrefix, $name);

                        $this->addLexedTag(self::CHAR_OPEN_CLOSING_TAG, $char, $name);

                        $open = true;
                        $nameStack[] = $name;
                        $doctypeDone = true;
                        continue;
                    }

                    $this->logTrivial('%s OpenBracket | Found a slash, but no matching tag name.', $logPrefix);

                    // There is a slash, but no tag name - this
                    // is no HTML tag.
                    $this->addMiscCharacter($char);
                    continue;
                }

                // Is this an opening comment?
                if($this->lookAhead($i+1, '!--') !== -1)
                {
                    $this->logTrivial('%s OpenBracket | Found an opening comment.', $logPrefix);

                    $this->addLexedType('open-comment', $char);
                    $inComment = true;
                    continue;
                }

                if(!$open)
                {
                    $name = $this->detectTagName($i+1);

                    if($name !== '')
                    {
                        $this->logTrivial('%s OpenBracket | Detected the tag [%s].', $logPrefix, $name);

                        $this->addLexedTag(self::CHAR_OPEN_OPENING_TAG, $char, $name);

                        $open = true;
                        $openTagName = $name;
                        $nameStack[] = $name;

                        // No tag can come before the doctype, so
                        // we can assume none will be forthcoming.
                        $doctypeDone = true;

                        continue;
                    }

                    $this->logTrivial('%s OpenBracket | No tag name detected, ignoring.', $logPrefix);
                    $this->addMiscCharacter($char);
                    continue;
                }

                $this->logTrivial('%s OpenBracket | Another tag is already open, ignore.', $logPrefix);
                $this->addMiscCharacter($char);
                continue;
            }

            if($char === '>')
            {
                if($open)
                {
                    if(empty($nameStack))
                    {
                        $this->addMiscCharacter($char);
                        continue;
                    }

                    $name = array_pop($nameStack);

                    $this->logTrivial('%s CloseBracket | Closing the open tag [%s].', $logPrefix, $name);

                    $this->addLexedTag(self::CHAR_CLOSE_TAG, $char, $name);
                    $open = false;
                    $openTagName = '';
                    continue;
                }

                $this->logTrivial('%s CloseBracket | No tag currently open, ignoring.', $logPrefix);
            }

            if($open)
            {
                $this->logTrivial('%s Tag [%s] | Adding character.', $logPrefix, $openTagName);
            }
            else
            {
                $this->logTrivial('%s Adding miscellaneous character.', $logPrefix);
            }

            $this->addMiscCharacter($char);
        }
    }

    public function clear() : self
    {
        unset($this->chars, $this->lexedChars);
        return $this;
    }

    private function addLexedTag(string $type, string $char, string $name) : void
    {
        $this->lexedChars[] = array(
            'type' => $type,
            'char' => $char,
            'tagName' => $name
        );
    }

    private function addMiscCharacter(string $char) : void
    {
        $this->lexedChars[] = array(
            'type' => self::CHAR_MISC_CHARACTER,
            'char' => $char
        );
    }

    private function addLexedType(string $type, string $char) : void
    {
        $this->lexedChars[] = array(
            'type' => $type,
            'char' => $char
        );
    }

    public function detectTagName(int $pos) : string
    {
        $seekingStarted = false;
        $name = '';
        $seekPos = $pos;

        $this->logDebug('DetectTagName | Starting at position [%s].', $pos);

        while(true)
        {
            if(!isset($this->chars[$seekPos])) {
                $this->logDebug('DetectTagName | Position [%s] | Reached the end > BREAK', $seekPos);
                return $name;
            }

            $seek = $this->chars[$seekPos];

            $this->logDebug('DetectTagName | Position [%s] | Char [%s]', $seekPos, $seek);

            if($seekingStarted && ($seek === '>' || $seek === '/' || $this->isWhitespace($seek)))
            {
                $this->logDebug('DetectTagName | Position [%s] | [%s] after name > DONE', $seekPos, $seek);
                return $name;
            }

            $seekingStarted = true;

            if($seek === '_' || $seek === ':' || ctype_alnum($seek))
            {
                $name .= $seek;

                $this->logDebug('DetectTagName | Position [%s] | Added character: [%s] > NEXT', $seekPos, $name);

                $seekPos++;
                continue;
            }

            $this->logDebug('DetectTagName | Position [%s] | Invalid character [%s] > BREAK', $seekPos, $seek);
            return '';
        }
    }

    public function isWhitespace(string $char) : bool
    {
        return ctype_space($char);
    }

    public function lookBack(int $pos, string $matchSequence) : int
    {
        $matchChars = ConvertHelper::string2array($matchSequence);
        $seek = $pos;
        $matchChar = array_pop($matchChars);
        $startPos = -1;

        $this->logDebug('LookBack | Position [%s] | Searching for [%s].', $pos, $matchSequence);

        while(true)
        {
            if(!isset($this->chars[$seek]))
            {
                $this->logDebug('LookBack | Position [%s] | Not found in sequence > BREAK', $seek);
                return -1;
            }

            $seekChar = $this->chars[$seek];

            $this->logDebug('LookBack | Position [%s] | Match char: [%s]', $seek, $matchChar);
            $this->logDebug('LookBack | Position [%s] | Seek char: [%s]', $seek, $seekChar);

            // Character is a match => Next character
            if($seekChar === $matchChar)
            {
                $this->logDebug('LookBack | Position [%s] | Is a match > PREV', $seek);

                if($startPos === -1) {
                    $startPos = $seek;
                }

                if(empty($matchChars)) {
                    return $startPos;
                }

                $seek--;
                $matchChar = array_pop($matchChars);

                continue;
            }

            // Ignore whitespace => Next char
            if(!$this->isWhitespace($matchChar) && $this->isWhitespace($seekChar))
            {
                $this->logDebug('LookBack | Position [%s] | Is whitespace > PREV', $seek);
                $seek--;
                continue;
            }

            $this->logDebug('LookBack | Position [%s] | Is no match > BREAK', $seek);
            return -1;
        }
    }
    public function lookAhead(int $pos, string $matchSequence) : int
    {
        $matchChars = ConvertHelper::string2array(mb_strtolower($matchSequence));
        $seek = $pos;
        $matchChar = array_shift($matchChars);
        $startPos = -1;

        $this->logDebug('LookAhead | Position [%s] | Searching for [%s].', $pos, $matchSequence);

        while(true)
        {
            if(!isset($this->chars[$seek]))
            {
                $this->logDebug('LookAhead | Position [%s] | Not found in sequence > BREAK', $seek);
                return -1;
            }

            $seekChar = mb_strtolower($this->chars[$seek]);

            $this->logDebug('LookAhead | Position [%s] | Match char: [%s]', $seek, $matchChar);
            $this->logDebug('LookAhead | Position [%s] | Seek char: [%s]', $seek, $seekChar);

            // Character is a match => Next character
            if($seekChar === $matchChar)
            {
                $this->logDebug('LookAhead | Position [%s] | Is a match > NEXT', $seek);

                if($startPos === -1) {
                    $startPos = $seek;
                }

                if(empty($matchChars)) {
                    return $startPos;
                }

                $seek++;
                $matchChar = array_shift($matchChars);

                continue;
            }

            // Ignore whitespace => Next char
            if(!$this->isWhitespace($matchChar) && $this->isWhitespace($seekChar))
            {
                $this->logDebug('LookAhead | Position [%s] | Is whitespace > NEXT', $seek);
                $seek++;
                continue;
            }

            $this->logDebug('LookAhead | Position [%s] | Is no match > BREAK', $seek);
            return -1;
        }
    }
}
