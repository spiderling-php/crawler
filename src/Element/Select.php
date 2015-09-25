<?php

namespace SP\Crawler\Element;

/**
 * @author    Ivan Kerin <ikerin@gmail.com>
 * @copyright 2015, Clippings Ltd.
 * @license   http://spdx.org/licenses/BSD-3-Clause
 */
class Select extends AbstractInput
{
    const SELECTED = './/option[@selected]';
    const VALUE = './/option[@value = "%s" or (not(@value) and contains(normalize-space(), "%s"))]';

    public function unselectAll()
    {
        foreach ($this->getChildren(Select::SELECTED) as $option) {
            $option->removeAttribute('selected');
        }
    }
    /**
     * @param string $value
     */
    public function setValue($value)
    {
        $this->unselectAll();

        $xpath = sprintf(Select::VALUE, $value, $value);

        foreach ($this->getChildren($xpath) as $option)
        {
            $option->setAttribute('selected', 'selected');
        }
    }

    /**
     * @return string
     */
    public function getValue()
    {
        $values = [];

        foreach ($this->getChildren(Select::SELECTED) as $option)
        {
            $values[] = $option->hasAttribute('value')
                ? $option->getAttribute('value')
                : $option->textContent;
        }

        return reset($values);
    }
}
