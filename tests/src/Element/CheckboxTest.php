<?php

namespace SP\Crawler\Test\Element;

use SP\Crawler\Element\Checkbox;
use SP\Crawler\Test\AbstractTestCase;

/**
 * @coversDefaultClass SP\Crawler\Element\Checkbox
 */
class CheckboxTest extends AbstractTestCase
{
    public function dataGetValue()
    {
        return [
            ['notifyme', 'yes'],
            ['newsletters', 'test'],
        ];
    }

    /**
     * @dataProvider dataGetValue
     * @covers ::getValue
     */
    public function testGetValue($id, $expected)
    {
        $domElement = $this->document->getElementById($id);

        $input = new Checkbox($this->crawler, $domElement);

        $this->assertSame($expected, $input->getValue());
    }

    public function dataSetValue()
    {
        return [
            ['notifyme', true, 'input#notifyme[checked]'],
            ['notifyme', false, 'input#notifyme:not([checked])'],
            ['newsletters', true, 'input#newsletters[checked]'],
            ['newsletters', false, 'input#newsletters:not([checked])'],
        ];
    }

    /**
     * @dataProvider dataSetValue
     * @covers ::setValue
     */
    public function testSetValue($id, $value, $expected)
    {
        $domElement = $this->document->getElementById($id);

        $input = new Checkbox($this->crawler, $domElement);

        $input->setValue($value);

        $this->assertMatchesSelector($expected, $domElement);
    }
}
