<?php

namespace Sphpdox\Element;

use Symfony\Component\Console\Output\OutputInterface;
use TokenReflection\IReflectionMethod;

/**
 * Method element
 */
class MethodElement extends Element
{
    public function __construct(IReflectionMethod $method)
    {
        $this->reflection = $method;
    }

    protected function getParameterInfo()
    {
        $params = array();

        $parameters = $this->reflection->getParameters();
        foreach ($parameters as $parameter) {
            $params[$parameter->getName()] = array(
                'name' => $parameter->getName(),
                'type' => $parameter->getOriginalTypeHint(),
            );

            if ($parameter->isDefaultValueAvailable()) {
                $params[$parameter->getName()]['default'] = $parameter->getDefaultValue();
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

            $type = $parts[1];
            $name = str_replace('$', '', $parts[2]);
            $comment = implode(' ', array_slice($parts, 3));

            if (isset($params[$name])) {
                if ($params[$name]['type'] == null) {
                    $params[$name]['type'] = $type;
                }
                $params[$name]['comment'] = $comment;
            }
        }

        return $params;
    }

    protected function getArguments()
    {
        $strings = array();

        foreach ($this->getParameterInfo() as $name => $parameter) {
            $string = '';

            if ($parameter['type']) {
                $string .= $parameter['type'] . ' ';
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
     * @return array
     */
    protected function getParameters()
    {
        $strings = array();

        foreach ($this->getParameterInfo() as $name => $parameter) {
            $string = ':param ';

            if ($parameter['type']) {
                $string .= $parameter['type'] . ' ';
            } else {
                $string .= '';
            }

            $string .= '$';
            $string .= $name;
            $string .= ': ';

            if (isset($parameter['comment'])) {
                $string .= $parameter['comment'];
            }

            $strings[] = $string;
        }

        return $strings;
    }

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

    public function __toString()
    {
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