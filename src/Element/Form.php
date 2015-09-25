<?php

namespace SP\Crawler\Element;

/**
 * @author    Ivan Kerin <ikerin@gmail.com>
 * @copyright 2015, Clippings Ltd.
 * @license   http://spdx.org/licenses/BSD-3-Clause
 */
class Form extends AbstractElement
{
    /**
     * @var array
     */
    private static $fieldsXPath = <<<FIELDS
//*[not(@disabled) and (
    (self::input and @type = 'radio' and @checked)
    or (self::input and @type = 'checkbox' and @checked)
    or (self::input and @type != 'radio' and @type != 'file' and @type != 'checkbox' and @type != 'submit')
    or (self::input and not(@type))
    or self::select
    or self::textarea
)]
FIELDS;

    private static $filesXPath = "//input[not(@disabled) and @type = 'file' and @value]";

    /**
     * @return string
     */
    public static function getFieldsXPath()
    {
        return self::$fieldsXPath;
    }

    /**
     * @return string
     */
    public static function getFilesXPath()
    {
        return self::$filesXPath;
    }

    private $multipartBoundary = null;

    /**
     * @param string $multipartBoundary
     */
    public function setMultipartBoundary($multipartBoundary)
    {
        $this->multipartBoundary = $multipartBoundary;
    }

    /**
     * @return string
     */
    public function getMultipartBoundary()
    {
        if (null === $this->multipartBoundary) {
            $this->multipartBoundary = uniqid();
        }

        return $this->multipartBoundary;
    }

    /**
     * @param  string $xpath
     * @return AbstractInput[]
     */
    public function getInputs($xpath)
    {
        return array_map(
            [$this->getReader(), 'getInput'],
            iterator_to_array($this->getReader()->query($xpath), false)
        );
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        return $this->getAttribute('method') ?: 'GET';
    }

    /**
     * @return boolean
     */
    public function isGet()
    {
        return strtoupper($this->getMethod()) === 'GET';
    }

    /**
     * @return string
     */
    public function getAction()
    {
        return $this->getAttribute('action');
    }

    /**
     * @return boolean
     */
    public function isMultipart()
    {
        return $this->getAttribute('enctype') === 'multipart/form-data';
    }

    /**
     * @param  array  $additional
     * @return array
     */
    public function getData(array $additional = [])
    {
        $data = [];

        foreach ($this->getInputs(self::$fieldsXPath) as $input) {
            $data[$input->getName()] = $input->getValue();
        }

        return array_merge($data, $additional);
    }

    /**
     * @param  array  $additional
     * @return array
     */
    public function getMultipartData(array $additional = [])
    {
        $data = [];

        foreach ($this->getData($additional) as $name => $value) {
            $data []= [
                'name' => $name,
                'contents' => $value
            ];
        }

        foreach ($this->getInputs(self::$filesXPath) as $input) {
            $data []= [
                'name' => $input->getName(),
                'contents' => file_get_contents($input->getValue()),
                'filename' => $input->getValue(),
            ];
        }

        return $data;
    }
}
