<?php
/**
 * Created by PhpStorm.
 * User: ivan.li
 * Date: 20/06/2017
 * Time: 9:53 AM
 */

namespace IvanCLI\Crawler\Repositories\TARGET;


use IvanCLI\Crawler\Repositories\DefaultCrawler;
use Ixudra\Curl\Facades\Curl;

class APICrawler extends DefaultCrawler
{
    const API_URL = 'http://redsky.target.com/v2/pdp/tcin/';
    protected $productId;

    protected function getProductId()
    {
        preg_match('#\/A\-(\d+)#', $this->url, $matches);
        if (isset($matches[1])) {
            $this->productId = $matches[1];
        } else {
            $this->productId = null;
        }
    }

    public function fetch()
    {
        $this->getProductId();

        $apiUrl = self::API_URL . $this->productId . "?excludes=taxonomy";
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