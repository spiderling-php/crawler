<?php

namespace SP\Crawler\Element;

/**
 * @author    Ivan Kerin <ikerin@gmail.com>
 * @copyright 2015, Clippings Ltd.
 * @license   http://spdx.org/licenses/BSD-3-Clause
 */
class Option extends AbstractElement implements InputInterface, SelectableInterface
{
    /**
     * @return string
     */
    public function getValue()
    {
        return $this->getAttribute('value');
    }

    public function setValue($value)
    {
        $this->setAttribute('value', $value);
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
    public function select()
    {
        $this->unselectOthers();
        $this->setAttribute('selected', 'seleced');
    }
}
