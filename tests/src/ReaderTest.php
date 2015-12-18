<?php

namespace SP\Crawler\Test;

use SP\PhpunitDomConstraints\DomConstraintsTrait;
use DOMDocument;
use DOMElement;
use SP\Crawler\Reader;
use SP\Crawler\Element\Option;
use GuzzleHttp\Psr7\Uri;
use GuzzleHttp\Psr7\Request;

/**
 * @coversDefaultClass SP\Crawler\Reader
 */
class ReaderTest extends AbstractTestCase
{
    /**
     * @covers ::__construct
     * @covers ::getDocument
     * @covers ::getXPath
     * @covers ::getInputMap
     */
    public function testConstruct()
    {
        $document = new DOMDocument();
        $reader = new Reader($document);

        $this->assertSame($document, $reader->getDocument());

        $this->assertInstanceOf('DOMXPath', $reader->getXPath());
        $this->assertSame($document, $reader->getXPath()->document);

        $this->assertInstanceOf('SP\Crawler\InputMap', $reader->getInputMap());
        $this->assertSame($reader, $reader->getInputMap()->getReader());
    }

    /**
     * @covers ::getUri
     */
    public function testGetUri()
    {
        $this->assertInstanceOf('Psr\Http\Message\UriInterface', $this->reader->getUri());
    }

    /**
     * @covers ::open
     */
    public function testOpen()
    {
        $this->setExpectedException(
            'BadMethodCallException',
            'Method SP\Crawler\Reader::open not supported by SP\Crawler\Reader'
        );

        $this->reader->open(new Uri('test id'));
    }

    /**
     * @covers ::sendRequest
     */
    public function testSendRequest()
    {
        $this->setExpectedException(
            'BadMethodCallException',
            'Cannot send request to /'
        );

        $this->reader->sendRequest(new Request('GET', '/'));
    }

    /**
     * @covers ::click
     */
    public function testClickable()
    {
        $element = $this->document->getElementById('navlink-1');

        $reader = $this
            ->getMockBuilder('SP\Crawler\Reader')
            ->setConstructorArgs([$this->document])
            ->setMethods(['getInput', 'getElement'])
            ->getMock();

        $input = $this->getMock('SP\Crawler\Element\ClickableInterface');

        $input
            ->expects($this->once())
            ->method('click');

        $reader
            ->expects($this->once())
            ->method('getElement')
            ->with('test id')
            ->willReturn($element);

        $reader
            ->expects($this->once())
            ->method('getInput')
            ->with($element)
            ->willReturn($input);

        $reader->click('test id');
    }

    /**
     * @covers ::click
     */
    public function testClickRequest()
    {
        $element = $this->document->getElementById('navlink-1');
        $request = new Request('GET', 'http://example.com');

        $reader = $this
            ->getMockBuilder('SP\Crawler\Reader')
            ->setConstructorArgs([$this->document])
            ->setMethods(['getInput', 'getElement', 'sendRequest'])
            ->getMock();

        $input = $this->getMock('SP\Crawler\Element\ClickRequestInterface');

        $input
            ->expects($this->once())
            ->method('clickRequest')
            ->willReturn($request);

        $reader
            ->expects($this->once())
            ->method('getElement')
            ->with('test id')
            ->willReturn($element);

        $reader
            ->expects($this->once())
            ->method('getInput')
            ->with($element)
            ->willReturn($input);

        $reader
            ->expects($this->once())
            ->method('sendRequest')
            ->with($request);

        $reader->click('test id');
    }

    /**
     * @covers ::select
     */
    public function testSelect()
    {
        $element = $this->document->getElementById('uk');

        $reader = $this
            ->getMockBuilder('SP\Crawler\Reader')
            ->setConstructorArgs([$this->document])
            ->setMethods(['getInput', 'getElement'])
            ->getMock();

        $input = $this->getMock('SP\Crawler\Element\SelectableInterface');

        $input
            ->expects($this->once())
            ->method('select');

        $reader
            ->expects($this->once())
            ->method('getElement')
            ->with('test id')
            ->willReturn($element);

        $reader
            ->expects($this->once())
            ->method('getInput')
            ->with($element)
            ->willReturn($input);

        $reader->select('test id');
    }

    /**
     * @covers ::click
     */
    public function testClickException()
    {
        $reader = new Reader($this->document);

        $this->setExpectedException(
            'BadMethodCallException',
            'Cannot click on SP\Crawler\Element\Option, //option[@id="uk"]'
        );

        $reader->click('//option[@id="uk"]');
    }

    /**
     * @covers ::select
     */
    public function testSelectException()
    {
        $reader = new Reader($this->document);

        $this->setExpectedException(
            'BadMethodCallException',
            'Cannot select on SP\Crawler\Element\Anchor, //a[@id="navlink-1"]'
        );

        $reader->select('//a[@id="navlink-1"]');
    }

    /**
     * @covers ::query
     */
    public function testQuery()
    {
        $index = $this->document->getElementById('index');

        $items = $this->reader->query('//form', $index);

        $this->assertEquals(1, $items->length);
        $this->assertMatchesSelector('form#form', $items->item(0));
    }

    /**
     * @covers ::getElement
     */
    public function testGetElement()
    {
        $form = $this->reader->getElement('//div[@class="page"]//p');

        $this->assertMatchesSelector('p#p-1', $form);

        $this->setExpectedException(
            'InvalidArgumentException',
            'Node with id //div[@id="unknown"] does not exist'
        );

        $this->reader->getElement('//div[@id="unknown"]');
    }

    /**
     * @covers ::getText
     */
    public function testGetText()
    {
        $this->assertEquals(
            'Subpage 1',
            $this->reader->getText('//a[@title="Subpage Title 1"]')
        );
    }

    /**
     * @covers ::getTagName
     */
    public function testGetTagName()
    {
        $this->assertEquals(
            'a',
            $this->reader->getTagName('//a[@title="Subpage Title 1"]')
        );
    }

    /**
     * @covers ::getAttribute
     */
    public function testGetAttribute()
    {
        $this->assertEquals(
            'navlink',
            $this->reader->getAttribute('//a[@title="Subpage Title 1"]', 'class')
        );
    }

    /**
     * @covers ::getHtml
     */
    public function testGetHtml()
    {
        $this->assertEquals(
            '<img alt="icon 1" src="icon1.png"/>',
            $this->reader->getHtml('//a[@title="Subpage Title 1"]/img')
        );
    }

    /**
     * @covers ::getHtml
     */
    public function testGetFullHtml()
    {
        $this->assertContains(
            '<title></title>',
            $this->reader->getFullHtml()
        );
    }

    public function dataIsVisible()
    {
        return [
            ['//div[@id="index"]', true],
            ['//a[@href="/test_functest/subpage1"]', true],
            ['//p[@id="p-3"]', false],
        ];
    }

    /**
     * @dataProvider dataIsVisible
     * @covers ::isVisible
     */
    public function testIsVisible($id, $expected)
    {
        $result = $this->reader->isVisible($id);
        $this->assertSame($expected, $result);
    }

    public function dataIsSelected()
    {
        return [
            ['//textarea[@name="message"]', false],
            ['//select[@id="country"]', false],
            ['//option[@value="uk"]', true],
            ['//option[@value="us"]', false],
        ];
    }

    /**
     * @dataProvider dataIsSelected
     * @covers ::isSelected
     */
    public function testIsSelected($id, $expected)
    {
        $result = $this->reader->isSelected($id);
        $this->assertSame($expected, $result);
    }

    public function dataIsChecked()
    {
        return [
            ['//textarea[@name="message"]', false],
            ['//select[@id="country"]', false],
            ['//input[@id="gender-1"]', false],
            ['//input[@id="gender-2"]', true],
            ['//input[@id="newsletters"]', true],
            ['//input[@id="notifyme"]', false],
        ];
    }

    /**
     * @dataProvider dataIsChecked
     * @covers ::isChecked
     */
    public function testIsChecked($id, $expected)
    {
        $result = $this->reader->isChecked($id);
        $this->assertSame($expected, $result);
    }

    public function dataGetInput()
    {
        return [
            ['email'       , 'SP\Crawler\Element\Input'],
            ['message'     , 'SP\Crawler\Element\Textarea'],
            ['gender-2'    , 'SP\Crawler\Element\Radio'],
            ['newsletters' , 'SP\Crawler\Element\Checkbox'],
            ['country'     , 'SP\Crawler\Element\Select'],
            ['uk'          , 'SP\Crawler\Element\Option'],
            ['file'        , 'SP\Crawler\Element\File'],
            ['navlink-1'   , 'SP\Crawler\Element\Anchor'],
            ['submit-btn'  , 'SP\Crawler\Element\Submit'],
        ];
    }

    /**
     * @dataProvider dataGetInput
     * @covers ::getInput
     */
    public function testGetInput($id, $class)
    {
        $element = $this->document->getElementById($id);
        $result = $this->reader->getInput($element);
        $this->assertInstanceOf($class, $result);
        $this->assertSame($this->reader, $result->getReader());
    }

    /**
     * @covers ::getValue
     */
    public function testGetValue()
    {
        $element = $this->document->getElementById('message');

        $reader = $this
            ->getMockBuilder('SP\Crawler\Reader')
            ->setConstructorArgs([$this->document])
            ->setMethods(['getInput'])
            ->getMock();

        $input = $this->getMock('SP\Crawler\Element\InputInterface');

        $input
            ->expects($this->once())
            ->method('getValue')
            ->willReturn('test text');

        $reader
            ->expects($this->once())
            ->method('getInput')
            ->with($element)
            ->willReturn($input);

        $result = $reader->getValue('//textarea');
        $this->assertEquals('test text', $result);
    }

    /**
     * @covers ::setValue
     */
    public function testSetValue()
    {
        $element = $this->document->getElementById('message');

        $reader = $this
            ->getMockBuilder('SP\Crawler\Reader')
            ->setConstructorArgs([$this->document])
            ->setMethods(['getInput'])
            ->getMock();

        $input = $this->getMock('SP\Crawler\Element\InputInterface');

        $input
            ->expects($this->once())
            ->method('isDisabled')
            ->willReturn(false);

        $input
            ->expects($this->once())
            ->method('setValue')
            ->with('new value');

        $reader
            ->expects($this->once())
            ->method('getInput')
            ->with($element)
            ->willReturn($input);

        $reader->setValue('//textarea', 'new value');
    }

    /**
     * @covers ::setFile
     */
    public function testSetFile()
    {
        $element = $this->document->getElementById('file');

        $reader = $this
            ->getMockBuilder('SP\Crawler\Reader')
            ->setConstructorArgs([$this->document])
            ->setMethods(['getInput'])
            ->getMock();

        $input = $this->getMockBuilder('SP\Crawler\Element\File')
            ->disableOriginalConstructor()
            ->getMock();

        $input
            ->expects($this->once())
            ->method('isDisabled')
            ->willReturn(false);

        $input
            ->expects($this->once())
            ->method('setValue')
            ->with('/var/usr/example.jpg');

        $reader
            ->expects($this->once())
            ->method('getInput')
            ->with($element)
            ->willReturn($input);

        $reader->setFile('//*[@id="file"]', '/var/usr/example.jpg');
    }

    /**
     * @covers ::setFile
     */
    public function testSetFileOnInput()
    {
        $element = $this->document->getElementById('message');

        $input = $this->getMockBuilder('SP\Crawler\Element\Textarea')
            ->disableOriginalConstructor()
            ->getMock();

        $reader = $this
            ->getMockBuilder('SP\Crawler\Reader')
            ->setConstructorArgs([$this->document])
            ->setMethods(['getInput'])
            ->getMock();

        $reader
            ->expects($this->once())
            ->method('getInput')
            ->with($element)
            ->willReturn($input);

        $this->setExpectedException(
            'InvalidArgumentException',
            'Node with id //textarea is not a file'
        );

        $reader->setFile('//textarea', '/var/usr/example.jpg');
    }

    /**
     * @covers ::queryIds
     */
    public function testQueryIds()
    {
        $filters = $this->getMock('SP\Spiderling\Query\Filters');

        $query = $this->getMockForAbstractClass(
            'SP\Spiderling\Query\AbstractQuery',
            ['selector', $filters]
        );

        $query
            ->expects($this->once())
            ->method('getXPath')
            ->willReturn('//div[@class="page"]/p');

        $filters
            ->expects($this->once())
            ->method('matchAll')
            ->with(
                $this->reader,
                [
                    '(//body//div[@class="page"]/p)[1]',
                    '(//body//div[@class="page"]/p)[2]',
                    '(//body//div[@class="page"]/p)[3]',
                ]
            )
            ->willReturn([
                '(//body//div[@class="page"]/p)[2]',
                '(//body//div[@class="page"]/p)[3]',
            ]);

        $result = $this->reader->queryIds($query, '//body');

        $expected = [
            '(//body//div[@class="page"]/p)[2]',
            '(//body//div[@class="page"]/p)[3]'
        ];

        $this->assertEquals($expected, $result);
    }
}
