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
            ['notifyme', 'test', 'input#notifyme[value=test]'],
            ['notifyme', 'other', 'input#notifyme[value=other]'],
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

        $input->setvalue($value);

        $this->assertMatchesSelector($expected, $domElement);
    }

    /**
     * @covers ::click
     */
    public function testClick()
    {
        $domElement = $this->document->getElementById('notifyme');

        $input = new Checkbox($this->crawler, $domElement);

        $input->click();

        $this->assertMatchesSelector('input[checked]', $domElement);

        $input->click();

        $this->assertNotMatchesSelector('input[checked]', $domElement);
    }
}
