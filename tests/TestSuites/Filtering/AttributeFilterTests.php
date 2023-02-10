<?php

declare(strict_types=1);

namespace Mistralys\MarkupTextReplacerTests\TestSuites\Filtering;

use Mistralys\MarkupTextReplacer\MarkupParser;
use Mistralys\MarkupTextReplacerTests\TestClasses\TestAllAttributesFilter;
use Mistralys\MarkupTextReplacerTests\TestClasses\TextReplacerTestCase;

final class AttributeFilterTests extends TextReplacerTestCase
{
    /**
     * A dumb filter that replaces all attributes regardless
     * of content.
     */
    public function test_create() : void
    {
        $html = <<<'EOT'
<!DOCTYPE html>
<html lang="en">
    <head>
        <title>Document title</title>
    </head>
    <body class="dark-mode">
        <img src="/path/to/image.jpg" class="image-fluid" alt="Alt text">
    </body>
</html>
EOT;

        $expected = <<<'EOT'
<!DOCTYPE html>
<html lang="{REPLACED}">
    <head>
        <title>Document title</title>
    </head>
    <body class="{REPLACED}">
        <img src="%7BREPLACED%7D" class="{REPLACED}" alt="{REPLACED}">
    </body>
</html>
EOT;

        // Stripping the whitespace for the comparison, because
        // the DOM parser will always make small changes that
        // can safely be ignored.
        $this->assertSame(
            $this->stripWhitespace($expected),
            $this->stripWhitespace(MarkupParser::create()
                ->addAttributeFilter(new TestAllAttributesFilter())
                ->filter($html))
        );
    }

    private function stripWhitespace(string $subject) : string
    {
        return str_replace(array("\n", "\r"), '', $subject);
    }
}
