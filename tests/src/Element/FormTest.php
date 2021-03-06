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

        $this->assertEquals(2, $result->length);
        $this->assertMatchesSelector('input#file', $result->item(0));
        $this->assertMatchesSelector('input#other-file', $result->item(1));
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
            'profile[name]' => 'Tomas',
            'gender' => 'female',
            'newsletters' => 'test',
            'message' => 'Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod
 tempor incididunt ut labore et dolore magna aliqua.',
            'country' => 'uk',
            'test' => 'additional',
        ];

        $this->assertEquals($expected, $data);
    }

    public function dataToNestedParams()
    {
        return [
            'Single' => [
                ['test' => 'value'],
                ['test' => 'value'],
            ],
            'Multiple' => [
                ['test' => 'value', 'other' => 'value2'],
                ['test' => 'value', 'other' => 'value2'],
            ],
            'Nested' => [
                ['test' => 'value', 'other[name]' => 'value2', 'other[type]' => 'value3'],
                ['test' => 'value', 'other' => ['name' => 'value2', 'type' => 'value3']],
            ],
        ];
    }

    /**
     * @covers ::toNestedParams
     * @dataProvider dataToNestedParams
     */
    public function testToNestedParams($params, $expected)
    {
        $this->assertEquals($expected, Form::toNestedParams($params));
    }

    /**
     * @covers ::getFiles
     */
    public function testGetFiles()
    {
        $data = $this->form->getFiles();

        $this->assertEquals('tests/files/file.txt', $data['file']['name']);
        $this->assertFileExists($data['file']['tmp_name']);
        $this->assertFileEquals('tests/files/file.txt', $data['file']['tmp_name']);
        $this->assertNotEquals('tests/files/file.txt', $data['file']['tmp_name']);
        $this->assertEquals(10, $data['file']['size']);
        $this->assertEquals(UPLOAD_ERR_OK, $data['file']['error']);
        $this->assertEquals('text/plain', $data['file']['type']);

        $this->assertEquals('tests/files/other.txt', $data['other']['file']['name']);
        $this->assertFileExists($data['other']['file']['tmp_name']);
        $this->assertFileEquals('tests/files/other.txt', $data['other']['file']['tmp_name']);
        $this->assertNotEquals('tests/files/other.txt', $data['other']['file']['tmp_name']);
        $this->assertEquals(16, $data['other']['file']['size']);
        $this->assertEquals(UPLOAD_ERR_OK, $data['other']['file']['error']);
        $this->assertEquals('text/plain', $data['other']['file']['type']);

        $this->assertEmpty($data['other']['empty']['name']);
        $this->assertEmpty($data['other']['empty']['tmp_name']);
        $this->assertEmpty($data['other']['empty']['type']);
        $this->assertEquals(0, $data['other']['empty']['size']);
        $this->assertEquals(UPLOAD_ERR_NO_FILE, $data['other']['empty']['error']);
    }

    /**
     * @covers ::getMultipartData
     */
    public function testGetMultipartData()
    {
        $data = $this->form->getMultipartData(['test' => 'additional']);

        $expected = [
            [
                'name' => 'email',
                'contents' => 'tom@example.com',
            ],
            [
                'name' => 'profile[name]',
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
                'filename' => 'tests/files/file.txt',
            ],
            [
                'name' => 'other[file]',
                'contents' => $data[8]['contents'],
                'filename' => 'tests/files/other.txt',
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

        $this->assertInstanceOf('GuzzleHttp\Psr7\ServerRequest', $request);
        $this->assertEquals('POST', $request->getMethod());
        $this->assertEquals('/test_functest/contact', (string) $request->getUri());

        parse_str((string) $request->getBody(), $body);

        $expected = [
            'email' => 'tom@example.com',
            'profile' => [
                'name' => 'Tomas'
            ],
            'gender' => 'female',
            'newsletters' => 'test',
            'message' => 'Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod
 tempor incididunt ut labore et dolore magna aliqua.',
            'country' => 'uk',
            'submit_input' => 'Submit Item',
        ];

        $this->assertEquals($expected, $body);

        $this->assertEquals($expected, $request->getParsedBody());
    }

    /**
     * @covers ::getRequest
     */
    public function testClickGet()
    {
        $this->form->removeAttribute('method');

        $request = $this->form->getRequest();

        $this->assertInstanceOf('GuzzleHttp\Psr7\ServerRequest', $request);
        $this->assertEquals('GET', $request->getMethod());
        $this->assertEquals('/test_functest/contact', $request->getUri()->getPath());

        $expected = [
            'email' => 'tom@example.com',
            'profile' => [
                'name' => 'Tomas',
            ],
            'gender' => 'female',
            'newsletters' => 'test',
            'message' => 'Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod
 tempor incididunt ut labore et dolore magna aliqua.',
            'country' => 'uk',
        ];

        parse_str((string) $request->getUri()->getQuery(), $query);

        $this->assertEquals($expected, $query);
        $this->assertEmpty((string) $request->getBody());
        $this->assertEquals($expected, $request->getParsedBody());
    }

    /**
     * @covers ::getRequest
     */
    public function testClickMultipart()
    {
        $this->form->setAttribute('enctype', 'multipart/form-data');
        $this->form->setMultipartBoundary('56054f939e50e');

        $request = $this->form->getRequest();

        $expected = file_get_contents(self::getFilesDir().'multipart.txt');

        $this->assertEquals($expected, (string) $request->getBody());

        $data = $request->getUploadedFiles();

        $this->assertInstanceOf('GuzzleHttp\Psr7\UploadedFile', $data['file']);
        $this->assertEquals(UPLOAD_ERR_OK, $data['file']->getError());
        $this->assertEquals('tests/files/file.txt', $data['file']->getClientFilename());

        $this->assertInstanceOf('GuzzleHttp\Psr7\UploadedFile', $data['other']['empty']);
        $this->assertEquals(UPLOAD_ERR_NO_FILE, $data['other']['empty']->getError());
        $this->assertEquals('', $data['other']['empty']->getClientFilename());

        $this->assertInstanceOf('GuzzleHttp\Psr7\UploadedFile', $data['other']['file']);
        $this->assertEquals(UPLOAD_ERR_OK, $data['other']['file']->getError());
        $this->assertEquals('tests/files/other.txt', $data['other']['file']->getClientFilename());
    }
}
