<?php

declare(strict_types=1);

namespace Mistralys\MarkupTextReplacer\BaseFilters;

abstract class BaseAttributeFilter extends BaseFilter
{
    public const ALL_ATTRIBUTES = '*';

    /**
     * @return string[]|string
     */
    abstract public function getAttributeNames();

    abstract public function filterAttribute(string $tagName, string $name, string $value) : string;
}
