<?php

namespace Sphpdox\Tests\Element;

use Sphpdox\Element\MethodElement;
use TokenReflection\Broker;
use TokenReflection\Broker\Backend\Memory;
use \Exception;

class MethodElementTest extends ElementTest
{
    protected $broker;
    protected $backend;

    public function setUp()
    {
        $this->backend = new Memory();
        $this->broker = new Broker($this->backend);
        $this->broker->processDirectory(__DIR__);
    }

    public function tearDown()
    {
        unset($this->backend);
        unset($this->broker);
    }

    /**
     * @param string $method
     * @throws InvalidArgumentException
     * @return MethodElement
     */
    public function getInstance($method = null)
    {
        if (!$method) {
            throw new InvalidArgumentException('Bad method');
        }
        $class = $this->broker->getClass(__CLASS__);
        return parent::getInstance($class->getMethod($method));
    }

    public function getClass()
    {
        return 'Sphpdox\Element\MethodElement';
    }

    public function testGetParameterInfo()
    {
        $element = (string)$this->getInstance('publicMethodWithArguments');
        $this->assertStringStartsWith('.. php:method:: ', $element);
        $this->assertRegExp('/^    Some short description$/m', $element, 'short description');
        $this->assertRegExp('/\n    \n    And a longer one here, with line breaks\n    \n/', $element, 'long description line breaks');
        $this->assertRegExp('/And Continuations On Multiple Lines!/', $element, 'continuations');
        $this->assertRegExp('/    :returns: int/', $element, 'return value');
        $this->assertContains('publicMethodWithArguments(bool $a, int $b, string $c, Exception $d, \Exception $e, Sphpdox\Element\MethodElement $f, boolean $g, $h, Exception $i)', $element, 'formal signature');
        $this->assertContains(':param $h:', $element, 'do not specify unknown types');
    }

    public function testPrivateMethod()
    {
    }

    // The methods below will be reflected and tested! Pretty meta.
    // Don't edit their

    /**
     * Some short description
     *
     * And a longer one here, with line breaks
     *
     * And Continuations
     * On Multiple Lines!
     *
     * @param bool $a
     * @param int $b
     * @param string $c
     * @param Exception $d
     * @param \Exception $e
     * @param Sphpdox\Element\MethodElement $f
     * @param boolean $g
     * @return int
     * @see Symfony\Component\Console\Command.Command::configure()
     */
    public function publicMethodWithArguments($a, $b, $c, $d, $e, $f, $g, $h, \Exception $i)
    {
    }

    /**
     * Some documentation for a protected method
     */
    protected function someProtectedMethod()
    {
    }

    /**
     * Some documentation for a private method
     *
     * @private
     */
    private function somePrivateAnnotationMethod()
    {
    }

    /**
     * Some documentation for a private method
     */
    private function somePrivateMethod()
    {
    }
}