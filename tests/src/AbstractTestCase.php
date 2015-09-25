<?php

namespace SP\Crawler\Test;

use PHPUnit_Framework_TestCase;
use DOMDocument;
use SP\Crawler\Crawler;
use SP\Crawler\Reader;
use SP\PhpunitDomConstraints\DomConstraintsTrait;

abstract class AbstractTestCase extends PHPUnit_Framework_TestCase
{
    use DomConstraintsTrait;

    private static $indexContent;

    public static function setUpBeforeClass()
    {
        self::$indexContent = file_get_contents(self::getFilesDir().'index.html');
    }

    public static function getFilesDir()
    {
        return __DIR__.'/../files/';
    }

    /**
     * @var DOMDocument
     */
    protected $document;

    /**
     * @var Crawler
     */
    protected $crawler;

    /**
     * @var LoaderInterface
     */
    protected $loader;

    /**
     * @var Reader
     */
    protected $reader;

    public function setUp()
    {
        $this->document = new DOMDocument();
        $this->document->loadHtml(self::$indexContent);
        $this->loader = $this->getMock('SP\Crawler\LoaderInterface');

        $this->crawler = new Crawler($this->loader, $this->document);
        $this->reader = new Reader($this->document);
    }
}
