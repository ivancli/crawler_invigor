<?php
/**
 * Created by PhpStorm.
 * User: ivan.li
 * Date: 15/08/2017
 * Time: 4:30 PM
 */

namespace IvanCLI\Crawler\Repositories\BEAUTYBAY;


use IvanCLI\Crawler\Repositories\DefaultCrawler;
use Ixudra\Curl\Facades\Curl;

class APICrawler extends DefaultCrawler
{
    const API_URL = "https://product-detail-api.service.beautybay.com/detail/production/";
    const API_URL_PARAMS = "?deliveryCountryId=14&regionCode=AUS&currencyCode=AUD";

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
        $parsedUrl = parse_url($this->url, PHP_URL_PATH);
        $segments = explode('/', $parsedUrl);

        if (end($segments) == '') {
            $segment = array_get($segments, count($segments) - 3) . array_get($segments, count($segments) - 2);
        } else {
            $segment = array_get($segments, count($segments) - 2) . end($segments);
        }

        $url = self::API_URL . $segment . self::API_URL_PARAMS;
        $response = Curl::to($url)
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