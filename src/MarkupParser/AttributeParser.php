<?php

declare(strict_types=1);

namespace Mistralys\MarkupTextReplacer\MarkupParser;

use Mistralys\MarkupTextReplacer\MarkupParser\Attributes\Attribute;

class AttributeParser
{
    private string $tagMarkup;

    /**
     * @var Attribute[]
     */
    private array $attributes = array();

    public function __construct(string $tagMarkup)
    {
        $this->tagMarkup = $tagMarkup;

        $this->parse();
    }

    private function parse() : void
    {
        preg_match_all('/([^\t\r\n\f\/ >"\'=]+)="([^"]*)"/', $this->tagMarkup,$result);

        foreach($result[0] as $idx => $match)
        {
            $this->attributes[] = new Attribute($match, $result[1][$idx], $result[2][$idx]);
        }
    }

    public function render() : string
    {
        $markup = $this->tagMarkup;

        foreach($this->attributes as $attribute)
        {
            if($attribute->isModified())
            {
                $markup = str_replace(
                    $attribute->getMatchedText(),
                    $attribute->render(),
                    $markup
                );
            }
        }

        return $markup;
    }
}
