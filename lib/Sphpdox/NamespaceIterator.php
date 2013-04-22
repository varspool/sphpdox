<?php

namespace Sphpdox;

use Sphpdox\Element\ClassElement;
use \IteratorAggregate;
use \ArrayIterator;

class NamespaceIterator implements IteratorAggregate
{
    protected $namespace;

    public function __construct($namespace)
    {
        $this->namespace = $namespace;
    }

    public function getIterator()
    {
        return new ArrayIterator($this->getElements());
    }

    public function getElements()
    {
        $classes = array();

        foreach (get_declared_classes() as $class) {
            if (substr($class, 0, strlen($this->namespace) + 1) != $this->namespace . '\\') {
                continue;
            }
            $classes[] = new ClassElement($class);
        }

        return $classes;
    }
}