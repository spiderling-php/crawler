<?php

namespace SP\Crawler\Element;

use GuzzleHttp\Psr7\Request;

/**
 * @author    Ivan Kerin <ikerin@gmail.com>
 * @copyright 2015, Clippings Ltd.
 * @license   http://spdx.org/licenses/BSD-3-Clause
 */
class Anchor extends AbstractClickable
{
    /**
     * @return Request
     */
    public function click()
    {
        return new Request('GET', $this->getAttribute('href'));
    }
}
