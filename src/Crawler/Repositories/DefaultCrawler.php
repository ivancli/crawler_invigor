<?php
/**
 * Created by PhpStorm.
 * User: Ivan
 * Date: 11/03/2017
 * Time: 3:48 PM
 */

namespace IvanCLI\Crawler\Repositories;


use IvanCLI\Crawler\Contracts\CrawlerContract;
use Ixudra\Curl\Facades\Curl;

class DefaultCrawler implements CrawlerContract
{
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
        $this->content = Curl::to($this->url)->get();
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