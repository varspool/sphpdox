<?php

namespace Sphpdox\Element;

use TokenReflection\ReflectionClass;

use Symfony\Component\Console\Output\OutputInterface;


/**
 * Class element
 */
class ClassElement extends Element
{
    /**
     * @var ReflectionClass
     */
    protected $reflection;

    /**
     * Constructor
     *
     * @param string $classname
     * @throws InvalidArgumentException
     */
    public function __construct(ReflectionClass $reflection)
    {
        parent::__construct($reflection);
    }

    public function getPath()
    {
        return $this->reflection->getShortName() . '.rst';
    }

    /**
     * @param string $basedir
     * @param OutputInterface $output
     */
    public function build($basedir, OutputInterface $output)
    {
        $file = $basedir . DIRECTORY_SEPARATOR . $this->getPath();
        file_put_contents($file, $this->__toString());
    }

    /**
     * @see Sphpdox\Element.Element::__toString()
     */
    public function __toString()
    {
        $name = $this->reflection->getName();

        $title = str_replace('\\', '\\\\', $name);
        //$title = $name;

        $string = str_repeat('-', strlen($title)) . "\n";
        $string .= $title . "\n";
        $string .= str_repeat('-', strlen($title)) . "\n\n";
        $string .= $this->getNamespaceElement();
        $string .= '.. php:class:: ' . $this->reflection->getShortName();

        $parser = $this->getParser();

        if ($description = $parser->getDescription()) {
            $string .= "\n\n";
            $string .= $this->indent($description, 4);
        }

        foreach ($this->getSubElements() as $element) {
            $e = $element->__toString();
            if ($e) {
                $string .= "\n\n";
                $string .= $this->indent($e, 4);
            }
        }

        $string .= "\n\n";

        // Finally, fix some whitespace errors
        $string = preg_replace('/^\s+$/m', '', $string);
        $string = preg_replace('/ +$/m', '', $string);

        return $string;
    }

    protected function getSubElements()
    {
        $elements = array_merge(
            $this->getConstants(),
            $this->getProperties(),
            $this->getMethods()
        );

        return $elements;
    }

    protected function getConstants()
    {
        return array_map(function ($v) {
            return new ConstantElement($v);
        }, $this->reflection->getConstantReflections());
    }

    protected function getProperties()
    {
        return array_map(function ($v) {
            return new PropertyElement($v);
        }, $this->reflection->getProperties());
    }

    protected function getMethods()
    {
        return array_map(function ($v) {
            return new MethodElement($v);
        }, $this->reflection->getMethods());
    }

    public function getNamespaceElement()
    {
        return '.. php:namespace: '
            . str_replace('\\', '\\\\', $this->reflection->getNamespaceName())
            . "\n\n";
    }
}