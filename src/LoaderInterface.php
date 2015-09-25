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
    public function send(RequestInterface $request);
    public function getCurrentUri();
    public function getUserAgent();
}
