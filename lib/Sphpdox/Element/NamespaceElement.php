<?php

namespace Sphpdox\Element;

use TokenReflection\ReflectionNamespace;
use Symfony\Component\Console\Output\OutputInterface;
use \DirectoryIterator;

class NamespaceElement extends Element
{
    /**
     * @var ReflectionNamespace
     */
    protected $reflection;

    protected $titles = array('`', ':', '\'', '"', '~', '^', '_', '*', '+', '#', '<', '>');

    public function __construct(ReflectionNamespace $namespace)
    {
        parent::__construct($namespace);
    }

    public function getPath()
    {
        return str_replace('\\', DIRECTORY_SEPARATOR, $this->reflection->getName());
    }

    protected function getSubElements()
    {
        $elements = array_merge(
            $this->getConstants()
        );

        return $elements;
    }

    protected function getConstants()
    {
        return array_map(function ($v) {
            return new ConstantElement($v);
        }, $this->reflection->getConstants());
    }


    protected function getClasses()
    {
        return array_map(function ($v) {
            return new ClassElement($v);
        }, $this->reflection->getClasses());
    }


    public function __toString()
    {
        $string = '';

        foreach ($this->getSubElements() as $element) {
            $e = $element->__toString();
            if ($e) {
                $string .= $this->indent($e, 4);
                $string .= "\n\n";
            }
        }

        return $string;
    }

    /**
     * @param string $basedir
     * @param OutputInterface $output
     */
    public function build($basedir, OutputInterface $output, array $options = array())
    {
        $path = $basedir;
        $parts = explode(DIRECTORY_SEPARATOR, $this->getPath());
        $target = $basedir . DIRECTORY_SEPARATOR . $this->getPath();

        foreach ($parts as $part) {
            if (!$part) continue;

            $path .= DIRECTORY_SEPARATOR . $part;

            if (!file_exists($path)) {
                $output->writeln(sprintf('<info>Creating namespace build directory: <comment>%s</comment></info>', $path));
                mkdir($path);
            }
        }

        foreach ($this->getClasses() as $element) {
            $element->build($target, $output);
        }

        $built_iterator = new DirectoryIterator($target);
        $index = $target . DIRECTORY_SEPARATOR . 'index.rst';

        $title = str_replace('\\', '\\\\', $this->reflection->getName());
        if (isset($options['title'])) {
            $title = $options['title'];
        }

        $depth = substr_count($this->reflection->getName(), '\\');

        $template = str_repeat($this->titles[$depth], strlen($title)) . "\n";
        $template .= $title . "\n";
        $template .= str_repeat($this->titles[$depth], strlen($title)) . "\n\n";
        $template .= $this->getNamespaceElement();

        $template .= ".. toctree::\n\n";

        foreach ($built_iterator as $file) {
            if ($file->isDot()) continue;
            if ($file->isFile() && !$file->getExtension() == 'rst') continue;
            if ($file->isFile() && substr($file->getBaseName(), 0, 1) == '.') continue;
            if ($file->getBaseName() == 'index.rst') continue;

            $template .= '   ' . pathinfo($file->getPathName(), PATHINFO_FILENAME);

            if ($file->isDir()) {
                $template .= '/index';
            }

            $template .= "\n";
        }

        file_put_contents($index, $template);
    }

    public function getNamespaceElement()
    {
        return '.. php:namespace: '
            . str_replace('\\', '\\\\', $this->reflection->getName())
            . "\n\n";
    }
}