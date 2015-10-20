<?php

namespace SP\Crawler\Test\Element;

use SP\Crawler\Element\Submit;
use GuzzleHttp\Psr7\Request;
use SP\Crawler\Test\AbstractTestCase;

/**
 * @coversDefaultClass SP\Crawler\Element\Submit
 */
class SubmitTest extends AbstractTestCase
{
    private $submit;

    public function setUp()
    {
        parent::setUp();

        $this->domElement = $this->document->getElementById('submit');

        $this->submit = new Submit($this->crawler, $this->domElement);
    }

    /**
     * @covers ::getForm
     * @covers ::getFormElement
     */
    public function testGetForm()
    {
        $this->assertMatchesSelector('form#form', $this->submit->getForm());

        $this->assertInstanceOf('SP\Crawler\Element\Form', $this->submit->getFormElement());
        $this->assertMatchesSelector('form#form', $this->submit->getFormElement()->getElement());
    }

    /**
     * @covers ::getDefaultData
     */
    public function testGetDefaultData()
    {
        $data = $this->submit->getDefaultData();

        $expected = ['submit input' => 'Submit Item'];

        $this->assertEquals($expected, $data);
    }

    /**
     * @covers ::clickRequest
     */
    public function testClickRequest()
    {
        $request = new Request('GET', '/test');
        $data = ['test' => 'testval'];

        $submit = $this
            ->getMockBuilder('SP\Crawler\Element\Submit')
            ->setMethods(['getFormElement', 'getAttribute'])
            ->disableOriginalConstructor()
            ->getMock();

        $form = $this
            ->getMockBuilder('SP\Crawler\Element\Form')
            ->setMethods(['getRequest'])
            ->disableOriginalConstructor()
            ->getMock();

        $submit
            ->method('getAttribute')
            ->will($this->returnValueMap([
                ['name', 'test'],
                ['value', 'testval'],
            ]));

        $submit
            ->method('getFormElement')
            ->willReturn($form);

        $form
            ->expects($this->once())
            ->method('getRequest')
            ->with($data)
            ->willReturn($request);

        $this->assertSame($request, $submit->clickRequest());
    }
}
