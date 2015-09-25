<?php

namespace SP\Crawler\Test\Element;

use SP\Crawler\Test\AbstractTestCase;
use DOMElement;

/**
 * @coversDefaultClass SP\Crawler\Element\AbstractElement
 */
class AbstractElementTest extends AbstractTestCase
{
    private $element;

    public function setUp()
    {
        parent::setUp();

        $domElement = $this->document->getElementById('email');

        $this->element = $this->getMockForAbstractClass(
            'SP\Crawler\Element\AbstractElement',
            [$this->crawler, $domElement]
        );
    }

    /**
     * @covers ::__construct
     * @covers ::getReader
     * @covers ::getElement
     */
    public function testConstruct()
    {
        $domElement = new DOMElement('div');

        $element = $this->getMockForAbstractClass(
            'SP\Crawler\Element\AbstractElement',
            [$this->crawler, $domElement]
        );

        $this->assertSame($this->crawler, $element->getReader());
        $this->assertSame($domElement, $element->getElement());
    }

    /**
     * @covers ::hasAttribute
     */
    public function testHasAttribute()
    {
        $this->assertTrue($this->element->hasAttribute('name'));
        $this->assertFalse($this->element->hasAttribute('href'));
    }

    /**
     * @covers ::getName
     */
    public function testGetName()
    {
        $this->assertEquals('email', $this->element->getName());
    }

    /**
     * @covers ::getAttribute
     */
    public function testGetAttribute()
    {
        $this->assertEquals('email', $this->element->getAttribute('name'));
        $this->assertEmpty($this->element->getAttribute('href'));
    }

    /**
     * @covers ::setAttribute
     */
    public function testSetAttribute()
    {
        $this->element->setAttribute('name', 'other');

        $this->assertEquals('other', $this->element->getElement()->getAttribute('name'));
    }

    /**
     * @covers ::removeAttribute
     */
    public function testRemoveAttribute()
    {
        $this->element->removeAttribute('name');

        $this->assertEquals('', $this->element->getElement()->getAttribute('name'));
    }

    /**
     * @covers ::isDisabled
     */
    public function testIsDisabled()
    {
        $this->assertFalse($this->element->isDisabled());

        $this->element->getElement()->setAttribute('disabled', 'disabled');

        $this->assertTrue($this->element->isDisabled());
    }

    /**
     * @covers ::getChildren
     */
    public function testGetChildren()
    {
        $domElement = $this->document->getElementById('index');

        $element = $this->getMockForAbstractClass(
            'SP\Crawler\Element\AbstractElement',
            [$this->crawler, $domElement]
        );

        $result = $element->getChildren('//a');

        $this->assertInstanceOf('DOMNodeList', $result);

        $this->assertEquals(3, $result->length);

        $this->assertMatchesSelector('a#navlink-1', $result->item(0));
        $this->assertMatchesSelector('a#navlink-2', $result->item(1));
        $this->assertMatchesSelector('a#navlink-3', $result->item(2));
    }
}
