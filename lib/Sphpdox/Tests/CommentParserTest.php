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
        $this->assertEquals($shortDescription, $instance->getShortDescription(), 'short description');
        $this->assertEquals($longDescription, $instance->getLongDescription(), 'long description');
        $this->assertEquals($annotations, $instance->getAnnotations(), 'annotations');
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