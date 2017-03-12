<?php
namespace IvanCLI\Crawler\Traits;
/**
 * Created by PhpStorm.
 * User: ivan.li
 * Date: 9/12/2016
 * Time: 10:38 AM
 */
trait Curler
{
    private $ch;
    private $curlHeaders = array(
        'Accept-Language: en-us',
        'User-Agent: Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US; rv:1.9.2.15) Gecko/20110303 Firefox/3.6.15',
        'Connection: Keep-Alive',
        'Cache-Control: no-cache',
    );
    private $curlURL;
    private $ips = array();
    private $cookieFile = null;
    private $sslCheck = 1;
    private $timeout = 120;

    /**
     *
     */
    private function initConnection()
    {
        $this->ch = curl_init();
    }

    /**
     *
     */
    private function closeConnection()
    {
        curl_close($this->ch);
    }

    /**
     * @return array
     */
    protected function getCurlHeaders()
    {
        return $this->curlHeaders;
    }

    /**
     * @param array $headers
     * @return array
     */
    protected function setCurlHeaders($headers = array())
    {
        $this->curlHeaders = $headers;
        return $this->curlHeaders;
    }

    /**
     * @param $header
     * @return array
     */
    protected function pushCurlHeaders($header)
    {
        $this->curlHeaders[] = $header;
        return $this->curlHeaders;
    }

    /**
     * @return mixed
     */
    protected function getCurlURL()
    {
        return $this->curlURL;
    }

    /**
     * @param $url
     * @return mixed
     */
    protected function setCurlURL($url)
    {
        $this->curlURL = $url;
        return $this->curlURL;
    }

    /**
     * @return array
     */
    protected function getIPs()
    {
        return $this->ips;
    }

    /**
     * @param array $ips
     * @return array
     */
    protected function setIPs($ips = array())
    {
        $this->ips = $ips;
        return $this->ips;
    }

    /**
     * @param $ip
     * @return array
     */
    protected function pushIP($ip)
    {
        $this->ips [] = $ip;
        return $this->ips;
    }

    /**
     * @return null
     */
    protected function getCookieFile()
    {
        return $this->cookieFile;
    }

    /**
     * @param $file
     * @return null
     */
    protected function setCookieFile($file)
    {
        $this->cookieFile = $file;
        return $this->cookieFile;
    }

    /**
     * @return int
     */
    protected function disableSSLCheck()
    {
        $this->sslCheck = 0;
        return $this->sslCheck;
    }

    /**
     * @return int
     */
    protected function enableSSLCheck()
    {
        $this->sslCheck = 1;
        return $this->sslCheck;
    }

    /**
     * @param $seconds
     * @return int
     */
    protected function timeout($seconds)
    {
        $this->timeout = $seconds;
        return $this->timeout;
    }

    /**
     * @return mixed
     */
    protected function sendCurl()
    {
        $this->initConnection();
        curl_setopt($this->ch, CURLOPT_URL, $this->curlURL);

        if (!empty($this->ips)) {
            $ipRandKey = array_rand($this->ips, 1);
            curl_setopt($this->ch, CURLOPT_INTERFACE, $this->ips[$ipRandKey]);
        }

        if (!is_null($this->cookieFile)) {
            curl_setopt($this->ch, CURLOPT_COOKIEFILE, $this->cookieFile);
            curl_setopt($this->ch, CURLOPT_COOKIEJAR, $this->cookieFile);
        }

        curl_setopt($this->ch, CURLOPT_HTTPHEADER, $this->curlHeaders);
        curl_setopt($this->ch, CURLOPT_HEADER, 1);
        curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($this->ch, CURLOPT_CONNECTTIMEOUT, 0);
        curl_setopt($this->ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, $this->sslCheck);

        $buffer = curl_exec($this->ch);
        $this->closeConnection();
        unset($ch);
        return $buffer;
    }
}