<?php

namespace SP\Crawler;

use SP\Spiderling\CrawlerInterface;
use SP\Crawler\Element\ClickRequestInterface;
use SP\Crawler\Element\ClickableInterface;
use SP\Crawler\Element\SelectableInterface;
use SP\Crawler\Element\File;
use Psr\Http\Message\ServerRequestInterface;
use SP\Spiderling\Query\AbstractQuery;
use GuzzleHttp\Psr7\Uri;
use Psr\Http\Message\UriInterface;
use DOMDocument;
use DOMElement;
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
     * @var DOMDocument
     */
    private $document;

    /**
     * @var SafeXPath
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

        $this->xpath = new SafeXPath($document);

        $this->inputMap = new InputMap($this);
    }

    /**
     * @param  string $content
     * @return self
     */
    public function setDocumentContent($content)
    {
        $this->document->loadHtml((string) $content);
        $this->xpath = new SafeXPath($this->document);

        return $this;
    }

    /**
     * @return DOMDocument
     */
    public function getDocument()
    {
        return $this->document;
    }

    /**
     * @return SafeXPath
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
        $input = $this->getInput($this->getElement($id));

        if ($input instanceof ClickableInterface) {
            $input->click();
        } elseif ($input instanceof ClickRequestInterface) {
            $request = $input->clickRequest();
            $this->sendRequest($request);
        } else {
            throw new BadMethodCallException(
                sprintf('Cannot click on %s, %s', get_class($input), $id)
            );
        }
    }

    /**
     * @param  string $id
     * @throws BadMethodCallException
     */
    public function select($id)
    {
        $input = $this->getInput($this->getElement($id));

        if ($input instanceof SelectableInterface) {
            $input->select();
        } else {
            throw new BadMethodCallException(
                sprintf('Cannot select on %s, %s', get_class($input), $id)
            );
        }
    }

    /**
     * @param  ServerRequestInterface $input
     * @throws BadMethodCallException
     */
    public function sendRequest(ServerRequestInterface $request)
    {
        throw new BadMethodCallException(
            sprintf('Cannot send request to %s', $request->getUri())
        );
    }

    /**
     * @param  string $url
     * @throws BadMethodCallException
     */
    public function open(UriInterface $url)
    {
        throw new BadMethodCallException(
            sprintf('Method %s not supported by %s', __METHOD__, __CLASS__)
        );
    }

    /**
     * @return Psr\Http\Message\UriInterface
     */
    public function getUri()
    {
        return new Uri('');
    }

    /**
     * @param  string          $xpath
     * @param  DOMElement|null $scope
     * @throws InvalidArgumentException If xpath is not valid
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
     * @param  DOMElement $element
     * @throws InvalidArgumentException when id not found
     * @return Element\AbstractElement
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
     * @param  string $id
     * @param  string $file
     * @throws InvalidArgumentException when id not found or not a file
     */
    public function setFile($id, $file)
    {
        $input = $this->getInput($this->getElement($id));

        if (false === ($input instanceof File)) {
            throw new InvalidArgumentException(
                sprintf('Node with id %s is not a file', $id)
            );
        }

        if (false === $input->isDisabled()) {
            $input->setValue($file);
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

        return $query->getFilters()->matchAll($this, $ids);
    }
}
