<?php
/**
 * Created by PhpStorm.
 * User: ivan.li
 * Date: 20/06/2017
 * Time: 11:23 AM
 */

namespace IvanCLI\Crawler\Repositories;


use IvanCLI\Crawler\Contracts\CrawlerContract;
use Ixudra\Curl\Facades\Curl;

class EntranceCrawler implements CrawlerContract
{
    protected $url;
    protected $content = null;
    protected $status = null;
    protected $headers = [
        'Accept-Language: en-us',
        'User-Agent: Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US; rv:1.9.2.15) Gecko/20110303 Firefox/3.6.15',
        'Connection: Keep-Alive',
        'Cache-Control: no-cache',
    ];

    /**
     * set target URL
     * @param $url
     * @return void
     */
    public function setURL($url)
    {
        $this->url = $url;
    }

    /**
     * Load content
     * @return void
     */
    public function fetch()
    {
        $response = Curl::to(domainWithProtocol($this->url))
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
                foreach ($cookies as $cookie) {
                    list($index, $value) = (explode('=', $cookie, 2));
                    $postData .= "$index=$value;";
                }
                $this->headers[] = 'Cookie:' . $postData;

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
    }

    /**
     * Get loaded content
     * @return mixed
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Get request status code
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Update content property
     * @param $content
     * @return void
     */
    public function setContent($content)
    {
        $this->content = $content;
    }

    /**
     * Update status property
     * @param $status
     * @return void
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }
}