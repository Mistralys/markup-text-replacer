<?php

declare(strict_types=1);

namespace Mistralys\MarkupTextReplacer\BaseFilters;

abstract class BaseTextFilter extends BaseFilter
{
    public const ALL_TAGS = '*';

    /**
     * @return string[]|string List of tag names to limit the filter to, or a wildcard (*) to filter all tags.
     */
    abstract public function getTagNames();

    abstract public function filterText(string $tagName, string $text) : string;
}
