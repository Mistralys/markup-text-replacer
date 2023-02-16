<?php

declare(strict_types=1);

namespace Mistralys\MarkupTextReplacerTests\TestClasses;

use Mistralys\MarkupTextReplacer\Interfaces\LoggerInterface;
use Mistralys\MarkupTextReplacer\Interfaces\LoggerTrait;
use PHPUnit\Framework\TestCase;

abstract class TextReplacerTestCase extends TestCase implements LoggerInterface
{
    use LoggerTrait;

    protected function stripWhitespace(string $subject) : string
    {
        return str_replace(array("\n", "\r"), '', $subject);
    }
}
