<?php

namespace SP\Crawler;

use Psr\Http\Message\RequestInterface;
use GuzzleHttp\Psr7\Request;
use DOMDocument;

/**
 * @author    Ivan Kerin <ikerin@gmail.com>
 * @copyright 2015, Clippings Ltd.
 * @license   http://spdx.org/licenses/BSD-3-Clause
 */
class Crawler extends Reader
{
    /**
     * @var array
     */
    private static $clickableMatchers = [
        'SP\Crawler\Element\Anchor'      => 'self::a',
        'SP\Crawler\Element\Submit'      => 'self::*[@type="submit" and (self::input or self::button)]',
    ];

    /**
     * @return array
     */
    public static function getClickableMatchers()
    {
        return self::$clickableMatchers;
    }

    /**
     * ElementMap
     */
    private $clickableMap;

    /**
     * @param LoaderInterface $loader
     * @param DOMDocument     $document
     */
    public function __construct(LoaderInterface $loader, DOMDocument $document)
    {
        $this->loader = $loader;

        $this->clickableMap = new ElementMap($this, Crawler::$clickableMatchers);

        parent::__construct($document);
    }

    /**
     * @return ElementMap
     */
    public function getClickableMap()
    {
        return $this->clickableMap;
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
    public function open($uri)
    {
        $request = new Request('GET', $uri);

        $this->sendRequest($request);
    }

    /**
     * @param  DOMElement $element
     * @return Element\AbstractClickable
     */
    public function getClickable($element)
    {
        return $this->clickableMap->get($element);
    }

    /**
     * @param  string $id
     */
    public function click($id)
    {
        $request = $this->getClickable($this->getElement($id))->click();

        $this->sendRequest($request);
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->loader->getCurrentUri()->getPath();
    }

    /**
     * @return string
     */
    public function getUri()
    {
        return (string) $this->loader->getCurrentUri();
    }

    /**
     * @return string
     */
    public function getUserAgent()
    {
        return $this->loader->getUserAgent();
    }
}
