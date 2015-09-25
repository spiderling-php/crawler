<?php

namespace SP\Crawler\Test\Element;

use SP\Crawler\Element\Input;
use SP\Crawler\Test\AbstractTestCase;

/**
 * @coversDefaultClass SP\Crawler\Element\Input
 */
class InputTest extends AbstractTestCase
{
    public function dataGetValue()
    {
        return [
            ['email', 'tom@example.com'],
            ['name', 'Tomas'],
        ];
    }

    /**
     * @dataProvider dataGetValue
     * @covers ::getValue
     */
    public function testGetValue($id, $expected)
    {
        $domElement = $this->document->getElementById($id);

        $input = new Input($this->crawler, $domElement);

        $this->assertSame($expected, $input->getValue());
    }

    public function dataSetValue()
    {
        return [
            ['email', 'test', 'input#email[value="test"]'],
            ['name', 'other', 'input#name[value="other"]'],
        ];
    }

    /**
     * @dataProvider dataSetValue
     * @covers ::setValue
     */
    public function testSetValue($id, $value, $expected)
    {
        $domElement = $this->document->getElementById($id);

        $input = new Input($this->crawler, $domElement);

        $input->setValue($value);

        $this->assertMatchesSelector($expected, $domElement);
    }
}
