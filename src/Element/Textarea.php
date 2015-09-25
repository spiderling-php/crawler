<?php

namespace SP\Crawler\Element;

/**
 * @author    Ivan Kerin <ikerin@gmail.com>
 * @copyright 2015, Clippings Ltd.
 * @license   http://spdx.org/licenses/BSD-3-Clause
 */
class Textarea extends AbstractInput
{
    /**
     * @param string $value
     */
    public function setValue($value)
    {
        $this->getElement()->nodeValue = $value;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->getElement()->textContent;
    }
}
