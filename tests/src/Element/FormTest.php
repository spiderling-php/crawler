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
                'contents' => "test file\n",
                'filename' => self::getFilesDir().'file.txt',
            ],
        ];

        $this->assertEquals($expected, $data);
    }
}
