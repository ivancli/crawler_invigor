<?php
/**
 * Created by PhpStorm.
 * User: ivan.li
 * Date: 18/08/2017
 * Time: 9:14 AM
 */

namespace IvanCLI\Crawler\Repositories\CHEMISTDISCOUNTCENTRE;


use IvanCLI\Crawler\Repositories\DefaultCrawler;
use Ixudra\Curl\Facades\Curl;

class APICrawler extends DefaultCrawler
{
    const API_URL = 'https://www.chemistdiscountcentre.com.au/breeze/frontend/Products';

    public function fetch()
    {
        preg_match('#prod\/(\d+)\/#', $this->url, $skuMatches);

        $sku = array_get($skuMatches, 1);
        if (!is_null($sku)) {
            $response = Curl::to(self::API_URL . '?$filter=Sku%20eq%20%27' . $sku . '%27')
                ->withHeaders($this->headers)
                ->returnResponseObject()
                ->withOption("FOLLOWLOCATION", true)
                ->get();
        } else {
            $response = Curl::to($this->url)
                ->withHeaders($this->headers)
                ->returnResponseObject()
                ->withOption("FOLLOWLOCATION", true)
                ->get();
        }
        if (is_object($response)) {
            $this->setContent($response->content);
            $this->setStatus($response->status);
        }
    }
}