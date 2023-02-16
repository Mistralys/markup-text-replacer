<?php

declare(strict_types=1);

namespace Mistralys\MarkupTextReplacerTests\TestSuites\Parser;

use Mistralys\MarkupTextReplacer\MarkupParser\Lexer;
use Mistralys\MarkupTextReplacerTests\TestClasses\TextReplacerTestCase;

final class LexerTests extends TextReplacerTestCase
{
    public function test_detectTagName() : void
    {
        $lexer = new Lexer('<tagname');
        $this->assertSame('tagname', $lexer->detectTagName(1));

        $lexer = new Lexer('<tagname  ');
        $this->assertSame('tagname', $lexer->detectTagName(1));

        $lexer = new Lexer('<tag_name ');
        $this->assertSame('tag_name', $lexer->detectTagName(1));

        $lexer = new Lexer('<TAG_NAME8 ');
        $this->assertSame('TAG_NAME8', $lexer->detectTagName(1));

        $lexer = new Lexer('<tag#name ');
        $this->assertSame('', $lexer->detectTagName(1));

        $lexer = new Lexer('<   ');
        $this->assertSame('', $lexer->detectTagName(1));

        $lexer = new Lexer('< tagname');
        $this->assertSame('', $lexer->detectTagName(1));

        $lexer = new Lexer('<namespace:name ');
        $this->assertSame('namespace:name', $lexer->detectTagName(1));
    }

    public function test_lookAhead() : void
    {
        $lexer = new Lexer('< !  -'.PHP_EOL.'-');
        $this->assertSame(2, $lexer->lookAhead(1, '!--'));

        $lexer = new Lexer('<!DOCTYPE');
        $this->assertSame(1, $lexer->lookAhead(1, '!DOCTYPE'));

        $lexer = new Lexer('</name');
        $this->assertSame(1, $lexer->lookAhead(1, '/'));

        $lexer = new Lexer('<name/>');
        $this->assertSame(-1, $lexer->lookAhead(1, '/'));
    }

    public function test_lookBack() : void
    {
        $lexer = new Lexer(' > - - >');
        $this->assertSame(5, $lexer->lookBack(6, '--'));
    }
}
