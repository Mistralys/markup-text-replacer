<?php

declare(strict_types=1);

use Mistralys\MarkupTextReplacer\MarkupParser\Lexer;
use Mistralys\MarkupTextReplacerTests\TestClasses\TextReplacerTestCase;

final class TokenizerTests extends TextReplacerTestCase
{
    public function test_sdsd() : void
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

        $tokenizer = (new Lexer($html))
            ->createTokenizer();

    }
}
