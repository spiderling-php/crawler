<?php

namespace SP\Crawler;

use Psr\Http\Message\RequestInterface;

/**
 * @author    Ivan Kerin <ikerin@gmail.com>
 * @copyright 2015, Clippings Ltd.
 * @license   http://spdx.org/licenses/BSD-3-Clause
 */
interface LoaderInterface
{
    /**
     * @param  RequestInterface $request
     */
    public function send(RequestInterface $request);

    /**
     * @return Psr\Http\Message\UriInterface
     */
    public function getCurrentUri();

    /**
     * @return string
     */
    public function getUserAgent();
}
