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
class InputMap
{
    private static $inputClasses = [
        'SP\Crawler\Element\Checkbox'    => 'self::input[@type="checkbox"]',
        'SP\Crawler\Element\Radio'       => 'self::input[@type="radio"]',
        'SP\Crawler\Element\File'        => 'self::input[@type="file"]',
        'SP\Crawler\Element\Input'       => 'self::input',
        'SP\Crawler\Element\Select'      => 'self::select',
        'SP\Crawler\Element\Textarea'    => 'self::textarea',
        'SP\Crawler\Element\Option'      => 'self::option',
        'SP\Crawler\Element\Anchor'      => 'self::a',
        'SP\Crawler\Element\Submit'      => 'self::*[@type="submit" and (self::input or self::button)]',
    ];

    /**
     * @return array
     */
    public static function getInputClasses()
    {
        return self::$inputClasses;
    }

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
     */
    public function __construct(Reader $reader)
    {
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
     * @param  DOMElement $element
     * @param  string     $xpath
     * @return boolean
     */
    public function inputMatches(DOMElement $element, $xpath)
    {
        return (bool) $this->reader->query($xpath, $element)->item(0);
    }

    /**
     * @param  DOMElement $element
     * @throws InvalidArgumentException
     * @return string
     */
    public function getInputClass(DOMElement $element)
    {
        foreach (self::$inputClasses as $class => $xpath) {
            if ($this->inputMatches($element, $xpath)) {
                return $class;
            }
        }

        throw new InvalidArgumentException(sprintf('%s is not an input', $element->tagName));
    }

    /**
     * @param  DOMElement $element
     * @throws InvalidArgumentException
     * @return Element\AbstractElement
     */
    public function create(DOMElement $element)
    {
        $class = $this->getInputClass($element);

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
