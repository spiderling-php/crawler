<?php

namespace SP\Crawler\Test;

use DOMDocument;
use SP\Crawler\ElementMap;
use SP\Crawler\Reader;
use SP\Crawler\Element\Checkbox;

/**
 * @coversDefaultClass SP\Crawler\ElementMap
 */
class ElementMapTest extends AbstractTestCase
{
    /**
     * @covers ::__construct
     * @covers ::getReader
     * @covers ::getClassMap
     */
    public function testConstruct()
    {
        $elementMap = new ElementMap($this->reader, Reader::getInputMatchers());

        $this->assertSame($this->reader, $elementMap->getReader());
        $this->assertSame(Reader::getInputMatchers(), $elementMap->getClassMap());
    }

    public function dataElementMatches()
    {
        return [
            ['email', 'self::input', true],
            ['gender-2', 'self::input[@type="radio"]', true],
            ['form', 'self::input[@type="checkbox"]', false],
        ];
    }

    /**
     * @dataProvider dataElementMatches
     * @covers ::elementMatches
     */
    public function testElementMatches($id, $match, $expected)
    {
        $map = new ElementMap($this->reader, []);

        $element = $this->document->getElementById($id);
        $result = $map->elementMatches($element, $match);
        $this->assertSame($expected, $result);
    }

    /**
     * @covers ::getElementClass
     */
    public function testGetElementClass()
    {
        $map = new ElementMap($this->reader, [
            'Class2' => 'self::input[@type="radio"]',
            'Class1' => 'self::input',
        ]);

        $email = $this->document->getElementById('email');

        $result = $map->getElementClass($this->document->getElementById('email'));
        $this->assertEquals('Class1', $result);

        $result = $map->getElementClass($this->document->getElementById('gender-2'));
        $this->assertEquals('Class2', $result);

        $this->setExpectedException('InvalidArgumentException', 'form is not an interactive element');

        $map->getElementClass($this->document->getElementById('form'));
    }

    /**
     * @covers ::create
     */
    public function testCreate()
    {
        $element = $this->document->getElementById('notifyme');

        $map = $this
            ->getMockBuilder('SP\Crawler\ElementMap')
            ->setConstructorArgs([$this->reader, []])
            ->setMethods(['getElementClass'])
            ->getMock();

        $map
            ->expects($this->once())
            ->method('getElementClass')
            ->with($element)
            ->willReturn('SP\Crawler\Element\Checkbox');


        $result = $map->create($element);
        $this->assertInstanceOf('SP\Crawler\Element\Checkbox', $result);
    }

    /**
     * @covers ::get
     */
    public function testGet()
    {
        $element = $this->document->getElementById('notifyme');
        $input = new Checkbox($this->reader, $element);

        $map = $this
            ->getMockBuilder('SP\Crawler\ElementMap')
            ->setConstructorArgs([$this->reader, []])
            ->setMethods(['create'])
            ->getMock();

        $map
            ->expects($this->once())
            ->method('create')
            ->with($element)
            ->willReturn($input);

        $result = $map->get($element);
        $this->assertSame($input, $result);

        $result2 = $map->get($element);
        $this->assertSame($input, $result);
    }
}
