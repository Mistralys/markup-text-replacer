<?php

declare(strict_types=1);

namespace Mistralys\MarkupTextReplacerTests\TestSuites\Parser;

use Mistralys\MarkupTextReplacer\MarkupParser\Parser;
use Mistralys\MarkupTextReplacerTests\TestClasses\TextReplacerTestCase;

final class ParserTests extends TextReplacerTestCase
{
    public function test_parse() : void
    {
        $parser = $this->createTestParser()->parse();

        //print_r($parser->renderNodeTree());

        $this->assertSame(3, $parser->countChildNodes(), 'Doctype, Newline, HTML taq');
        $this->assertSame(2, $parser->countChildNodes(false), 'Doctype, HTML tag');
    }

    public function test_restoreOriginalHTMLUnchanged() : void
    {
        $parser = $this->createTestParser()->parse();

        $this->assertSame($parser->render(), $this->testHTML);
    }

    private string $testHTML = <<<'EOT'
<!DOCTYPE html>
<html lang="en">
    <head>
        <title>Document title</title>
    </head>
    <!-- Comment here with a <br> tag -->
    <body class="dark-mode">
        <img src="/path/to/image.jpg" class="image-fluid" alt="Alt text">
        <p>
            Text with <strong>bold text</strong> here.
            Stray < tag brackets > will be ignored.
        </p>
    </body>
</html>
EOT;

    private function createTestParser() : Parser
    {
        return Parser::create($this->testHTML);
    }
}
