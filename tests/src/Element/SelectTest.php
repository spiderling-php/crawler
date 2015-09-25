<?php

namespace SP\Crawler\Test\Element;

use SP\Crawler\Element\Select;
use SP\Crawler\Test\AbstractTestCase;

/**
 * @coversDefaultClass SP\Crawler\Element\Select
 */
class SelectTest extends AbstractTestCase
{
    /**
     * @covers ::getValue
     */
    public function testGetValue()
    {
        $domElement = $this->document->getElementById('country');

        $input = new Select($this->crawler, $domElement);

        $expected = 'uk';

        $this->assertSame($expected, $input->getValue());
    }

    /**
     * @covers ::setValue
     * @covers ::unselectAll
     */
    public function testSetValue()
    {
        $domElement = $this->document->getElementById('country');

        $input = new Select($this->crawler, $domElement);

        $input->setValue('bulgaria');

        $this->assertSame('bulgaria', $input->getValue());
    }
}
