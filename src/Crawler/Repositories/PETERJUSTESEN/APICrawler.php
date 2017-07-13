<?php
/**
 * Created by PhpStorm.
 * User: ivan.li
 * Date: 13/07/2017
 * Time: 11:01 AM
 */

namespace IvanCLI\Crawler\Repositories\PETERJUSTESEN;


use IvanCLI\Crawler\Repositories\DefaultCrawler;
use Ixudra\Curl\Facades\Curl;

class APICrawler extends DefaultCrawler
{
    const API_URL = 'https://www.pj.dk/ucommerceapi/json/reply/detailsForProduct?productId=';
    protected $productId;

    protected function getProductId()
    {
        $paths = array_get(parse_url($this->url), 'path');
        if (!is_null($paths)) {
            $productName = array_last(explode('/', $paths));
            if (!is_null($productName)) {
                $productId = array_last(explode('-', $productName));
                if (!is_null($productId)) {
                    $this->productId = $productId;
                }
            }
        }
    }

    public function fetch()
    {
        $this->getProductId();

        $apiUrl = self::API_URL . $this->productId;

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