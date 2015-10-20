<?php

namespace SP\Crawler\Element;

/**
 * @author    Ivan Kerin <ikerin@gmail.com>
 * @copyright 2015, Clippings Ltd.
 * @license   http://spdx.org/licenses/BSD-3-Clause
 */
class Submit extends AbstractElement implements ClickRequestInterface
{
    private $form;

    /**
     * @return DOMElement
     */
    public function getForm()
    {
        return $this->getChildren('./ancestor::form')->item(0);
    }

    /**
     * @return Form
     */
    public function getFormElement()
    {
        if (null === $this->form) {
            $this->form = new Form($this->getReader(), $this->getForm());
        }

        return $this->form;
    }

    public function getDefaultData()
    {
        $data = [];

        if ($this->getAttribute('name')) {
            $data[$this->getAttribute('name')] = $this->getAttribute('value');
        }

        return $data;
    }

    /**
     * @return Request
     */
    public function clickRequest()
    {
        $data = [];

        if ($this->getAttribute('name')) {
            $data[$this->getAttribute('name')] = $this->getAttribute('value');
        }

        return $this->getFormElement()->getRequest($data);
    }
}
