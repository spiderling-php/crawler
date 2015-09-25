<?php

namespace SP\Crawler;

use SP\Spiderling\CrawlerInterface;
use SP\Spiderling\Query\AbstractQuery;
use DOMDocument;
use DOMElement;
use DOMXPath;
use InvalidArgumentException;
use BadMethodCallException;

/**
 * @author    Ivan Kerin <ikerin@gmail.com>
 * @copyright 2015, Clippings Ltd.
 * @license   http://spdx.org/licenses/BSD-3-Clause
 */
class Reader implements CrawlerInterface
{
    /**
     * @var array
     */
    private static $inputMatchers = [
        'SP\Crawler\Element\Checkbox'    => 'self::input[@type="checkbox"]',
        'SP\Crawler\Element\Radio'       => 'self::input[@type="radio"]',
        'SP\Crawler\Element\File'        => 'self::input[@type="file"]',
        'SP\Crawler\Element\Input'       => 'self::input',
        'SP\Crawler\Element\Select'      => 'self::select',
        'SP\Crawler\Element\Textarea'    => 'self::textarea',
        'SP\Crawler\Element\Option'      => 'self::option',
    ];

    /**
     * @return array
     */
    public static function getInputMatchers()
    {
        return self::$inputMatchers;
    }

    /**
     * @var DOMDocument
     */
    private $document;

    /**
     * @var DOMXPath
     */
    private $xpath;

    /**
     * @var ElementMap
     */
    private $inputMap;

    /**
     * @param DOMDocument $document
     */
    public function __construct(DOMDocument $document)
    {
        $this->document = $document;

        $this->xpath = new DOMXPath($document);

        $this->inputMap = new ElementMap($this, self::$inputMatchers);
    }

    /**
     * @return DOMDocument
     */
    public function getDocument()
    {
        return $this->document;
    }

    /**
     * @return DOMXPath
     */
    public function getXPath()
    {
        return $this->xpath;
    }

    /**
     * @return ElementMap
     */
    public function getInputMap()
    {
        return $this->inputMap;
    }

    /**
     * @param  string $id
     * @throws BadMethodCallException
     */
    public function click($id)
    {
        throw new BadMethodCallException(
            sprintf('Method %s not supported by %s', __METHOD__, __CLASS__)
        );
    }

    /**
     * @param  string $url
     * @throws BadMethodCallException
     */
    public function open($url)
    {
        throw new BadMethodCallException(
            sprintf('Method %s not supported by %s', __METHOD__, __CLASS__)
        );
    }

    /**
     * @throws BadMethodCallException
     */
    public function getPath()
    {
        return null;
    }

    /**
     * @throws BadMethodCallException
     */
    public function getUri()
    {
        return null;
    }

    /**
     * @throws BadMethodCallException
     */
    public function getUserAgent()
    {
        return null;
    }

    /**
     * @param  string          $xpath
     * @param  DOMElement|null $scope
     * @return DOMNodeList
     */
    public function query($xpath, DOMElement $scope = null)
    {
        return $this->getXpath()->query($xpath, $scope);
    }

    /**
     * @param  string $xpath
     * @throws InvalidArgumentException when id not found
     * @return DOMElement
     */
    public function getElement($xpath)
    {
        $items = $this->query($xpath);

        if (0 === $items->length) {
            throw new InvalidArgumentException(
                sprintf('Node with id %s does not exist', $xpath)
            );
        }

        return $items->item(0);
    }

    /**
     * @param  string $id
     * @throws InvalidArgumentException when id not found
     * @return string
     */
    public function getText($id)
    {
        $element = $this->getElement($id);

        return trim(preg_replace('/[ \s\f\n\r\t\v ]+/u', ' ', $element->textContent));
    }

    /**
     * @param  string $id
     * @throws InvalidArgumentException when id not found
     * @return string
     */
    public function getTagName($id)
    {
        return $this->getElement($id)->tagName;
    }

    /**
     * @param  string $id
     * @param  string $name
     * @throws InvalidArgumentException when id not found
     * @return string
     */
    public function getAttribute($id, $name)
    {
        return $this->getElement($id)->getAttribute($name);
    }

    /**
     * @param  string $id
     * @throws InvalidArgumentException when id not found
     * @return string
     */
    public function getHtml($id)
    {
        return $this->document->saveXml($this->getElement($id));
    }

    /**
     * @return string
     */
    public function getFullHtml()
    {
        return $this->document->saveHtml();
    }

    /**
     * @param  string $id
     * @throws InvalidArgumentException when id not found
     * @return boolean
     */
    public function isVisible($id)
    {
        $element = $this->getElement($id);

        $conditions = [
            "contains(@style, 'display:none')",
            "contains(@style, 'display: none')",
            "self::script",
            "self::head",
        ];

        $hidden = $this->xpath->query(
            './ancestor-or-self::*['.join(' or ', $conditions).']',
            $element
        );

        return $hidden->length == 0;
    }

    /**
     * @param  string $id
     * @throws InvalidArgumentException when id not found
     * @return boolean
     */
    public function isSelected($id)
    {
        return $this->getElement($id)->hasAttribute('selected');
    }

    /**
     * @param  string $id
     * @throws InvalidArgumentException when id not found
     * @return boolean
     */
    public function isChecked($id)
    {
        return $this->getElement($id)->hasAttribute('checked');
    }

    /**
     * @param  string $id
     * @throws InvalidArgumentException when id not found
     * @return Element/AbstractInput
     */
    public function getInput(DOMElement $element)
    {
        return $this->inputMap->get($element);
    }

    /**
     * @param  string $id
     * @throws InvalidArgumentException when id not found
     * @return mixed
     */
    public function getValue($id)
    {
        return $this->getInput($this->getElement($id))->getValue();
    }

    /**
     * @param  string $id
     * @param  string $value
     * @throws InvalidArgumentException when id not found
     */
    public function setValue($id, $value)
    {
        $input = $this->getInput($this->getElement($id));

        if (false === $input->isDisabled()) {
            $input->setValue($value);
        }
    }

    /**
     * @param  AbstractQuery $query
     * @param  string        $parent
     * @return array
     */
    public function queryIds(AbstractQuery $query, $parent = null)
    {
        $xpath = $parent.$query->getXPath();

        $ids = [];

        foreach ($this->query($xpath) as $index => $element) {
            $ids []= "($xpath)[".($index+1)."]";
        }

        return array_values(array_filter($ids, function ($id) use ($query) {
            return $query->getFilters()->match($this, $id);
        }));
    }
}
