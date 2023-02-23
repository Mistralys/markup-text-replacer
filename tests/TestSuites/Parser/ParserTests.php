<?php

declare(strict_types=1);

namespace Mistralys\MarkupTextReplacerTests\TestSuites\Parser;

use Mistralys\MarkupTextReplacer\MarkupParser\Lexer;
use Mistralys\MarkupTextReplacer\MarkupParser\Parser;
use Mistralys\MarkupTextReplacerTests\TestClasses\TextReplacerTestCase;

final class ParserTests extends TextReplacerTestCase
{
    public function test_tree() : void
    {
        $html = <<<'EOT'
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

        $parser = (new Parser(
            (new Lexer($html))
            ->createTokenizer()
        ))
            ->setAllLoggingEnabled()
        ->parse();

        $result = array();
        $parser->renderNodeTree($result);
        print_r($result);
    }
}
