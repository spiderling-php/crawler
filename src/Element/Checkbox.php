<?php

namespace SP\Crawler\Element;

/**
 * @author    Ivan Kerin <ikerin@gmail.com>
 * @copyright 2015, Clippings Ltd.
 * @license   http://spdx.org/licenses/BSD-3-Clause
 */
class Checkbox extends AbstractInput
{
    /**
     * @param boolean $value
     */
    public function setValue($value)
    {
        if ($value) {
            $this->setAttribute('checked', 'checked');
        } else {
            $this->removeAttribute('checked');
        }
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        if ($this->hasAttribute('checked')) {
            return $this->getAttribute('value');
        } else {
            return null;
        }
    }
}
