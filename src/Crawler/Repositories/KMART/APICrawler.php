<?php
/**
 * Created by PhpStorm.
 * User: ivan.li
 * Date: 20/06/2017
 * Time: 1:03 PM
 */

namespace IvanCLI\Crawler\Repositories\KMART;


use IvanCLI\Crawler\Repositories\DefaultCrawler;
use Ixudra\Curl\Facades\Curl;

class APICrawler extends DefaultCrawler
{
    const API_URL = 'http://www.kmart.com/content/pdp/products/pricing/v2/get/price/display/json?offer=';
    protected $productId;

    protected function getProductId()
    {
        preg_match('#\/p\-(\d+)#', $this->url, $matches);
        if (isset($matches[1])) {
            $this->productId = $matches[1];
        } else {
            $this->productId = null;
        }
    }

    public function fetch()
    {
        $this->getProductId();

        $apiUrl = self::API_URL . $this->productId . "&priceMatch=Y&memberType=G&urgencyDeal=Y&site=KMART";
        $this->headers[] = "AuthID:aA0NvvAIrVJY0vXTc99mQQ==";
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