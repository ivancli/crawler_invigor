<?php

/**
 * Created by PhpStorm.
 * User: ivan.li
 * Date: 12/07/2017
 * Time: 11:09 AM
 */

namespace IvanCLI\Crawler\Repositories\ADIDAS\INDIA;

use IvanCLI\Crawler\Repositories\DefaultCrawler;
use Ixudra\Curl\Facades\Curl;

class APICrawler extends DefaultCrawler
{
    const API_BASE_LINK_FIRST_PART = 'https://shop.adidas.co.in/gateway/catalog/api/product/ADIDAS_IN/';
    const API_BASE_LINK_SECOND_PART = '/urlkey';

    protected $apiLink;

    /**
     * Load content
     * @return void
     */
    public function fetch()
    {

        $this->getAPILink();
        if (!is_null($this->apiLink)) {
            $response = Curl::to($this->apiLink)
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

    protected function getAPILink()
    {
        $parts = parse_url($this->url);
        $fragments = explode('/', array_get($parts, 'fragment'));
        $productKey = array_last($fragments);

        $this->apiLink = self::API_BASE_LINK_FIRST_PART . $productKey . self::API_BASE_LINK_SECOND_PART;
    }
}