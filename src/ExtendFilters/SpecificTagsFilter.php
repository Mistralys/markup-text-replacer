<?php

declare(strict_types=1);

namespace Mistralys\MarkupTextReplacer\ExtendFilters;

use Mistralys\MarkupTextReplacer\BaseFilters\BaseTextFilter;

abstract class SpecificTagsFilter extends BaseTextFilter
{
    /**
     * @return string[]
     */
    public function getTagNames() : array
    {
        return $this->_getTagNames();
    }

    /**
     * @return string[]
     */
    abstract protected function _getTagNames() : array;
}