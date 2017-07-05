<?php
/**
 * Created by PhpStorm.
 * User: ivan.li
 * Date: 5/07/2017
 * Time: 2:03 PM
 */

namespace IvanCLI\Crawler\Repositories\APPLEJACK;


use IvanCLI\Crawler\Repositories\DefaultCrawler;
use Ixudra\Curl\Facades\Curl;
use Symfony\Component\DomCrawler\Crawler;

class APICrawler extends DefaultCrawler
{
    const PRODUCT_API_LINK_XPATH = '//body/img[1]';

    protected $apiLink;

    /**
     * Load content
     * @return void
     */
    public function fetch()
    {

        $this->getAPILink();
        if (!is_null($this->apiLink)) {
            $response = Curl::to($this->apiLink)
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

    protected function getAPILink()
    {
        $response = Curl::to($this->url)
            ->withHeaders($this->headers)
            ->returnResponseObject()
            ->withOption("FOLLOWLOCATION", true)
            ->get();
        if ($response->status == 200 && !empty($response->content)) {
            $crawler = new Crawler($response->content);
            $imgNode = $crawler->filterXPath(self::PRODUCT_API_LINK_XPATH)->first();
            if (!is_null($imgNode)) {

                $domain = array_get(parse_url($this->url), 'host');
                $this->apiLink = $domain . $imgNode->attr("src");
            }
        }
    }
}