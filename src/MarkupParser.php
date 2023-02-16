<?php
/**
 * @package MarkupTextReplacer
 * @see \Mistralys\MarkupTextReplacer\MarkupParser
 */

declare(strict_types=1);

namespace Mistralys\MarkupTextReplacer;

use DOMAttr;
use DOMDocument;
use DOMElement;
use DOMNode;
use DOMNodeList;
use DOMText;
use Mistralys\MarkupTextReplacer\BaseFilters\BaseAttributeFilter;
use Mistralys\MarkupTextReplacer\BaseFilters\BaseFilter;
use Mistralys\MarkupTextReplacer\BaseFilters\BaseTextFilter;

/**
 * Parser with markup awareness: Allows replacing texts
 * DOM-style by applying filters to individual attribute
 * values and text nodes to avoid breaking the markup
 * code with global replacements like regexes.
 *
 * @package MarkupTextReplacer
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class MarkupParser
{
    public const ERROR_CANNOT_RENDER_MARKUP = 128501;

    public static function create() : MarkupParser
    {
        return new self();
    }

    /**
     * @param string $markup
     * @param bool $xml
     * @return string
     * @throws MarkupParserException
     */
    public function filter(string $markup, bool $xml=false) : string
    {
        $dom = new DOMDocument();
        $dom->preserveWhiteSpace = true;
        $dom->formatOutput = false;
        $dom->strictErrorChecking = false;
        $dom->validateOnParse = false;

        if($xml)
        {
            $dom->loadXML($markup);
        }
        else
        {
            $dom->loadHTML($markup);
        }

        $this->walkDom($dom->childNodes);

        $result = $dom->saveHTML();
        if ($result !== false)
        {
            return trim($result);
        }

        throw new MarkupParserException(
            'Could not render the filtered markup.',
            '',
            self::ERROR_CANNOT_RENDER_MARKUP
        );
    }

    // region: A - Configure filters

    public function addFilter(BaseFilter $filter) : self
    {
        if ($filter instanceof BaseAttributeFilter)
        {
            return $this->addAttributeFilter($filter);
        }

        if ($filter instanceof BaseTextFilter)
        {
            return $this->addTextFilter($filter);
        }

        return $this;
    }

    public function addTextFilter(BaseTextFilter $filter) : self
    {
        $names = $filter->getTagNames();

        if (!is_array($names))
        {
            return $this->registerTextFilter(BaseTextFilter::ALL_TAGS, $filter);
        }

        foreach ($names as $name)
        {
            $this->registerTextFilter(strtolower($name), $filter);
        }

        return $this;
    }

    public function addAttributeFilter(BaseAttributeFilter $filter) : self
    {
        $names = $filter->getAttributeNames();

        if (!is_array($names))
        {
            return $this->registerAttributeFilter(BaseAttributeFilter::ALL_ATTRIBUTES, $filter);
        }

        foreach ($names as $name)
        {
            $this->registerAttributeFilter(strtolower($name), $filter);
        }

        return $this;
    }

    /**
     * @var array<string,BaseAttributeFilter[]>
     */
    private array $attributeFilters = array();

    /**
     * @param string $key
     * @param BaseAttributeFilter $filter
     * @return $this
     */
    private function registerAttributeFilter(string $key, BaseAttributeFilter $filter) : self
    {
        if (!isset($this->attributeFilters[$key]))
        {
            $this->attributeFilters[$key] = array();
        }

        $this->attributeFilters[$key][] = $filter;

        return $this;
    }

    /**
     * @var array<string,BaseTextFilter[]>
     */
    private array $textFilters = array();

    private function registerTextFilter(string $key, BaseTextFilter $filter) : self
    {
        if (!isset($this->textFilters[$key]))
        {
            $this->textFilters[$key] = array();
        }

        $this->textFilters[$key][] = $filter;

        return $this;
    }

    // endregion

    // region: B - Parse Markup

    private function walkAttribute(DOMElement $element, DOMAttr $attribute) : void
    {
        $attribute->value = $this->filterAttribute(
            $element->nodeName,
            $attribute->name,
            $attribute->value
        );
    }

    /**
     * @param DOMNodeList $nodes
     * @return void
     */
    private function walkDom(DOMNodeList $nodes) : void
    {
        foreach ($nodes as $node)
        {
            $this->walkNode($node);
        }
    }

    private function walkNode(DOMNode $node) : void
    {
        if ($node instanceof DOMText)
        {
            $this->walkTextNode($node);
            return;
        }

        if ($node instanceof DOMElement)
        {
            $this->walkElementNode($node);
        }
    }

    private function walkTextNode(DOMText $node) : void
    {
        $text = $node->textContent;

        if (empty(trim($text)))
        {
            return;
        }

        $node->textContent = $this->filterText($node->parentNode->nodeName, $text);
    }

    private function walkElementNode(DOMElement $element) : void
    {
        foreach ($element->attributes as $attribute)
        {
            if ($attribute instanceof DOMAttr)
            {
                $this->walkAttribute($element, $attribute);
            }
        }

        $this->walkDom($element->childNodes);
    }

    // endregion

    // region: C - Apply filters

    private function filterAttribute(string $tagName, string $name, string $value) : string
    {
        $filtered = $this->_filterAttribute($tagName, $name, $value);

        if ($filtered !== $value)
        {
            $this->registerAttributeChange($tagName, $name, $value, $filtered);
        }

        return $filtered;
    }

    /**
     * Goes through attribute filters in this order:
     *
     * 1) Specific tag + attribute combination, e.g. "img:alt"
     * 2) Specific attribute, e.g. "label"
     * 3) Wildcard filters for all attributes
     *
     * @param string $tagName
     * @param string $name
     * @param string $value
     * @return string
     */
    private function _filterAttribute(string $tagName, string $name, string $value) : string
    {
        $value = $this->_filterAttributeByKey($tagName . ':' . $name, $tagName, $name, $value);
        $value = $this->_filterAttributeByKey($name, $tagName, $name, $value);
        return $this->_filterAttributeByKey(BaseAttributeFilter::ALL_ATTRIBUTES, $tagName, $name, $value);
    }

    private function _filterAttributeByKey(string $key, string $tagName, string $name, string $value) : string
    {
        if (!isset($this->attributeFilters[$key]))
        {
            return $value;
        }

        foreach ($this->attributeFilters[$key] as $filter)
        {
            $value = $filter->filterAttribute($tagName, $name, $value);
        }

        return $value;
    }

    private function filterText(string $tagName, string $text) : string
    {
        $filtered = $this->_filterText($tagName, $text);

        if ($this->trackModifications !== false && $filtered !== $text)
        {
            $this->registerTextChange($text, $filtered, $tagName);
        }

        return $filtered;
    }

    private function _filterText($tagName, $text) : string
    {
        $text = $this->_filterTextByKey($tagName, $tagName, $text);
        return $this->_filterTextByKey(BaseTextFilter::ALL_TAGS, $tagName, $text);
    }

    private function _filterTextByKey(string $key, string $tagName, string $text) : string
    {
        if (!isset($this->textFilters[$key]))
        {
            return $text;
        }

        foreach ($this->textFilters[$key] as $filter)
        {
            $text = $filter->filterText($tagName, $text);
        }

        return $text;
    }

    // endregion

    // region: D - Track changes

    private bool $trackModifications = false;

    /**
     * @return array<int,array<string,string>>
     */
    public function getModified() : array
    {
        return $this->modified;
    }

    /**
     * @var array<int,array<string,string>>
     */
    private array $modified = array();

    private function registerTextChange(string $originalText, string $filteredText, string $tagName) : void
    {
        $this->modified[] = array(
            'type' => 'text',
            'tag' => $tagName,
            'original' => $originalText,
            'filtered' => $filteredText
        );
    }

    private function registerAttributeChange(string $tagName, string $attributeName, string $originalText, string $filteredText) : void
    {
        $this->modified[] = array(
            'type' => 'attribute',
            'tag' => $tagName,
            'attrib' => $attributeName,
            'original' => $originalText,
            'filtered' => $filteredText
        );
    }

    // endregion
}
