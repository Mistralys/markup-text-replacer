<?php

declare(strict_types=1);

namespace Mistralys\MarkupTextReplacer\ExtendFilters;

use Mistralys\MarkupTextReplacer\BaseFilters\BaseTextFilter;

abstract class AllTagsFilter extends BaseTextFilter
{
    public function getTagNames() : string
    {
        return self::ALL_TAGS;
    }
}