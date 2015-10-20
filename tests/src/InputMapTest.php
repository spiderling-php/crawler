<?php

namespace SP\Crawler\Test;

use DOMDocument;
use SP\Crawler\InputMap;
use SP\Crawler\Reader;
use SP\Crawler\Element\Checkbox;

/**
 * @coversDefaultClass SP\Crawler\InputMap
 */
class InputMapTest extends AbstractTestCase
{
    /**
     * @covers ::getInputClasses
     */
    public function testGetInputMatchers()
    {
        $classes = InputMap::getInputClasses();

        $this->assertArrayHasKey('SP\Crawler\Element\Checkbox', $classes);
        $this->assertArrayHasKey('SP\Crawler\Element\File', $classes);
        $this->assertArrayHasKey('SP\Crawler\Element\Input', $classes);
        $this->assertArrayHasKey('SP\Crawler\Element\Option', $classes);
        $this->assertArrayHasKey('SP\Crawler\Element\Radio', $classes);
        $this->assertArrayHasKey('SP\Crawler\Element\Checkbox', $classes);
        $this->assertArrayHasKey('SP\Crawler\Element\Textarea', $classes);
        $this->assertArrayHasKey('SP\Crawler\Element\Anchor', $classes);
        $this->assertArrayHasKey('SP\Crawler\Element\Submit', $classes);
    }

    /**
     * @covers ::__construct
     * @covers ::getReader
     */
    public function testConstruct()
    {
        $elementMap = new InputMap($this->reader);

        $this->assertSame($this->reader, $elementMap->getReader());
    }

    public function dataInputMatches()
    {
        return [
            ['email', 'self::input', true],
            ['gender-2', 'self::input[@type="radio"]', true],
            ['form', 'self::input[@type="checkbox"]', false],
        ];
    }

    /**
     * @dataProvider dataInputMatches
     * @covers ::inputMatches
     */
    public function testInputMatches($id, $match, $expected)
    {
        $map = new InputMap($this->reader, []);

        $element = $this->document->getElementById($id);
        $result = $map->inputMatches($element, $match);
        $this->assertSame($expected, $result);
    }

    /**
     * @covers ::getInputClass
     */
    public function testGetInputClass()
    {
        $map = new InputMap($this->reader);

        $email = $this->document->getElementById('email');

        $result = $map->getInputClass($this->document->getElementById('email'));
        $this->assertEquals('SP\Crawler\Element\Input', $result);

        $result = $map->getInputClass($this->document->getElementById('gender-2'));
        $this->assertEquals('SP\Crawler\Element\Radio', $result);

        $this->setExpectedException('InvalidArgumentException', 'form is not an input');

        $map->getInputClass($this->document->getElementById('form'));
    }

    /**
     * @covers ::create
     */
    public function testCreate()
    {
        $element = $this->document->getElementById('notifyme');

        $map = $this
            ->getMockBuilder('SP\Crawler\InputMap')
            ->setConstructorArgs([$this->reader, []])
            ->setMethods(['getInputClass'])
            ->getMock();

        $map
            ->expects($this->once())
            ->method('getInputClass')
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
            ->getMockBuilder('SP\Crawler\InputMap')
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
