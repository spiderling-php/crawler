<?php

namespace SP\Crawler\Element;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Uri;
use GuzzleHttp\Psr7\MultipartStream;

/**
 * @author    Ivan Kerin <ikerin@gmail.com>
 * @copyright 2015, Clippings Ltd.
 * @license   http://spdx.org/licenses/BSD-3-Clause
 */
class Submit extends AbstractClickable
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

    /**
     * @return Request
     */
    public function click()
    {
        $form = $this->getFormElement();

        $method = $form->getMethod();
        $uri = new Uri($form->getAction());
        $headers = [];
        $body = null;
        $data = [];

        if ($this->getAttribute('name')) {
            $data[$this->getAttribute('name')] = $this->getAttribute('value');
        }

        if ($form->isGet()) {
            foreach ($form->getData($data) as $key => $value) {
                $uri = Uri::withQueryValue($uri, $key, $value);
            }
        } elseif ($form->isMultipart()) {
            $body = new MultipartStream($form->getMultipartData($data), $form->getMultipartBoundary());
            $headers['Content-Type'] = 'multipart/form-data; boundary='.$form->getMultipartBoundary();
        } else {
            $body = http_build_query($form->getData($data), null, '&');
            $headers['Content-Type'] = 'application/x-www-form-urlencoded';
        }

        return new Request($method, $uri, $headers, $body);
    }
}
