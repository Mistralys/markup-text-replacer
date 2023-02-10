<?php

declare(strict_types=1);

namespace Mistralys\MarkupTextReplacerTests\TestClasses;

use Mistralys\MarkupTextReplacer\ExtendFilters\AllAttributesFilter;

final class TestAllAttributesFilter extends AllAttributesFilter
{
    public const TEXT_REPLACED = '{REPLACED}';

    public function filterAttribute(string $tagName, string $name, string $value) : string
    {
        return self::TEXT_REPLACED;
    }
}
