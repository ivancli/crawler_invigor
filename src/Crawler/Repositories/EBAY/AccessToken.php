<?php
/**
 * Created by PhpStorm.
 * User: ivan.li
 * Date: 2/06/2017
 * Time: 11:49 AM
 */

namespace IvanCLI\Crawler\Repositories\EBAY;


use Illuminate\Support\Facades\Cache;
use IvanCLI\Crawler\Repositories\DefaultCrawler;
use Ixudra\Curl\Facades\Curl;

class AccessToken extends DefaultCrawler
{
    protected $accessToken;

    const EBAY_ACCESS_TOKEN_URL = 'https://api.ebay.com/identity/v1/oauth2/token';

    protected function pushHeader($header)
    {
        $this->headers[] = $header;
    }

    public function getAccessToken()
    {
        if (is_null($this->accessToken)) {
            $this->request();
        }
        return $this->accessToken;
    }

    protected function request()
    {
        $this->accessToken = Cache::remember('ebay_access_token', 110, function () {
            $authKey = $this->__authKey();
            $this->pushHeader("Authorization: Basic {$authKey}");
            $this->pushHeader("Content-Type: application/x-www-form-urlencoded");
            $result = Curl::to(self::EBAY_ACCESS_TOKEN_URL)
                ->withHeaders($this->headers)
                ->returnResponseObject()
                ->withData([
                    "grant_type" => "client_credentials",
                    "scope" => "https://api.ebay.com/oauth/api_scope"
                ])
                ->post();
            if ($result->status == 200) {
                $content = json_decode($result->content);
                if (isset($content->access_token)) {
                    return $content->access_token;
                }
            }
        });
    }

    private function __authKey()
    {
        $client_id = config('ebay.client_id');
        $client_secret = config('ebay.client_secret');
        $authKey = base64_encode("{$client_id}:{$client_secret}");
        return $authKey;
    }

}