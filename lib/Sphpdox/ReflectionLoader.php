<?php

namespace Sphpdox;

use \RecursiveIteratorIterator;
use \RecursiveDirectoryIterator;
use \IteratorAggregate;

class ReflectionLoader implements IteratorAggregate
{
    protected $fileExtension = '.php';
    protected $namespace;
    protected $namespaceSeparator = '\\';
    protected $path;

    public function __construct($namespace, $path)
    {
        $this->namespace = $namespace;
        $this->path = $path;
    }

    /**
     * @return \RecursiveIteratorIterator
     */
    public function getIterator()
    {
        return new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(
                $this->path
                    . DIRECTORY_SEPARATOR
                    . str_replace($this->namespaceSeparator, DIRECTORY_SEPARATOR, $this->namespace)
                    . DIRECTORY_SEPARATOR,
                RecursiveDirectoryIterator::FOLLOW_SYMLINKS
                    | RecursiveDirectoryIterator::SKIP_DOTS
            ),
            RecursiveIteratorIterator::LEAVES_ONLY
        );
    }

    public function loadAll()
    {
        foreach ($this->getIterator() as $file) {
            if ($file->getExtension() != 'php') continue;
            if (!preg_match('/^([A-Z0-9][A-Za-z0-9.]*)\.php$/', $file->getBaseName(), $matches)) continue;
            $class = $matches[1];

            if (!class_exists($matches[1])) {
                require_once $file->getPathName();
            }
        }
    }

    public function loadClass($class)
    {
        if (null === $this->namespace
                || $this->namespace . $this->namespaceSeparator === substr($class, 0, strlen($this->namespace . $this->namespaceSeparator))
        ) {
            $file = '';
            $namespace = '';

            if (false !== ($suffix_length = strripos($class, $this->namespaceSeparator))) {
                $namespace = substr($class, 0, $suffix_length);
                $class = substr($class, $suffix_length + 1);
                $file = str_replace($this->namespaceSeparator, DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
            }

            $file .= str_replace('_', DIRECTORY_SEPARATOR, $class) . $this->fileExtension;
            require_once ($this->path !== null ? $this->path . DIRECTORY_SEPARATOR : '') . $file;
        }
    }
}