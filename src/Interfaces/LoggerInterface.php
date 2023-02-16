<?php

declare(strict_types=1);

namespace Mistralys\MarkupTextReplacer\Interfaces;

interface LoggerInterface
{
    public const LEVEL_DEBUG = 1;
    public const LEVEL_TRIVIAL = 2;
    public const LEVEL_INFO = 3;
    public const LEVEL_IMPORTANT = 4;
}
