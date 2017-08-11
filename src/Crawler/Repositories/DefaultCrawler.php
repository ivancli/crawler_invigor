<?php
/**
 * Created by PhpStorm.
 * User: Ivan
 * Date: 11/03/2017
 * Time: 3:48 PM
 */

namespace IvanCLI\Crawler\Repositories;


use IvanCLI\Crawler\Contracts\CrawlerContract;
use Ixudra\Curl\Facades\Curl;

class DefaultCrawler implements CrawlerContract
{
    protected $url;
    protected $content = null;
    protected $status = null;

    protected $ip = null;
    protected $port = null;

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
     * Set proxy IP address
     * @param $ip
     * @param null $port
     */
    public function setProxy($ip, $port = null)
    {
        $this->ip = $ip;
        $this->port = $port;
    }

    /**
     * Load content
     * @return void
     */
    public function fetch()
    {
        if (!is_null($this->ip)) {
            $response = Curl::to($this->url)
                ->withHeaders($this->headers)
                ->withOption('PROXY', $this->ip)
                ->withOption('PROXYPORT', $this->port)
                ->returnResponseObject()
                ->withOption("FOLLOWLOCATION", true)
                ->get();
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