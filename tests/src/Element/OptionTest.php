<?php

namespace SP\Crawler\Test\Element;

use SP\Crawler\Element\Option;
use SP\Crawler\Element\Select;
use SP\Crawler\Test\AbstractTestCase;

/**
 * @coversDefaultClass SP\Crawler\Element\Option
 */
class OptionTest extends AbstractTestCase
{
    public function dataGetValue()
    {
        return [
            ['uk', 'uk'],
            ['bulgaria', 'bulgaria'],
        ];
    }

    /**
     * @dataProvider dataGetValue
     * @covers ::getValue
     */
    public function testGetValue($id, $expected)
    {
        $domElement = $this->document->getElementById($id);

        $input = new Option($this->crawler, $domElement);

        $this->assertSame($expected, $input->getValue());
    }

    public function dataGetSelect()
    {
        return [
            ['uk', 'select#country'],
            ['bulgaria', 'select#country'],
        ];
    }

    /**
     * @dataProvider dataGetSelect
     * @covers ::getValue
     */
    public function testGetSelect($id, $expected)
    {
        $domElement = $this->document->getElementById($id);

        $input = new Option($this->crawler, $domElement);

        $this->assertMatchesSelector($expected, $input->getSelect());
    }

    public function dataSetValue()
    {
        return [
            ['uk', 'test', 'option#uk[value=test]'],
            ['uk', 'other', 'option#uk[value=other]'],
        ];
    }

    /**
     * @dataProvider dataSetValue
     * @covers ::setValue
     */
    public function testSetValue($id, $value, $expected)
    {
        $domElement = $this->document->getElementById($id);

        $input = new Option($this->crawler, $domElement);

        $input->setvalue($value);

        $this->assertMatchesSelector($expected, $domElement);
    }

    /**
     * @covers ::unselectOthers
     * @covers ::getSelect
     */
    public function testUnselectOthers()
    {
        $bulgaria = $this->document->getElementById('bulgaria');
        $uk = $this->document->getElementById('uk');

        $input = new Option($this->crawler, $bulgaria);

        $input->unselectOthers();

        $this->assertMatchesSelector('option:not([selected])', $bulgaria);
        $this->assertMatchesSelector('option:not([selected])', $uk);
    }

    /**
     * @covers ::select
     * @covers ::unselect
     */
    public function testSelect()
    {
        $uk = $this->document->getElementById('uk');
        $bulgaria = $this->document->getElementById('bulgaria');
        $country = $this->document->getElementById('country');

        $optionUk = new Option($this->crawler, $uk);
        $optionBulgaria = new Option($this->crawler, $bulgaria);
        $select = new Select($this->crawler, $country);

        $optionUk->unselect();

        $this->assertMatchesSelector('option:not([selected])', $uk);
        $this->assertMatchesSelector('option:not([selected])', $bulgaria);

        $this->assertSame(false, $select->getValue());

        $optionBulgaria->select();

        $this->assertMatchesSelector('option:not([selected])', $uk);
        $this->assertMatchesSelector('option[selected]', $bulgaria);

        $this->assertSame('bulgaria', $select->getValue());
    }
}
