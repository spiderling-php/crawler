<?php

namespace SP\Crawler\Test\Element;

use SP\Crawler\Element\Anchor;
use SP\Crawler\Test\AbstractTestCase;

/**
 * @coversDefaultClass SP\Crawler\Element\Anchor
 */
class AnchorTest extends AbstractTestCase
{
    /**
     * @covers ::clickRequest
     */
    public function testClickRequest()
    {
        $domElement = $this->document->getElementById('navlink-1');

        $anchor = new Anchor($this->crawler, $domElement);

        $result = $anchor->clickRequest();

        $this->assertInstanceOf('Psr\Http\Message\RequestInterface', $result);
        $this->assertEquals('/test_functest/subpage1', (string) $result->getUri());
    }
}
