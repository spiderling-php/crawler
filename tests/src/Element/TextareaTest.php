<?php

namespace SP\Crawler\Test\Element;

use SP\Crawler\Element\Textarea;
use SP\Crawler\Test\AbstractTestCase;

/**
 * @coversDefaultClass SP\Crawler\Element\Textarea
 */
class TextareaTest extends AbstractTestCase
{
    /**
     * @covers ::getValue
     */
    public function testGetValue()
    {
        $domElement = $this->document->getElementById('message');

        $input = new Textarea($this->crawler, $domElement);

        $expected = <<<TEXT
Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod
 tempor incididunt ut labore et dolore magna aliqua.
TEXT;

        $this->assertSame($expected, $input->getValue());
    }

    /**
     * @covers ::setValue
     */
    public function testSetValue()
    {
        $domElement = $this->document->getElementById('message');

        $input = new Textarea($this->crawler, $domElement);

        $input->setValue('test');

        $this->assertSame('test', $input->getValue());
    }
}
