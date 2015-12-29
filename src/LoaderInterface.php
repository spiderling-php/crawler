<?php

namespace SP\Crawler;

use Psr\Http\Message\ServerRequestInterface;

/**
 * @author    Ivan Kerin <ikerin@gmail.com>
 * @copyright 2015, Clippings Ltd.
 * @license   http://spdx.org/licenses/BSD-3-Clause
 */
interface LoaderInterface
{
    /**
     * @param  ServerRequestInterface $request
     */
    public function send(ServerRequestInterface $request);

    /**
     * @return \Psr\Http\Message\UriInterface
     */
    public function getCurrentUri();
}
