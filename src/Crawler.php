<?php

namespace SP\Crawler;

use Psr\Http\Message\RequestInterface;
use SP\Crawler\Element\AbstractElement;
use Psr\Http\Message\UriInterface;
use GuzzleHttp\Psr7\Request;
use DOMDocument;
use DOMElement;

/**
 * @author    Ivan Kerin <ikerin@gmail.com>
 * @copyright 2015, Clippings Ltd.
 * @license   http://spdx.org/licenses/BSD-3-Clause
 */
class Crawler extends Reader
{
    /**
     * @var LoaderInterface
     */
    private $loader;

    /**
     * @param LoaderInterface $loader
     * @param DOMDocument     $document
     */
    public function __construct(LoaderInterface $loader, DOMDocument $document)
    {
        $this->loader = $loader;

        parent::__construct($document);
    }

    /**
     * @return LoaderInterface
     */
    public function getLoader()
    {
        return $this->loader;
    }

    /**
     * @param  RequestInterface $request
     */
    public function sendRequest(RequestInterface $request)
    {
        $response = $this->loader->send($request);
        $contents = $response->getBody()->getContents();

        $this->getDocument()->loadHtml($contents);
    }

    /**
     * @param  string $uri
     */
    public function open(UriInterface $uri)
    {
        $request = new Request('GET', $uri);

        $this->sendRequest($request);
    }

    /**
     * @return Psr\Http\Message\UriInterface
     */
    public function getUri()
    {
        return $this->loader->getCurrentUri();
    }
}
