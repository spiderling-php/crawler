<?php

namespace SP\Crawler;

use SplObjectStorage;
use DOMXPath;
use DOMElement;
use InvalidArgumentException;

/**
 * @author    Ivan Kerin <ikerin@gmail.com>
 * @copyright 2015, Clippings Ltd.
 * @license   http://spdx.org/licenses/BSD-3-Clause
 */
class ElementMap
{
    /**
     * @var array
     */
    private $classMap;

    /**
     * @var Reader
     */
    private $reader;

    /**
     * @var SplObjectStorage
     */
    private $items;

    /**
     * @param Reader $reader
     * @param array  $classMap
     */
    public function __construct(Reader $reader, $classMap)
    {
        $this->classMap = $classMap;
        $this->reader = $reader;
        $this->items = new SplObjectStorage();
    }

    /**
     * @return Reader
     */
    public function getReader()
    {
        return $this->reader;
    }

    /**
     * @return array
     */
    public function getClassMap()
    {
        return $this->classMap;
    }

    /**
     * @param  DOMElement $element
     * @param  string     $xpath
     * @return boolean
     */
    public function elementMatches(DOMElement $element, $xpath)
    {
        return (bool) $this->reader->query($xpath, $element)->item(0);
    }

    /**
     * @param  DOMElement $element
     * @throws InvalidArgumentException
     * @return string
     */
    public function getElementClass(DOMElement $element)
    {
        foreach ($this->classMap as $class => $xpath) {
            if ($this->elementMatches($element, $xpath)) {
                return $class;
            }
        }

        throw new InvalidArgumentException(sprintf('%s is not an interactive element', $element->tagName));
    }

    /**
     * @param  DOMElement $element
     * @throws InvalidArgumentException
     * @return Element\AbstractElement
     */
    public function create(DOMElement $element)
    {
        $class = $this->getElementClass($element);

        return new $class($this->reader, $element);
    }

    /**
     * @param  DOMElement $element
     * @return Element\AbstractElement
     */
    public function get(DOMElement $element)
    {
        if (empty($this->items[$element])) {
            $this->items[$element] = $this->create($element);
        }

        return $this->items[$element];
    }
}
