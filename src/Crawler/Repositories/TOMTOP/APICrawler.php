<?php
/**
 * Created by PhpStorm.
 * User: ivan.li
 * Date: 18/07/2017
 * Time: 11:04 AM
 */

namespace IvanCLI\Crawler\Repositories\TOMTOP;


use IvanCLI\Crawler\Repositories\DefaultCrawler;
use Ixudra\Curl\Facades\Curl;

class APICrawler extends DefaultCrawler
{
    const API_URL = 'https://www.tomtop.com/index.php';
    const LIST_ID_REGEX = '#var allListingIds = \'(.*?)\';#';
    const R_PARAM = 'details/activity/ajaxactivityprice';
    protected $listId;
    protected $cookie;

    protected function getListId()
    {
        $response = Curl::to($this->url)
            ->withHeaders($this->headers)
            ->returnResponseObject()
            ->withOption("FOLLOWLOCATION", true)
            ->withOption("HEADER", true)
            ->get();

        if ($response->status == 200) {
            $content = $response->content;
            preg_match(self::LIST_ID_REGEX, $content, $matches);
            if (isset($matches[1])) {
                $this->listId = $matches[1];
            }

            preg_match_all('/Set-Cookie:(.*?);/', $response->content, $m);
            if (isset($m[1])) {
                $cookies = $m[1];
                foreach ($cookies as $cookie) {
                    list($index, $value) = (explode('=', $cookie, 2));
                    $this->cookie .= "$index=$value;";
                }
                $this->headers[] = 'Cookie: ' . $this->cookie;
            }
        }
    }

    public function fetch()
    {
        $this->getListId();

        $apiUrl = self::API_URL . "?r=" . self::R_PARAM . "&listingIds={$this->listId}";
        $response = Curl::to($apiUrl)
            ->withHeaders($this->headers)
            ->returnResponseObject()
            ->withContentType('application/json')
            ->withOption("FOLLOWLOCATION", true)
            ->get();

        if (is_object($response)) {
            $this->setContent($response->content);
            $this->setStatus($response->status);
        }
    }
}