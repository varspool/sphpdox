<?php

namespace Sphpdox\Tests;

class CommentParserTest extends Test
{
    public function getClass()
    {
        return 'Sphpdox\CommentParser';
    }

    /**
     * @dataProvider getValidComments
     */
    public function testParse($comment, $shortDescription, $longDescription, $annotations)
    {
        $instance = $this->getInstance($comment);

        $this->assertInstanceOfClass($instance);

        if ($shortDescription) {
            $this->assertTrue($instance->hasDescription(), 'has description');
        }

        $this->assertEquals($shortDescription, $instance->getShortDescription(), 'short description');
        $this->assertEquals($longDescription, $instance->getLongDescription(), 'long description');
        $this->assertEquals($annotations, $instance->getAnnotations(), 'annotations');
    }

    public function testGetAnnotationsByName()
    {
        $instance = $this->getInstance('
            /**
             * A description
             *
             * @foo
             * @bar
             * @foo wow amazing really long annotations even.
             *        that can be split across lines.
             * @bar
             * @foo with content
             *
             * Some content in between here. Tricky
             *
             * @foo more content
             */
        ');

        $annotations = $instance->getAnnotationsByName('foo');

        $this->assertCount(4, $annotations, 'correct number of annotations');
        $this->assertEquals('@foo', $annotations[0], 'blank annotation');
        $this->assertEquals('@foo wow amazing really long annotations even. that can be split across lines.', $annotations[1], 'blank annotation');
        $this->assertEquals('@foo with content', $annotations[2], 'blank annotation');
        $this->assertEquals('@foo more content', $annotations[3], 'blank annotation');
        $this->assertEquals(array(), $instance->getAnnotationsByName('failron'));
    }

    public function testHasAnnotation()
    {
        $instance = $this->getInstance('
            /**
             *
             * @private
             *
             * Some content in between here. Tricky
             *
             * @magic And a comment about how it is magic
             */
        ');

        $this->assertTrue($instance->hasAnnotation('magic'));
        $this->assertTrue($instance->hasAnnotation('private'));
        $this->assertFalse($instance->hasAnnotation('foo'));
    }

    public function getValidComments()
    {
        return array(
            array(
                '
                /**
                 * Some description
                 *
                 * Some long description
                 *
                 * @param string $paramName Some description
                 * @param mixed $anotherParam This time the description is
                 *     split across two lines
                 * @param mixed $aThird And there\'s another behind it
                 * @return void
                 */
                ',
                'Some description',
                'Some long description',
                array(
                    '@param string $paramName Some description',
                    '@param mixed $anotherParam This time the description is split across two lines',
                    '@param mixed $aThird And there\'s another behind it',
                    '@return void'
                )
            ),
            array(
                '
                /**
                 * Only a short description
                 *
                 * @param string $paramName Some description
                 * @param mixed $anotherParam This time the description is
                 *     split across two lines
                 * @param mixed $aThird And there\'s another behind it
                 * @return void
                 */
                ',
                'Only a short description',
                null,
                array(
                    '@param string $paramName Some description',
                    '@param mixed $anotherParam This time the description is split across two lines',
                    '@param mixed $aThird And there\'s another behind it',
                    '@return void'
                )
            ),
            array(
                '
                /**
                 * @param string $paramName Only annotations!
                 * @param mixed $anotherParam This time the description is
                 *     split across two lines
                 * @param mixed $aThird And there\'s another behind it
                 * @return void
                 */
                ',
                null,
                null,
                array(
                    '@param string $paramName Only annotations!',
                    '@param mixed $anotherParam This time the description is split across two lines',
                    '@param mixed $aThird And there\'s another behind it',
                    '@return void'
                )
            ),
            array(
                '
                /**
                 * Only a short description
                 */
                ',
                'Only a short description',
                null,
                array()
            ),
            array(
                '
                /**
                 * A short description
                 *
                 * And an extra long multi-line long description that also spans
                 * multiple paragraphs.
                 *
                 * See, told you so.
                 */
                ',
                'A short description',
                "And an extra long multi-line long description that also spans multiple paragraphs.\n\nSee, told you so.",
                array()
            ),
            array(
                '
                /**
                 * @param string $paramName Only annotations!
                 * @param mixed $anotherParam This time the description is
                 *     split across two lines
                 * @param mixed $aThird And there\'s another behind it
                 * @return void
                 *
                 * Annotations and description in weird order
                 */
                ',
                'Annotations and description in weird order',
                null,
                array(
                    '@param string $paramName Only annotations!',
                    '@param mixed $anotherParam This time the description is split across two lines',
                    '@param mixed $aThird And there\'s another behind it',
                    '@return void'
                )
            )
        );
    }
}