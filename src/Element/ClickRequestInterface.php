<?php

namespace SP\Crawler\Element;

use Psr\Http\Message\RequestInterface;

/**
 * @author    Ivan Kerin <ikerin@gmail.com>
 * @copyright 2015, Clippings Ltd.
 * @license   http://spdx.org/licenses/BSD-3-Clause
 */
interface ClickRequestInterface
{
    /**
     * @return RequestInterface
     */
    public function clickRequest();
}
