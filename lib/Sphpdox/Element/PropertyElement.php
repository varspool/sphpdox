<?php

namespace Sphpdox\Element;

use Symfony\Component\Console\Output\OutputInterface;
use TokenReflection\IReflectionProperty;

/**
 * Property element
 */
class PropertyElement extends Element
{
    public function __construct(IReflectionProperty $property)
    {
        $this->reflection = $property;
    }

    public function __toString()
    {
        $string = sprintf(".. php:attr:: %s\n\n", $this->reflection->getName());

        $parser = $this->getParser();

        if (strlen($parser->getDescription()) > 200) {
            exit;
        }

        $string .= $this->indent($parser->getDescription(), 4, true);

        return $string;
    }
}