<?php

namespace SP\Crawler\Element;

/**
 * @author    Ivan Kerin <ikerin@gmail.com>
 * @copyright 2015, Clippings Ltd.
 * @license   http://spdx.org/licenses/BSD-3-Clause
 */
class Checkbox extends AbstractElement implements ClickableInterface, InputInterface
{
    /**
     * @param boolean $value
     */
    public function setValue($value)
    {
        $this->setAttribute('value', $value);
    }

    public function click()
    {
        if ($this->hasAttribute('checked')) {
            $this->removeAttribute('checked');
        } else {
            $this->setAttribute('checked', 'checked');
        }
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->getAttribute('value');
    }
}
