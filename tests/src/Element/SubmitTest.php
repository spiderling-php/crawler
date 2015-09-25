<?php

namespace SP\Crawler\Test\Element;

use SP\Crawler\Element\Submit;
use h4cc\Multipart\ParserSelector;
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

        $domElement = $this->document->getElementById('submit');

        $this->submit = new Submit($this->crawler, $domElement);
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
     * @covers ::click
     */
    public function testClick()
    {
        $request = $this->submit->click();

        $this->assertInstanceOf('GuzzleHttp\Psr7\Request', $request);
        $this->assertEquals('POST', $request->getMethod());
        $this->assertEquals('/test_functest/contact', (string) $request->getUri());

        parse_str((string) $request->getBody(), $body);

        $expected = [
            'email' => 'tom@example.com',
            'name' => 'Tomas',
            'gender' => 'female',
            'newsletters' => 'test',
            'message' => 'Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod
 tempor incididunt ut labore et dolore magna aliqua.',
            'country' => 'uk',
            'submit_input' => 'Submit Item',
        ];

        $this->assertEquals($expected, $body);
    }

    /**
     * @covers ::click
     */
    public function testClickGet()
    {
        $this->submit->getForm()->removeAttribute('method');

        $request = $this->submit->click();

        $this->assertInstanceOf('GuzzleHttp\Psr7\Request', $request);
        $this->assertEquals('GET', $request->getMethod());
        $this->assertEquals('/test_functest/contact', $request->getUri()->getPath());

        $expected = [
            'email' => 'tom@example.com',
            'name' => 'Tomas',
            'gender' => 'female',
            'newsletters' => 'test',
            'message' => 'Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod
 tempor incididunt ut labore et dolore magna aliqua.',
            'country' => 'uk',
            'submit_input' => 'Submit Item',
        ];

        parse_str((string) $request->getUri()->getQuery(), $body);

        $this->assertEquals($expected, $body);
    }

    /**
     * @covers ::click
     */
    public function testClickMultipart()
    {
        $this->submit->getForm()->setAttribute('enctype', 'multipart/form-data');
        $this->submit->getFormElement()->setMultipartBoundary('56054f939e50e');

        $file = $this->document->getElementById('file');
        $file->setAttribute('value', self::getFilesDir().'file.txt');

        $request = $this->submit->click();

        $expected = file_get_contents(self::getFilesDir().'multipart.txt');

        $this->assertEquals($expected, (string) $request->getBody());
    }
}
