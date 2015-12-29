<?php

namespace SP\Crawler\Test;

use DOMDocument;
use SP\Crawler\Crawler;
use GuzzleHttp\Psr7\ServerRequest;
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
     * @covers ::setDocumentContent
     */
    public function testSendRequest()
    {
        $emptyDocument = new DOMDocument();
        $crawler = new Crawler($this->loader, $emptyDocument);

        $responseBody = <<<HTML
<!DOCTYPE html>
<html><body>Success!</body></html>

HTML;
        $request = new ServerRequest('GET', 'http://example.com');
        $response = new Response(200, [], $responseBody);

        $this->loader
            ->expects($this->once())
            ->method('send')
            ->with($request)
            ->willReturn($response);

        $crawler->sendRequest($request);

        $this->assertEquals($responseBody, $crawler->getFullHtml());

        $result = $crawler->getXPath()->query('//body');

        $this->assertEquals(
            1,
            $result->length,
            'Should be able to use the new html'
        );
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
                    $this->isInstanceOf('Psr\Http\Message\ServerRequestInterface'),
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

        $uri = new Uri($uriString);

        $this->loader
            ->expects($this->once())
            ->method('getCurrentUri')
            ->willReturn($uri);

        $result = $this->crawler->getUri();

        $this->assertEquals($uriString, $result);
    }
}
