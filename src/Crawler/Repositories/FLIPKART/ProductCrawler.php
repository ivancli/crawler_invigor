<?php
/**
 * Created by PhpStorm.
 * User: ivan.li
 * Date: 21/07/2017
 * Time: 10:39 AM
 */

namespace IvanCLI\Crawler\Repositories\FLIPKART;


use IvanCLI\Crawler\Repositories\DefaultCrawler;
use Ixudra\Curl\Facades\Curl;

class ProductCrawler extends DefaultCrawler
{
    const PRODUCT_INFO_REGEX = '#window.__INITIAL_STATE__ = (.*?)\<\/script#si';

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