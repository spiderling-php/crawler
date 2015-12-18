<?php

namespace SP\Crawler;

use DOMNode;
use DOMXPath;
use InvalidArgumentException;

/**
 * @author    Ivan Kerin <ikerin@gmail.com>
 * @copyright 2015, Clippings Ltd.
 * @license   http://spdx.org/licenses/BSD-3-Clause
 */
class SafeXPath extends DOMXPath
{
    private $errors;

    public function onQueryError($errno, $errstrs)
    {
        $this->errors []= $errstrs;
    }

    /**
     * @param  string          $xpath
     * @param  DOMNode|null $scope
     * @throws InvalidArgumentException If xpath is not valid
     * @return DOMNodeList|false
     */
    public function query($xpath, DOMNode $scope = null, $registerNodeNS = null)
    {
        set_error_handler([$this, 'onQueryError']);

        $result = parent::query($xpath, $scope, $registerNodeNS);

        restore_error_handler();

        if ($this->errors) {
            throw new InvalidArgumentException(sprintf('XPath error for %s (%s)', $xpath, current($this->errors)));
        }

        return $result;
    }
}
