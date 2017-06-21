<?php
/**
 * Created by PhpStorm.
 * User: Ivan
 * Date: 21/06/2017
 * Time: 10:09 PM
 */

namespace IvanCLI\Crawler\Repositories\WOOLWORTHS;


use IvanCLI\Crawler\Repositories\DefaultCrawler;
use Ixudra\Curl\Facades\Curl;

class APICrawler extends DefaultCrawler
{
    const API_URL = 'https://www.woolworths.com.au/apis/ui/product/detail/';
    protected $productId;

    protected function getProductId()
    {
        $urlData = parse_url($this->url);
        $queryString = array_get($urlData, 'query');
        if (!is_null($queryString)) {
            parse_str($queryString, $query);
            $this->productId = array_get($query, 'productId');
        } else {
            $paths = array_get($urlData, 'path');
            $segments = explode('/', $paths);
            if (isset($segments[3])) {
                $this->productId = $segments[3];
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