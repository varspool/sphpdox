<?php

namespace Sphpdox\Element;

use Symfony\Component\Console\Output\OutputInterface;
use TokenReflection\IReflectionMethod;

/**
 * Method element
 */
class MethodElement extends Element
{
    /**
     * Constructor
     *
     * @param IReflectionMethod $method
     */
    public function __construct(IReflectionMethod $method)
    {
        parent::__construct($method);
    }

    /**
     * Gets an array of simplified information about the parameters of this
     * method
     *
     * @return array
     */
    protected function getParameterInfo()
    {
        $params = array();

        $parameters = $this->reflection->getParameters();
        foreach ($parameters as $parameter) {
            $params[$parameter->getName()] = array(
                'name'      => $parameter->getName(),
                'hint_type' => $parameter->getOriginalTypeHint(),
                'type'      => $parameter->getOriginalTypeHint(),
                'comment'   => null
            );

            if ($parameter->isDefaultValueAvailable()) {
                try {
                    $params[$parameter->getName()]['default'] = trim($parameter->getDefaultValueDefinition());
                } catch (\Exception\RuntimeException $e) {
                    // Just don't provide a default
                }
            }
        }

        $annotations = array_filter($this->getParser()->getAnnotations(), function ($v) {
            $e = explode(' ', $v);
            return isset($e[0]) && $e[0] == '@param';
        });
        foreach ($annotations as $parameter) {
            $parts = explode(' ', $parameter);

            if (count($parts) < 3) {
                continue;
            }

            $type = trim($parts[1]);
            $name = trim(str_replace('$', '', $parts[2]));
            $comment = trim(implode(' ', array_slice($parts, 3)));

            if (isset($params[$name])) {
                if ($params[$name]['type'] == null && $type) {
                    $params[$name]['type'] = $type;
                }
                if ($comment) {
                    $params[$name]['comment'] = $comment;
                }
            }
        }

        return $params;
    }

    /**
     * Gets the formal signature/declaration argument list ReST output
     *
     * @return string
     */
    protected function getArguments()
    {
        $strings = array();

        foreach ($this->getParameterInfo() as $name => $parameter) {
            $string = '';

            if ($parameter['hint_type']) {
                $string .= $parameter['hint_type'] . ' ';
            }

            $string .= '$' . $name;

            if (isset($parameter['default'])) {
                if ($parameter['default'] == '~~NOT RESOLVED~~') {
                    $parameter['default'] = '';
                }
                $string .= ' = ' . $parameter['default'];
            }

            $strings[] = $string;
        }

        return implode(', ', $strings);
    }

    /**
     * Gets an array of parameter information, in ReST format
     *
     * @return array
     */
    protected function getParameters()
    {
        $strings = array();

        foreach ($this->getParameterInfo() as $name => $parameter) {
            if ($parameter['type']) {
                $strings[] = ':type $' . $name . ': ' . $parameter['type'];
            }

            $string = ':param $' . $name . ':';

            if (isset($parameter['comment']) && $parameter['comment']) {
                $string .= ' ' . $parameter['comment'];
            }

             $strings[] = $string;
        }

        return $strings;
    }

    /**
     * Gets the return value ReST notation
     *
     * @return boolean|string
     */
    protected function getReturnValue()
    {
        $annotations = array_filter($this->getParser()->getAnnotations(), function ($v) {
            $e = explode(' ', $v);
            return isset($e[0]) && $e[0] == '@return';
        });
        foreach ($annotations as $parameter) {
            $parts = explode(' ', $parameter);

            if (count($parts) < 2) {
                continue;
            }

            $type = array_slice($parts, 1, 1);
            $type = $type[0];

            $comment = implode(' ', array_slice($parts, 2));

            $string = ':returns:';

            return sprintf(
                ':returns: %s%s',
                $type ?: 'unknown',
                $comment ? ' ' . $comment : ''
            );
        }

        return false;
    }

    /**
     * @see \Sphpdox\Element\Element::__toString()
     */
    public function __toString()
    {
        try {
            $arguments = $this->getArguments();
        } catch (\Exception $e) {
            $arguments = '';
        }

        $string = sprintf(".. php:method:: %s(%s)\n\n", $this->reflection->getName(), $this->getArguments());

        $parser = $this->getParser();

        if ($description = $parser->getDescription()) {
            $string .= $this->indent($description . "\n\n", 4, true);
        }

        $return = $this->getReturnValue();

        $annotations = array_merge(
            $this->getParameters(),
            $return ? array($return) : array()
        );

        if ($annotations) {
            $string .= $this->indent(implode("\n", $annotations), 4) . "\n";
        }

        return trim($string);
    }
}