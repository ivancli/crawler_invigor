<?php
/**
 * Created by PhpStorm.
 * User: ivan.li
 * Date: 21/06/2017
 * Time: 1:09 PM
 */

namespace IvanCLI\Crawler\Repositories\LANGTONS;


use IvanCLI\Crawler\Repositories\DefaultCrawler;
use Ixudra\Curl\Facades\Curl;

class APICrawler extends DefaultCrawler
{
    const API_URL = 'https://www.langtons.com.au/api/product/get-purchase-options/';
    protected $productId;

    public function fetch()
    {
        $parts = parse_url($this->url);
        $path = array_get($parts, 'path');
        if (!is_null($path)) {
            $segments = explode('/', $path);
            if (isset($segments[2]) && isset($segments[3]) && isset($segments[4])) {
                $productId = $segments[2];
                $unit = $segments[3];
                $vintage = $segments[4];

                $postData = new \stdClass();
                $postData->ProductId = $productId;
                $postData->BottleSizeId = $unit;
                $postData->Vintage = $vintage;
                $this->headers[] = "Content-Type: application/json";

                $response = Curl::to(self::API_URL)
                    ->withHeaders($this->headers)
                    ->returnResponseObject()
                    ->withOption("FOLLOWLOCATION", true)
                    ->withData(json_encode($postData))
                    ->post();

                if (is_object($response)) {
                    $this->setContent($response->content);
                    $this->setStatus($response->status);
                }
            }
        }
    }
}