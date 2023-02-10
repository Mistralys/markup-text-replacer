<?php

declare(strict_types=1);

namespace Mistralys\MarkupTextReplacer\ExtendFilters;

use Mistralys\MarkupTextReplacer\BaseFilters\BaseAttributeFilter;

abstract class AllAttributesFilter extends BaseAttributeFilter
{
    public function getAttributeNames() : string
    {
        return self::ALL_ATTRIBUTES;
    }

    abstract public function filterAttribute(string $tagName, string $name, string $value) : string;
}
