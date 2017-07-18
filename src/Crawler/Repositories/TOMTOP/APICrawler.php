<?php
/**
 * Created by PhpStorm.
 * User: ivan.li
 * Date: 18/07/2017
 * Time: 11:04 AM
 */

namespace IvanCLI\Crawler\Repositories\TOMTOP;


use IvanCLI\Crawler\Repositories\DefaultCrawler;
use Ixudra\Curl\Facades\Curl;

class APICrawler extends DefaultCrawler
{
    const PRODUCT_INFO_REGEX = '#var mainContent = (.*?)\<\/script#si';

    public function fetch()
    {
        $response = Curl::to($this->url)
            ->withHeaders($this->headers)
            ->returnResponseObject()
            ->withOption("FOLLOWLOCATION", true)
            ->get();
        if (is_object($response) && $response->status == 200) {
            $content = $response->content;
            preg_match(self::PRODUCT_INFO_REGEX, $content, $matches);
            if (isset($matches[1])) {
                $productInfo = trim($matches[1]);
                $productInfo = str_replace(';', '', $productInfo);
                $this->setContent($productInfo);
                $this->setStatus($response->status);
            }
        }
    }
}