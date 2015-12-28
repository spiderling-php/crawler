<?php

namespace SP\Crawler\Element;

use Guzzlehttp\Psr7;

/**
 * @author    Ivan Kerin <ikerin@gmail.com>
 * @copyright 2015, Clippings Ltd.
 * @license   http://spdx.org/licenses/BSD-3-Clause
 */
class File extends Input
{
    public function getPhpFileArray()
    {
        if ($this->getValue()) {
            $tmpFile = tempnam(sys_get_temp_dir(), 'crawler-uploaded-file');
            copy($this->getValue(), $tmpFile);

            return [
                'tmp_name' => $tmpFile,
                'size' => filesize($this->getValue()),
                'error' => UPLOAD_ERR_OK,
                'name' => $this->getValue(),
                'type' => Psr7\mimetype_from_filename($this->getValue())
            ];
        } else {
            return [
                'tmp_name' => '',
                'size' => '0',
                'error' => UPLOAD_ERR_NO_FILE,
                'name' => '',
                'type' => ''
            ];
        }
    }
}
