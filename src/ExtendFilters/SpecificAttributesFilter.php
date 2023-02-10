<?php

declare(strict_types=1);

namespace Mistralys\MarkupTextReplacer\ExtendFilters;

use Mistralys\MarkupTextReplacer\BaseFilters\BaseAttributeFilter;

abstract class SpecificAttributesFilter extends BaseAttributeFilter
{
    /**
     * @return string[]
     */
    public function getAttributeNames() : array
    {
        return $this->_getAttributeNames();
    }

    /**
     * @return string[]
     */
    abstract protected function _getAttributeNames() : array;

    abstract public function filterAttribute(string $tagName, string $name, string $value) : string;
}
