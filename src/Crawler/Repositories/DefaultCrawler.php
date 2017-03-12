<?php
/**
 * Created by PhpStorm.
 * User: Ivan
 * Date: 11/03/2017
 * Time: 3:48 PM
 */

namespace IvanCLI\Crawler\Repositories;


use IvanCLI\Crawler\Contracts\CrawlerContract;
use IvanCLI\Crawler\Traits\Curler;

class DefaultCrawler implements CrawlerContract
{
    use Curler;

    protected $url;
    protected $content;

    public function __construct($url)
    {
        $this->setURL($url);
        $this->fetch();
    }

    /**
     * set target URL
     * @param $url
     * @return mixed
     */
    public function setURL($url)
    {
        $this->url = $url;
    }

    /**
     * Load content
     * @return mixed
     */
    public function fetch()
    {
        $this->setCurlURL($this->url);
        $this->content = $this->sendCurl();
    }

    /**
     * Get loaded content
     * @return mixed
     */
    public function getContent()
    {
        return $this->content;
    }
}