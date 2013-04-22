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
        if ($this->reflection->isPrivate()) {
            return '';
        }

        $parser = $this->getParser();

        if ($parser->hasAnnotation('private')) {
            return '';
        }

        $string = sprintf(".. php:attr:: %s\n\n", $this->reflection->getName());
        $string .= $this->getModifierLine();


        $string .= $this->indent($parser->getDescription(), 4, true);

        return $string;
    }

    protected function getModifierLine()
    {
        $line = '';
        $line .= $this->getVisibilityModifier() . ' ';
        $line .= $this->getTypeModifier() . ' ';
        $line = trim($line);

        if ($line) {
            $line = $this->indent($line, 4, true);
            $line .= "\n\n";
        }

        return $line;
    }

    protected function getVisibilityModifier()
    {
        if ($this->reflection->isProtected()) {
             return 'protected';
        }
    }

    protected function getTypeModifier()
    {
        $vars = $this->getParser()->getAnnotationsByName('var');
        $var = count($vars) ? array_pop($vars) : false;
        $parts = preg_split('/\s+/', $var);

        if (count($parts) >= 2) {
            if ($parts[1]) {
                return $parts[1];
            }
        }
    }
}