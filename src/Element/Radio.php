<?php

namespace SP\Crawler\Element;

/**
 * @author    Ivan Kerin <ikerin@gmail.com>
 * @copyright 2015, Clippings Ltd.
 * @license   http://spdx.org/licenses/BSD-3-Clause
 */
class Radio extends Checkbox
{
    const CHECKED = "//input[@type='radio' and @name='%s' and @checked]";

    public function uncheckOthers()
    {
        $xpath = sprintf(Radio::CHECKED, $this->getName());

        foreach ($this->getReader()->query($xpath) as $radio) {
            $radio->removeAttribute('checked');
        }
    }

    /**
     * @param boolean $value
     */
    public function setValue($value)
    {
        if ($value) {
            $this->uncheckOthers();
        }

        parent::setValue($value);
    }
}
