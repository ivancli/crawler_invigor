<?php
/**
 * Created by PhpStorm.
 * User: Ivan
 * Date: 11/03/2017
 * Time: 3:23 PM
 */

namespace IvanCLI\Crawler\Contracts;


interface CrawlerContract
{
    /**
     * set target URL
     * @param $url
     * @return mixed
     */
    public function setURL($url);

    /**
     * Load content
     * @return mixed
     */
    public function fetch();

    /**
     * Get loaded content
     * @return mixed
     */
    public function getContent();
}