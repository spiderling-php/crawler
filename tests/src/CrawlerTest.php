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
     * @covers ::__construct
     * @covers ::getLoader
     */
    public function testConstruct()
    {
        $crawler = new Crawler($this->loader, $this->document);

        $this->assertSame($this->document, $crawler->getDocument());
        $this->assertSame($this->loader, $crawler->getLoader());
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
