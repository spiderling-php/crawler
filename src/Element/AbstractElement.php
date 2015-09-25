<?php

namespace SP\Crawler\Element;

use SP\Crawler\Reader;
use DOMElement;

/**
 * @author    Ivan Kerin <ikerin@gmail.com>
 * @copyright 2015, Clippings Ltd.
 * @license   http://spdx.org/licenses/BSD-3-Clause
 */
abstract class AbstractElement
{
    /**
     * @var DOMElement
     */
    private $element;

    /**
     * @var Reader
     */
    private $reader;

    /**
     * @param Reader     $reader
     * @param DOMElement $element
     */
    public function __construct(Reader $reader, DOMElement $element)
    {
        $this->element = $element;
        $this->reader = $reader;
    }

    /**
     * @return DOMElement
     */
    public function getElement()
    {
        return $this->element;
    }

    /**
     * @param  string  $name
     * @return boolean
     */
    public function hasAttribute($name)
    {
        return $this->element->hasAttribute($name);
    }

    /**
     * @param  string $name
     * @return string
     */
    public function getAttribute($name)
    {
        return $this->element->getAttribute($name);
    }

    /**
     * @param string $name
     * @param mixed $value
     */
    public function setAttribute($name, $value)
    {
        $this->element->setAttribute($name, $value);
    }

    /**
     * @param string $name
     */
    public function removeAttribute($name)
    {
        $this->element->removeAttribute($name);
    }

    /**
     * @return boolean
     */
    public function isDisabled()
    {
        return $this->element->hasAttribute('disabled');
    }

    /**
     * @return Reader
     */
    public function getReader()
    {
        return $this->reader;
    }

    /**
     * @param  string $xpath
     * @return DOMNodeList
     */
    public function getChildren($xpath)
    {
        return $this->reader->query($xpath, $this->element);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->getAttribute('name');
    }
}
