<?php
/**
 * Created by PhpStorm.
 * User: ivan.li
 * Date: 21/06/2017
 * Time: 3:39 PM
 */

namespace IvanCLI\Crawler\Repositories\LIQUOREMPORIUM;


use IvanCLI\Crawler\Repositories\DefaultCrawler;
use Ixudra\Curl\Facades\Curl;

class APICrawler extends DefaultCrawler
{
    const API_URL = 'http://ecom.wix.com/storefront/product/';
    protected $productId;
    protected $instance;

    protected function getProductInfo()
    {
        $response = Curl::to($this->url)
            ->withHeaders($this->headers)
            ->returnResponseObject()
            ->withOption("FOLLOWLOCATION", true)
            ->get();
        if ($response->status == 200) {
            preg_match('#var rendererModel \\= (.+)\\;#', $response->content, $matches);

            if (count($matches) > 0) {
                $match = $matches[0];
                $match = str_replace("var rendererModel = ", "", $match);
                $match = str_replace(";", "", $match);
                $rendererModel = json_decode($match);

                if (!is_null($rendererModel->clientSpecMap)) {
                    $collection = collect($rendererModel->clientSpecMap);
                    $renderer = $collection->filter(function ($value) {
                        return isset($value->appDefinitionName) && $value->appDefinitionName == "Wix Stores";
                    })->first();
                    if (!is_null($renderer)) {
                        $this->instance = $renderer->instance;
                        preg_match('#<meta property="og:url" content="(.*?)product-page/(.*?)"/>#', $response->content, $productNameMatches);
                        if (isset($productNameMatches[2])) {
                            $this->productId = $productNameMatches[2];
                        }
                    }
                }
            }
        }
        return false;
    }

    public function fetch()
    {
        $this->getProductInfo();

        $apiUrl = self::API_URL . $this->productId . "?cacheKiller=" . time() . "&instance=" . $this->instance;
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