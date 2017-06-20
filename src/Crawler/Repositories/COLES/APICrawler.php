<?php

/**
 * Created by PhpStorm.
 * User: ivan.li
 * Date: 20/06/2017
 * Time: 4:13 PM
 */

namespace IvanCLI\Crawler\Repositories\COLES;

use IvanCLI\Crawler\Repositories\DefaultCrawler;
use Ixudra\Curl\Facades\Curl;

class APICrawler extends DefaultCrawler
{
    const API_URL = 'https://shop.coles.com.au/search/resources/store/';
    protected $productId;
    protected $productName;

    protected function getProductInfo()
    {
        $response = Curl::to($this->url)
            ->withHeaders($this->headers)
            ->returnResponseObject()
            ->withOption("FOLLOWLOCATION", true)
            ->get();


        preg_match('#\'storeId\': (.*?),#', $response->content, $matches);
        $this->productName = basename(parse_url($this->url)['path']);

        if (isset($matches[1])) {
            $this->productId = $matches[1];
        } else {
            $this->productId = null;
        }
    }

    public function fetch()
    {
        $this->getProductInfo();

        $apiUrl = self::API_URL . $this->productId . "/productview/bySeoUrlKeyword/" . $this->productName;

        $response = Curl::to($apiUrl)
            ->withHeaders($this->headers)
            ->returnResponseObject()
            ->withOption("FOLLOWLOCATION", true)
            ->get();

        if (is_object($response)) {
            $this->setContent($response->content);
            $this->setStatus($response->status);
        }
    }
}