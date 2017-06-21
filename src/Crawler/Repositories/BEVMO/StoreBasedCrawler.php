<?php
/**
 * Created by PhpStorm.
 * User: ivan.li
 * Date: 22/06/2017
 * Time: 9:42 AM
 */

namespace IvanCLI\Crawler\Repositories\BEVMO;


use IvanCLI\Crawler\Repositories\DefaultCrawler;
use Ixudra\Curl\Facades\Curl;

class StoreBasedCrawler extends DefaultCrawler
{
    const API_URL = 'http://www.bevmo.com/bevmostoreselector/select/setoption';
    const CSRF_XPATH = '//*[@id=\'CSRFToken\']/@value';
    protected $storeId;
    protected $cookie = "";

    protected function getStoreId()
    {
        $parts = parse_url($this->url);
        if (array_has($parts, 'query')) {
            parse_str(array_get($parts, 'query'), $query);
            $storeId = array_get($query, 's');
            $this->storeId = $storeId;
        }
    }

    protected function getCookie()
    {
        $response = Curl::to($this->url)
            ->withHeaders($this->headers)
            ->returnResponseObject()
            ->withOption("FOLLOWLOCATION", true)
            ->withOption("HEADER", true)
            ->get();

        if ($response->status == 200) {
            /*cookie*/
            preg_match_all('/Set-Cookie:(.*?);/', $response->content, $m);
            if (isset($m[1])) {
                $cookies = $m[1];
                foreach ($cookies as $cookie) {
                    list($index, $value) = (explode('=', $cookie, 2));
                    $this->cookie .= "$index=$value;";
                }
            }
        }
    }

    public function fetch()
    {
        $this->getStoreId();
        if (!is_null($this->storeId)) {
            $this->getCookie();
            if (!empty($this->cookie)) {
                $this->headers[] = "Cookie:{$this->cookie}";
            }
            $response = Curl::to(self::API_URL)
                ->withHeaders($this->headers)
                ->returnResponseObject()
                ->withOption("FOLLOWLOCATION", true)
                ->withOption("HEADER", true)
                ->withData([
                    "return_url" => $this->url,
                    "fulfillment_type" => "ncr_storepickup_pickup",
                    "location" => $this->storeId,
                    "ship_state" => 1,
                    "delivery_zip" => ""
                ])
                ->post();
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