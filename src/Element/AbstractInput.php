<?php

namespace SP\Crawler\Element;

/**
 * @author    Ivan Kerin <ikerin@gmail.com>
 * @copyright 2015, Clippings Ltd.
 * @license   http://spdx.org/licenses/BSD-3-Clause
 */
abstract class AbstractInput extends AbstractElement
{
    /**
     * @return mixed
     */
    abstract public function getValue();

    /**
     * @param mixed $value
     */
    abstract public function setValue($value);

}
