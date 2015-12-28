<?php

namespace SP\Crawler\Test\Element;

use SP\Crawler\Element\File;
use SP\Crawler\Test\AbstractTestCase;

/**
 * @coversDefaultClass SP\Crawler\Element\File
 */
class FileTest extends AbstractTestCase
{
    /**
     * @covers ::getPhpFileArray
     */
    public function testGetPhpFileArrayOk()
    {
        $domElement = $this->document->getElementById('file');

        $file = new File($this->crawler, $domElement);
        $array = $file->getPhpFileArray();

        $this->assertEquals('tests/files/file.txt', $array['name']);
        $this->assertFileExists($array['tmp_name']);
        $this->assertFileEquals('tests/files/file.txt', $array['tmp_name']);
        $this->assertNotEquals('tests/files/file.txt', $array['tmp_name']);
        $this->assertEquals(10, $array['size']);
        $this->assertEquals(UPLOAD_ERR_OK, $array['error']);
        $this->assertEquals('text/plain', $array['type']);

        unlink($array['tmp_name']);
    }

    /**
     * @covers ::getPhpFileArray
     */
    public function testGetPhpFileArrayEmpty()
    {
        $domElement = $this->document->getElementById('empty-file');

        $file = new File($this->crawler, $domElement);
        $array = $file->getPhpFileArray();

        $this->assertEmpty($array['name']);
        $this->assertEmpty($array['tmp_name']);
        $this->assertEmpty($array['type']);
        $this->assertEquals(0, $array['size']);
        $this->assertEquals(UPLOAD_ERR_NO_FILE, $array['error']);
    }
}
