<?php

namespace SP\Crawler\Test;

use DOMDocument;
use SP\Crawler\Crawler;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Uri;

/**
 * @coversDefaultClass SP\Crawler\Crawler
 */
class CrawlerTest extends AbstractTestCase
{
    /**
     * @covers ::getClickableMatchers
     */
    public function testGetInputMatchers()
    {
        $result = Crawler::getClickableMatchers();

        $this->assertArrayHasKey('SP\Crawler\Element\Anchor', $result);
        $this->assertArrayHasKey('SP\Crawler\Element\Submit', $result);
    }

    /**
     * @covers ::__construct
     * @covers ::getClickableMap
     */
    public function testConstruct()
    {
        $crawler = new Crawler($this->loader, $this->document);

        $this->assertSame($this->document, $crawler->getDocument());

        $this->assertInstanceOf('SP\Crawler\ElementMap', $crawler->getInputMap());
        $this->assertSame($crawler, $crawler->getClickableMap()->getReader());
    }

    /**
     * @covers ::sendRequest
     * @covers ::getFullHtml
     */
    public function testSendRequest()
    {
        $responseBody = <<<HTML
<!DOCTYPE html>
<html><body>Success!</body></html>

HTML;
        $request = new Request('GET', 'http://example.com');
        $response = new Response(200, [], $responseBody);

        $this->loader
            ->expects($this->once())
            ->method('send')
            ->with($request)
            ->willReturn($response);

        $this->crawler->sendRequest($request);

        $this->assertEquals($responseBody, $this->crawler->getFullHtml());
    }

    public function dataGetClickable()
    {
        return [
            ['navlink-1'  , 'SP\Crawler\Element\Anchor'],
            ['submit-btn' , 'SP\Crawler\Element\Submit'],
            ['submit'     , 'SP\Crawler\Element\Submit'],
        ];
    }

    /**
     * @dataProvider dataGetClickable
     * @covers ::getClickable
     */
    public function testGetClickable($id, $class)
    {
        $element = $this->document->getElementById($id);
        $result = $this->crawler->getClickable($element);
        $this->assertInstanceOf($class, $result);
        $this->assertSame($this->crawler, $result->getReader());
    }

    /**
     * @covers ::click
     */
    public function testClick()
    {
        $element = $this->document->getElementById('navlink-1');
        $request = new Request('GET', 'http://example.com');

        $crawler = $this
            ->getMockBuilder('SP\Crawler\Crawler')
            ->setConstructorArgs([$this->loader, $this->document])
            ->setMethods(['getClickable', 'sendRequest'])
            ->getMock();

        $clickable = $this->getMockForAbstractClass('SP\Crawler\Element\AbstractClickable', [$crawler, $element]);

        $crawler
            ->expects($this->once())
            ->method('getClickable')
            ->with($element)
            ->willReturn($clickable);

        $clickable
            ->expects($this->once())
            ->method('click')
            ->willReturn($request);

        $crawler
            ->expects($this->once())
            ->method('sendRequest')
            ->with($request);

        $crawler->click('//a[@id="navlink-1"]');
    }

    /**
     * @covers ::open
     */
    public function testOpen()
    {
        $uri = new Uri('http://example.com/api/users');

        $crawler = $this
            ->getMockBuilder('SP\Crawler\Crawler')
            ->setConstructorArgs([$this->loader, $this->document])
            ->setMethods(['sendRequest'])
            ->getMock();

        $crawler
            ->expects($this->once())
            ->method('sendRequest')
            ->with(
                $this->logicalAnd(
                    $this->isInstanceOf('Psr\Http\Message\RequestInterface'),
                    $this->attribute(
                        $this->equalTo($uri),
                        'uri'
                    )
                )
            );

        $result = $crawler->open($uri);
    }

    /**
     * @covers ::getUri
     */
    public function testGetUri()
    {
        $uriString = 'http://example.com/api/users';

        $request = new Uri($uriString);

        $this->loader
            ->expects($this->once())
            ->method('getCurrentUri')
            ->willReturn($request);

        $result = $this->crawler->getUri();

        $this->assertEquals($uriString, $result);
    }
}
