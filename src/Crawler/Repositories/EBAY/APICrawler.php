<?php
/**
 * Created by PhpStorm.
 * User: ivan.li
 * Date: 2/06/2017
 * Time: 11:36 AM
 */

namespace IvanCLI\Crawler\Repositories\EBAY;


use IvanCLI\Crawler\Repositories\DefaultCrawler;
use Ixudra\Curl\Facades\Curl;
use Maatwebsite\Excel\Classes\Cache;

class APICrawler extends DefaultCrawler
{
    protected $accessToken;
    protected $accessTokenRepo;

    const ITEM_URL = 'https://api.ebay.com/buy/browse/v1/item/';
    const ITEM_GROUP_URL = 'https://api.ebay.com/buy/browse/v1/item/get_items_by_item_group?item_group_id=';

    public function __construct(AccessToken $accessTokenRepo)
    {
        $this->accessTokenRepo = $accessTokenRepo;
    }

    protected function pushHeader($header)
    {
        $this->headers[] = $header;
    }

    /**
     * Load content
     * @return void
     */
    public function fetch()
    {
        $this->getAccessToken();
        $this->pushHeader("Authorization: Bearer {$this->accessToken}");

        $path = array_get(parse_url($this->url), 'path');
        $segments = explode('/', $path);
        $id = array_last($segments);

        $this->__fetchItem($id);
        if ($this->status != 200) {
            $this->__fetchItemGroup($id);
        }
    }

    private function __fetchItem($id)
    {
        if (starts_with($id, 'v1')) {
            $id = urlencode($id);
            $url = self::ITEM_URL . $id;
        } else {
            $url = self::ITEM_URL . "v1%7C{$id}%7C0";
        }

        $response = Curl::to($url)
            ->withHeaders($this->headers)
            ->returnResponseObject()
            ->get();
        if (is_object($response)) {
            $this->setContent($response->content);
            $this->setStatus($response->status);
        }
    }

    private function __fetchItemGroup($id)
    {
        $url = self::ITEM_GROUP_URL . $id;
        $response = Curl::to($url)
            ->withHeaders($this->headers)
            ->returnResponseObject()
            ->get();
        if (is_object($response)) {
            $this->setContent($response->content);
            $this->setStatus($response->status);
        }
    }

    protected function getAccessToken()
    {
        $this->accessToken = $this->accessTokenRepo->getAccessToken();
    }

}