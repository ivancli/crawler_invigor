<?php
/**
 * Created by PhpStorm.
 * User: ivan.li
 * Date: 6/07/2017
 * Time: 11:11 AM
 */

namespace IvanCLI\Crawler\Repositories\KMART;


use IvanCLI\Crawler\Repositories\DefaultCrawler;
use Ixudra\Curl\Facades\Curl;

class ProductCrawler extends DefaultCrawler
{
    protected $productId;

    protected function getProductId()
    {
        preg_match('#\/p\-(\d+)#', $this->url, $matches);
        if (isset($matches[1])) {
            $this->productId = $matches[1];
        } else {
            $this->productId = null;
        }
    }

    public function fetch()
    {
        $this->getProductId();

        $this->headers[] = "Cookie: optimizelyEndUserId=oeu1499303611778r0.7328432698776077; JSESSIONID=00006UvmIqeNE1QilVXM_TwKhxq:16pjgpnr4; WC_PERSISTENT=e%2F0D%2Bri5997fzLX86PJTPpL50Fc%3D%0A%3B2017-07-06+11%3A13%3A32.106_1499303612106-42334_0; visid_incap_382817=tOAYzyLeQR2wpGiNZ3EUEruOXVkAAAAAQUIPAAAAAACnRDqPhRwz0HppFwPXJNtj; incap_ses_137_382817=9n+SYg2XKXMHQ3ww97nmAbuOXVkAAAAAKoWL2TZ0h0bnUG414p9SIA==; signUp=true; usrLS=Thu%20Jul%2006%202017%2011%3A13%3A32%20GMT%2B1000%20(AUS%20Eastern%20Standard%20Time); optimizelyBuckets=%7B%7D; optimizelySegments=%7B%222752310207%22%3A%22false%22%2C%222754970481%22%3A%22gc%22%2C%222780311006%22%3A%22direct%22%2C%222786270067%22%3A%22none%22%2C%223071380556%22%3A%22dual%2520dart%2520and%2520disc%2520b%22%2C%223774370171%22%3A%22true%22%7D; cmTPSet=Y; CoreID6=04322917811714993036131&ci=90258809; CoreM_State=11~-1~-1~-1~-1~3~3~5~3~3~7~7~|~99644919~|~~|~~|~0||||||~|~1499303613185~|~~|~~|~~|~~|~~|~~|~; CoreM_State_Content=6~|~~|~; cus_adl_state=NSW; _gat_UA-7745282-1=1; 90258809_clogin=v=1&l=1499303613&e=1499305414032; _ga=GA1.3.1214198754.1499303614; _gid=GA1.3.1284405540.1499303614; _dc_gtm_UA-7745282-1=1; _fby_site_=1%7Ckmart.com.au%7C1499303614%7C1499303614%7C1499303614%7C1499303614%7C1%7C1%7C1";
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