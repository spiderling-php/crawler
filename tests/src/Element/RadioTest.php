<?php

namespace SP\Crawler\Test\Element;

use SP\Crawler\Element\Radio;
use SP\Crawler\Test\AbstractTestCase;

/**
 * @coversDefaultClass SP\Crawler\Element\Radio
 */
class RadioTest extends AbstractTestCase
{
    public function dataGetValue()
    {
        return [
            ['gender-1', 'male'],
            ['gender-2', 'female'],
        ];
    }

    /**
     * @dataProvider dataGetValue
     * @covers ::getValue
     */
    public function testGetValue($id, $expected)
    {
        $domElement = $this->document->getElementById($id);

        $input = new Radio($this->crawler, $domElement);

        $this->assertSame($expected, $input->getValue());
    }

    /**
     * @covers ::uncheckOthers
     * @covers ::click
     */
    public function testSetValue()
    {
        $male = $this->document->getElementById('gender-1');
        $female = $this->document->getElementById('gender-2');

        $radioMale = new Radio($this->crawler, $male);
        $radioFemale = new Radio($this->crawler, $female);

        $radioMale->click();

        $this->assertTrue($male->hasAttribute('checked'));
        $this->assertFalse($female->hasAttribute('checked'));

        $radioMale->click();

        $this->assertFalse($male->hasAttribute('checked'));
        $this->assertFalse($female->hasAttribute('checked'));

        $radioFemale->click();

        $this->assertFalse($male->hasAttribute('checked'));
        $this->assertTrue($female->hasAttribute('checked'));
    }
}
