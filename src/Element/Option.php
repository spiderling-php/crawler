<?php

namespace SP\Crawler\Element;

/**
 * @author    Ivan Kerin <ikerin@gmail.com>
 * @copyright 2015, Clippings Ltd.
 * @license   http://spdx.org/licenses/BSD-3-Clause
 */
class Option extends AbstractInput
{
    /**
     * @return string
     */
    public function getValue()
    {
        return $this->getAttribute('value');
    }

    /**
     * @return DOMElement
     */
    public function getSelect()
    {
        return $this->getChildren('./ancestor::select')->item(0);
    }

    public function unselectOthers()
    {
        $select = $this->getReader()->getInput($this->getSelect());
        $select->unselectAll();
    }

    /**
     * @param boolean $value
     */
    public function setValue($value)
    {
        if ($value) {
            $this->unselectOthers();

            $this->setAttribute('selected', 'seleced');
        } else {
            $this->removeAttribute('selected');
        }
    }
}
