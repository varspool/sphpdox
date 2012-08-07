<?php

namespace Sphpdox\Tests\Element;

use Sphpdox\Element\Element;
use TokenReflection\IReflection;
use Sphpdox\Tests\Test;

abstract class ElementTest extends Test
{
    public function testIndent()
    {
        $instance = new MockElement();

        $this->assertEquals('', $instance->indent(''), 'blank lines do not get indented');
        $this->assertRegExp('/^[ ]{7}\w/m', $instance->indent('    Something', 3, true));
    }
}

class MockElement extends Element
{
    public function __construct() {}

    public function __toString()
    {
        return '';
    }

    public function indent($output, $spaces = 3, $rewrap = false)
    {
        return parent::indent($output, $spaces, $rewrap);
    }
}
