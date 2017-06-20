<?php
/**
 * Created by PhpStorm.
 * User: ivan.li
 * Date: 20/06/2017
 * Time: 3:26 PM
 */

namespace IvanCLI\Crawler\Repositories\ASDA;


use IvanCLI\Crawler\Repositories\DefaultCrawler;
use Ixudra\Curl\Facades\Curl;

class APICrawler extends DefaultCrawler
{
    const API_URL = "https://groceries.asda.com/api/items/view?itemid=";

    protected $accessToken;
    protected $accessTokenRepo;

    const ITEM_URL = 'https://api.ebay.com/buy/browse/v1/item/';
    const ITEM_GROUP_URL = 'https://api.ebay.com/buy/browse/v1/item/get_items_by_item_group?item_group_id=';

    /**
     * Load content
     * @return void
     */
    public function fetch()
    {
        $uri_path = parse_url($this->url, PHP_URL_PATH);
        $uri_segments = explode('/', $uri_path);
        $itemid = $uri_segments[count($uri_segments) - 1];

        $apiUrl = self::API_URL . $itemid . "&responsegroup=extended&shipdate=currentDate";

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