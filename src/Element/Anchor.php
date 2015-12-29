<?php

namespace SP\Crawler\Element;

use GuzzleHttp\Psr7\ServerRequest;

/**
 * @author    Ivan Kerin <ikerin@gmail.com>
 * @copyright 2015, Clippings Ltd.
 * @license   http://spdx.org/licenses/BSD-3-Clause
 */
class Anchor extends AbstractElement implements ClickRequestInterface
{
    /**
     * @return ServerRequest
     */
    public function clickRequest()
    {
        return new ServerRequest('GET', $this->getAttribute('href'));
    }
}
