<?php

declare(strict_types=1);

namespace Mistralys\MarkupTextReplacer\MarkupParser\Attributes;

class Attribute
{
    private string $matchedText;
    private string $text;
    private bool $modified = false;
    private string $name;

    public function __construct(string $matchedText, string $name, string $text)
    {
        $this->matchedText = $matchedText;
        $this->name = $name;
        $this->text = $text;
    }

    public function getMatchedText() : string
    {
        return $this->matchedText;
    }

    public function isModified() : bool
    {
        return $this->modified;
    }

    public function getName() : string
    {
        return $this->name;
    }

    public function getText() : string
    {
        return $this->text;
    }

    /**
     * @param string $text
     * @return $this
     */
    public function setText(string $text) : self
    {
        if($text !== $this->text)
        {
            $this->text = $text;
            $this->modified = true;
        }

        return $this;
    }

    public function render() : string
    {
        return sprintf(
            '%s="%s"',
            $this->getName(),
            $this->getText()
        );
    }
}
