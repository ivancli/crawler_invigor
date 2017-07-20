<?php
/**
 * Created by PhpStorm.
 * User: ivan.li
 * Date: 17/07/2017
 * Time: 4:41 PM
 */

namespace IvanCLI\Crawler\Repositories\AUSSIEFARMERS;


use IvanCLI\Crawler\Repositories\DefaultCrawler;
use Ixudra\Curl\Facades\Curl;

class StateBasedCrawler extends DefaultCrawler
{
    const LOGIN_URL = "https://shop.aussiefarmers.com.au/api/auth/login/";
    const PRODUCT_REGEX = '#var INITIAL_STATE = (.*?)\<\/script#si';

    protected $accessToken;
    protected $accessTokenRepo;

    protected $secondHeaders = [
        'Accept-Language: en-us',
        'User-Agent: Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US; rv:1.9.2.15) Gecko/20110303 Firefox/3.6.15',
        'Connection: Keep-Alive',
        'Cache-Control: no-cache',
    ];

    protected $email;
    const DUMMY_ACCOUNT_PASSWORD = 'S0lutions';


    private function __setEmail()
    {
        $parts = parse_url($this->url);
        parse_str(array_get($parts, 'query'), $query);
        if (array_has($query, 'state')) {
            switch (strtolower(array_get($query, 'state'))) {
                case 'nsw':
                    $this->email = 'nsw.aussiefarmers@gmail.com';
                    break;
                case 'qld':
                    $this->email = 'qld.aussiefarmers@gmail.com';
                    break;
                case 'vic':
                    $this->email = 'vic.aussiefarmers@gmail.com';
                    break;
                case 'act':
                    $this->email = 'act.aussiefarmers@gmail.com';
                    break;
                case 'wa':
                    $this->email = 'wa.aussiefarmers@gmail.com';
                    break;
                case 'sa':
                    $this->email = 'sa.aussiefarmers@gmail.com';
                    break;
            }
        }
    }

    /**
     * Load content
     */
    public function fetch()
    {
        $this->__setEmail();
        if (!is_null($this->email)) {
            $this->loginAndCrawl();
        }
        $response = Curl::to($this->url)
            ->withHeaders($this->headers)
            ->returnResponseObject()
            ->withOption("FOLLOWLOCATION", true)
            ->get();

        if (is_object($response)) {
            if (!is_null($response->content) && !empty($response->content)) {
                preg_match(self::PRODUCT_REGEX, $response->content, $matches);
                if (isset($matches[1])) {
                    $productData = trim($matches[1]);
                    $productData = str_replace(';', '', $productData);
                    $this->setContent($productData);
                    $this->setStatus($response->status);
                    return $this->content;
                }
            }
        }
    }

    protected function loginAndCrawl()
    {
        $response = Curl::to($this->url)
            ->withHeaders($this->headers)
            ->returnResponseObject()
            ->withOption("FOLLOWLOCATION", true)
            ->withOption("HEADER", true)
            ->get();
        if (is_object($response)) {

            preg_match_all('/Set-Cookie:(.*?);/', $response->content, $m);
            if (isset($m[1])) {
                $postData = "";
                $cookies = $m[1];
                $csrfToken = null;
                foreach ($cookies as $cookie) {
                    list($index, $value) = (explode('=', $cookie, 2));
                    $postData .= "$index=$value;";
                    if (trim($index) == 'csrftoken') {
                        $csrfToken = $value;
                    }
                }
                $this->headers[] = 'Cookie:' . $postData;

                if (!is_null($csrfToken)) {
                    $this->headers [] = "x-csrftoken={$csrfToken}";
                }


                $response = Curl::to(self::LOGIN_URL)
                    ->withHeaders($this->headers)
                    ->returnResponseObject()
                    ->withOption("FOLLOWLOCATION", true)
                    ->withOption("HEADER", true)
                    ->withData([
                        "email" => $this->email,
                        "password" => self::DUMMY_ACCOUNT_PASSWORD,
                        "remember_me" => false,
                    ])
                    ->post();


                if (is_object($response)) {

                    preg_match_all('/Set-Cookie:(.*?);/', $response->content, $m);
                    if (isset($m[1])) {
                        $cookies = $m[1];
                        foreach ($cookies as $cookie) {
                            list($index, $value) = (explode('=', $cookie, 2));
                            $postData .= "$index=$value;";
                        }
                        $this->secondHeaders[] = 'Cookie:' . $postData;

                        $newResponse = Curl::to($this->url)
                            ->withHeaders($this->secondHeaders)
                            ->returnResponseObject()
                            ->withOption("FOLLOWLOCATION", true)
                            ->get();

                        if (is_object($newResponse)) {
                            if (!is_null($newResponse->content) && !empty($newResponse->content)) {
                                preg_match(self::PRODUCT_REGEX, $newResponse->content, $matches);
                                if (isset($matches[1])) {
                                    $productData = trim($matches[1]);
                                    $productData = str_replace(';', '', $productData);

                                    if (!is_null($this->content) && !empty($this->content)) {
                                        $productInfo = json_decode($productData);
                                        if (!is_null($productInfo) && json_last_error() === JSON_ERROR_NONE) {
                                            if (!isset($productInfo->userState) || $productInfo->userState != "IS_LOGGED_IN") {
                                                sleep(1);
                                                $this->loginAndCrawl();
                                                return $this->content;
                                            }
                                        }
                                    }

                                    $this->setContent($productData);
                                    $this->setStatus($newResponse->status);
                                    return $this->content;
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}