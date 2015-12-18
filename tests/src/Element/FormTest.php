<?php

namespace SP\Crawler\Test\Element;

use SP\Crawler\Element\Form;
use SP\Crawler\Test\AbstractTestCase;

/**
 * @coversDefaultClass SP\Crawler\Element\Form
 */
class FormTest extends AbstractTestCase
{
    /**
     * @var Form
     */
    private $form;

    public function setUp()
    {
        parent::setUp();

        $domElement = $this->document->getElementById('form');
        $this->form = new Form($this->crawler, $domElement);
    }

    /**
     * @covers ::getFieldsXPath
     */
    public function testGetFieldsMathers()
    {
        $fieldsXpath = Form::getFieldsXPath();

        $result = $this->crawler->query($fieldsXpath);

        $expected = [
            'input#email',
            'input#name',
            'input#gender-2',
            'input#newsletters',
            'textarea#message',
            'select#country',
        ];

        $this->assertEquals(count($expected), $result->length);

        foreach ($expected as $index => $selector) {
            $this->assertMatchesSelector($selector, $result->item($index));
        }
    }

    /**
     * @covers ::getFilesXPath
     */
    public function testGetFilesMathers()
    {
        $fieldsXpath = Form::getFilesXPath();

        $result = $this->crawler->query($fieldsXpath);

        $this->assertEquals(1, $result->length);
        $this->assertMatchesSelector('input#file', $result->item(0));
    }

    /**
     * @covers ::getMultipartBoundary
     * @covers ::setMultipartBoundary
     */
    public function testMultipartBoundary()
    {
        $this->assertNotEmpty($this->form->getMultipartBoundary());

        $boundary = 'test';

        $this->form->setMultipartBoundary($boundary);

        $this->assertEquals($boundary, $this->form->getMultipartBoundary());
    }

    /**
     * @covers ::getInputs
     */
    public function testGetInputs()
    {
        $result = $this->form->getInputs('//*[@id="email" or @id="message"]');

        $this->assertCount(2, $result);

        $this->assertInstanceOf('SP\Crawler\Element\Input', $result[0]);
        $this->assertMatchesSelector('input#email', $result[0]->getElement());

        $this->assertInstanceOf('SP\Crawler\Element\Textarea', $result[1]);
        $this->assertMatchesSelector('textarea#message', $result[1]->getElement());
    }

    /**
     * @covers ::getMethod
     */
    public function testGetMethod()
    {
        $this->assertEquals('post', $this->form->getMethod());

        $this->form->setAttribute('method', 'get');

        $this->assertEquals('get', $this->form->getMethod());

        $this->form->removeAttribute('method');

        $this->assertEquals('GET', $this->form->getMethod());
    }

    /**
     * @covers ::isGet
     */
    public function testIsGet()
    {
        $this->assertFalse($this->form->isGet());
        $this->form->removeAttribute('method');
        $this->assertTrue($this->form->isGet());
    }

    /**
     * @covers ::getAction
     */
    public function testGetAction()
    {
        $this->assertEquals('/test_functest/contact', $this->form->getAction());
    }

    /**
     * @covers ::isMultipart
     */
    public function testIsMultipart()
    {
        $this->assertFalse($this->form->isMultipart());

        $this->form->setAttribute('enctype', 'multipart/form-data');

        $this->assertTrue($this->form->isMultipart());
    }

    /**
     * @covers ::getData
     */
    public function testGetData()
    {
        $data = $this->form->getData(['test' => 'additional']);

        $expected = [
            'email' => 'tom@example.com',
            'name' => 'Tomas',
            'gender' => 'female',
            'newsletters' => 'test',
            'message' => 'Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod
 tempor incididunt ut labore et dolore magna aliqua.',
            'country' => 'uk',
            'test' => 'additional',
        ];

        $this->assertEquals($expected, $data);
    }

    /**
     * @covers ::getMultipartData
     */
    public function testGetMultipartData()
    {
        $file = $this->document->getElementById('file');
        $file->setAttribute('value', self::getFilesDir().'file.txt');

        $data = $this->form->getMultipartData(['test' => 'additional']);

        $expected = [
            [
                'name' => 'email',
                'contents' => 'tom@example.com',
            ],
            [
                'name' => 'name',
                'contents' => 'Tomas',
            ],
            [
                'name' => 'gender',
                'contents' => 'female',
            ],
            [
                'name' => 'newsletters',
                'contents' => 'test',
            ],
            [
                'name' => 'message',
                'contents' => 'Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod
 tempor incididunt ut labore et dolore magna aliqua.'
            ],
            [
                'name' => 'country',
                'contents' => 'uk',
            ],
            [
                'name' => 'test',
                'contents' => 'additional',
            ],
            [
                'name' => 'file',
                'contents' => $data[7]['contents'],
                'filename' => self::getFilesDir().'file.txt',
            ],
        ];

        $this->assertEquals($expected, $data);
    }

    /**
     * @covers ::getHeaders
     */
    public function testGetHeadersGet()
    {
        $this->form->setAttribute('method', 'get');

        $headers = $this->form->getHeaders();

        $this->assertEmpty($headers);
    }

    /**
     * @covers ::getHeaders
     */
    public function testGetHeadersMultipart()
    {
        $this->form->setAttribute('enctype', 'multipart/form-data');
        $this->form->setMultipartBoundary('56054f939e50e');

        $headers = $this->form->getHeaders();

        $this->assertArraySubset(
            ['Content-Type' => 'multipart/form-data; boundary=56054f939e50e'],
            $headers
        );
    }

    /**
     * @covers ::getHeaders
     */
    public function testGetHeadersPost()
    {
        $headers = $this->form->getHeaders();

        $this->assertArraySubset(
            ['Content-Type' => 'application/x-www-form-urlencoded'],
            $headers
        );
    }

    /**
     * @covers ::getRequest
     */
    public function testGetRequestPost()
    {
        $request = $this->form->getRequest(['submit_input' => 'Submit Item']);

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
     * @covers ::getRequest
     */
    public function testClickGet()
    {
        $this->form->removeAttribute('method');

        $request = $this->form->getRequest();

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
        ];

        parse_str((string) $request->getUri()->getQuery(), $query);

        $this->assertEquals($expected, $query);
        $this->assertEmpty((string) $request->getBody());
    }

    /**
     * @covers ::getRequest
     */
    public function testClickMultipart()
    {
        $this->form->setAttribute('enctype', 'multipart/form-data');
        $this->form->setMultipartBoundary('56054f939e50e');

        $file = $this->document->getElementById('file');
        $file->setAttribute('value', self::getFilesDir().'file.txt');

        $request = $this->form->getRequest();

        $expected = file_get_contents(self::getFilesDir().'multipart.txt');

        $this->assertEquals($expected, (string) $request->getBody());
    }
}
