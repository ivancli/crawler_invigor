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

    protected $email;
    const DUMMY_ACCOUNT_PASSWORD = 'S0lutions';


    private function __setEmail()
    {
        $parts = parse_url($this->url);
        parse_str(array_get($parts, 'query'), $query);
        if (array_has($query, 'state')) {
            switch (array_get($query, 'state')) {
                case 'NSW':
                    $this->email = 'nsw.aussiefarmers@gmail.com';
                    break;
                case 'QLD':
                    $this->email = 'qld.aussiefarmers@gmail.com';
                    break;
                case 'VIC':
                    $this->email = 'vic.aussiefarmers@gmail.com';
                    break;
                case 'ACT':
                    $this->email = 'act.aussiefarmers@gmail.com';
                    break;
                case 'WA':
                    $this->email = 'wa.aussiefarmers@gmail.com';
                    break;
                case 'SA':
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
                    }
                }
            }
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
        return $this->content;
    }
}