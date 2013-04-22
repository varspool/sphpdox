<?php

namespace Sphpdox\Element;

use Sphpdox\CommentParser;
use TokenReflection\IReflection;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Represents a code element that can be documented with PHPDoc/Sphinx
 */
abstract class Element
{
    protected $reflection;

    /**
     * Constructor
     *
     * @param IReflection $reflection
     */
    public function __construct(IReflection $reflection)
    {
        $this->reflection = $reflection;
    }

    /**
     */
    protected function getParser()
    {
        return new CommentParser($this->reflection->getDocComment());
    }

    /**
     * Gets ReST markup for this element
     */
    abstract public function __toString();

    /**
     * Indents the given lines
     *
     * @param string $output
     * @param int $level
     */
    protected function indent($output, $spaces = 3, $rewrap = false)
    {
        if (!$output) {
            return '';
        }

        $line = 78;
        $spaces = str_pad(' ', $spaces);

        if ($rewrap) {
            $existing_indent = '';
            if (preg_match('/^( +)/', $output, $matches)) {
                $spaces .= $matches[1];
            }
            $output = preg_replace('/^ +/m', '', $output);
            $output = wordwrap($output, $line - strlen($spaces));
        }

        $output = preg_replace('/^/m', $spaces, $output);

        return $output;
    }
}