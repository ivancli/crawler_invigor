<?php
/**
 * Created by PhpStorm.
 * User: ivan.li
 * Date: 22/06/2017
 * Time: 9:09 AM
 */

namespace IvanCLI\Crawler\Repositories\HEB;


use IvanCLI\Crawler\Repositories\DefaultCrawler;
use Ixudra\Curl\Facades\Curl;
use Symfony\Component\DomCrawler\Crawler;

class StoreBasedCrawler extends DefaultCrawler
{
    const API_URL = 'https://www.heb.com/includes/setpreferredstore.jsp';
    protected $storeId;
    protected $csrfToken;
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

    protected function updateLocation()
    {
        $this->getCookie();
        $this->headers[] = "Cookie:{$this->cookie}";
        $response = Curl::to(self::API_URL)
            ->withHeaders($this->headers)
            ->returnResponseObject()
            ->withOption("FOLLOWLOCATION", true)
            ->withOption("HEADER", true)
            ->withData([
                "storeId" => $this->storeId,
            ])
            ->post();

        if ($response->status == 200) {

            preg_match_all('/Set-Cookie:(.*?);/', $response->content, $m);
            $this->cookie = "";
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
            $this->updateLocation();
        }

        if (!empty($this->cookie)) {
            $this->headers[] = "Cookie:{$this->cookie}";
        }
        $response = Curl::to($this->url)
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