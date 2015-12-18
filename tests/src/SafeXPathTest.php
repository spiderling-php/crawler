<?php

namespace SP\Crawler\Test;

use SP\Crawler\SafeXPath;
use DOMElement;

/**
 * @coversDefaultClass SP\Crawler\SafeXPath
 */
class SafeXPathTest extends AbstractTestCase
{

    /**
     * @covers ::query
     */
    public function testQuery()
    {
        $index = $this->document->getElementById('index');
        $xpath = new SafeXPath($this->document);

        $items = $xpath->query('//form', $index);

        $this->assertEquals(1, $items->length);
        $this->assertMatchesSelector('form#form', $items->item(0));
    }

    /**
     * @covers ::query
     * @covers ::onQueryError
     */
    public function testQueryMalformedXpath()
    {
        $xpath = new SafeXPath($this->document);

        $this->setExpectedException('InvalidArgumentException', 'XPath error for //div[@test (DOMXPath::query(): Invalid predicate)');

        $xpath->query('//div[@test');
    }

    /**
     * @covers ::query
     * @covers ::onQueryError
     */
    public function testQueryInvalidScope()
    {
        $xpath = new SafeXPath($this->document);

        $this->setExpectedException('InvalidArgumentException', 'XPath error for //form (DOMXPath::query(): Node From Wrong Document)');

        $xpath->query('//form', new DOMElement('div'));
    }
}
