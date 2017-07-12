<?php
/**
 * Created by PhpStorm.
 * User: ivan.li
 * Date: 12/07/2017
 * Time: 2:22 PM
 */

namespace IvanCLI\Crawler\Repositories\BOOZEBUD;


use IvanCLI\Crawler\Repositories\DefaultCrawler;
use Ixudra\Curl\Facades\Curl;

class APICrawler extends DefaultCrawler
{
    const MULTIPLE_ITEM_API_BASE_LINK_FIRST_PART = 'https://www.boozebud.com/a/product/';
    const SINGLE_ITEM_API_BASE_LINK_FIRST_PART = 'https://www.boozebud.com/a/producturl';

    protected $apiLink;

    /**
     * Load content
     * @return void
     */
    public function fetch()
    {
        $this->getMultipleItemAPILink();
        if (!is_null($this->apiLink)) {
            $response = Curl::to($this->apiLink)
                ->withHeaders($this->headers)
                ->returnResponseObject()
                ->withOption("FOLLOWLOCATION", true)
                ->get();
            if ($response->content == 'null') {
                $this->getSingleItemAPILink();
                $response = Curl::to($this->apiLink)
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

    protected function getMultipleItemAPILink()
    {
        $parts = parse_url($this->url);
        $paths = explode('/', array_get($parts, 'path'));
        $productKey = array_last($paths);

        $this->apiLink = self::MULTIPLE_ITEM_API_BASE_LINK_FIRST_PART . $productKey;
    }

    protected function getSingleItemAPILink()
    {
        $parts = parse_url($this->url);
        $paths = array_get($parts, 'path');

        $this->apiLink = self::SINGLE_ITEM_API_BASE_LINK_FIRST_PART . $paths;
    }
}