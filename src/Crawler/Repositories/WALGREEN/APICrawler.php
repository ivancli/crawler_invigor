<?php
/**
 * Created by PhpStorm.
 * User: ivan.li
 * Date: 22/06/2017
 * Time: 4:16 PM
 */

namespace IvanCLI\Crawler\Repositories\WALGREEN;


use IvanCLI\Crawler\Repositories\DefaultCrawler;
use Ixudra\Curl\Facades\Curl;

class APICrawler extends DefaultCrawler
{
    const API_URL = 'https://www.walgreens.com/svc/products/';
    protected $productId;

    protected function getProductId()
    {
        preg_match('#ID=(.*?)-product#', $this->url, $matches);
        if (isset($matches[1])) {
            $this->productId = $matches[1];
        }
    }

    public function fetch()
    {
        $this->getProductId();

        if (!is_null($this->productId)) {

        }
        $apiUrl = self::API_URL . $this->productId . "/(PriceInfo+Inventory+ProductInfo+ProductDetails)";
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