<?php
/**
 * Created by PhpStorm.
 * User: ivan.li
 * Date: 3/08/2017
 * Time: 4:09 PM
 */

namespace IvanCLI\Crawler\Repositories\WEGO;


use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use IvanCLI\Crawler\Contracts\CrawlerContract;
use Ixudra\Curl\Facades\Curl;

class ProductListCrawler implements CrawlerContract
{
    protected $legsListAPI = "https://srv.wego.com/v2/metasearch/flights/searches?currencyCode=KWD&locale=en";

    protected $count = 0;
    protected $resultCount = 0;

    protected $url;
    protected $content = null;
    protected $status = null;

    public function __construct()
    {

    }

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
     *
     */
    public function fetch()
    {
        $this->count++;
        $url = $this->url;

        $segments = explode('/', $url);
        $lastSegment = end($segments);

        $class = array_get($segments, count($segments) - 2);

        $depArrDateSegment = array_get($segments, count($segments) - 3);
        list($departureDate, $arrivalDate) = explode(':', $depArrDateSegment);

        $depArrSegment = array_get($segments, count($segments) - 4);
        list($departureCode, $arrivalCode) = explode('-', $depArrSegment);

        $departureCode = str_replace('c', '', $departureCode);
        $arrivalCode = str_replace('c', '', $arrivalCode);

        list($adultCount, $childCount, $infantCount) = explode(":", $lastSegment);

        $adultCount = intval($adultCount);
        $childCount = intval($childCount);
        $infantCount = intval($infantCount);

        $searchRequestData = new \stdClass();
        $searchRequestData->search = new \stdClass();
        $searchRequestData->offset = $this->resultCount;
        $searchRequestData->paymentMethodIds = [97];
        $searchRequestData->providerTypes = [];
        $searchRequestData->search->cabin = $class;
        $searchRequestData->search->deviceType = "DESKTOP";
        $searchRequestData->search->appType = "WEB_APP";
        $searchRequestData->search->userLoggedIn = false;
        $searchRequestData->search->adultsCount = $adultCount;
        $searchRequestData->search->childrenCount = $childCount;
        $searchRequestData->search->infantsCount = $infantCount;
        $searchRequestData->search->siteCode = "KW";
        $searchRequestData->search->currencyCode = "KWD";
        $searchRequestData->search->locale = "en";

        $searchRequestData->search->legs = [];

        $departureLeg = new \stdClass();
        $departureLeg->departureCityCode = $departureCode;
        $departureLeg->arrivalAirportCode = $arrivalCode;
        $departureLeg->outboundDate = $departureDate;

        $arrivalLeg = new \stdClass();
        $arrivalLeg->departureAirportCode = $arrivalCode;
        $arrivalLeg->arrivalCityCode = $departureCode;
        $arrivalLeg->outboundDate = $arrivalDate;

        $searchRequestData->search->legs[] = $departureLeg;
        $searchRequestData->search->legs[] = $arrivalLeg;


        $content = $this->__sendPostRequest('https://srv.wego.com/v2/metasearch/flights/searches?currencyCode=KWD&locale=en', $searchRequestData);
        if (!is_null($content)) {
            $id = $content->search->id;
            $searchRequestData->search->id = $id;

            $this->__sendGetRequest('https://secure.wego.com/analytics/context');
            $this->__sendOptionsRequest('https://secure.wego.com/analytics/v2/flights/searches');

            $newResponseContent = $this->__sendPostRequest('https://srv.wego.com/v2/metasearch/flights/searches?currencyCode=KWD&locale=en', $searchRequestData);
            $this->resultCount += $newResponseContent->count;
            if ($this->count < 5) {
                $this->fetch();
                return;
            }
            $fares = collect($newResponseContent->fares);
            $fares = $fares->sortBy(function ($fare) {
                return $fare->price->amount;
            });
            $cheapestFare = $fares->first();
            $tripId = $cheapestFare->tripId;

            $newUrl = "https://srv.wego.com/v2/metasearch/flights/trips/{$tripId}?currencyCode=KWD&locale=en&isShamsi=false&paymentMethodIds[]=97";
            $finalResponseContent = json_encode($this->__sendGetRequest($newUrl));

            $this->setContent($finalResponseContent);
            $this->setStatus(200);
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

    private function __sendGetRequest($url)
    {
        $response = Curl::to($url)
            ->asJson()
            ->setCookieFile(storage_path('cookie/wego'))
            ->setCookieJar(storage_path('cookie/wego'))
            ->returnResponseObject()
            ->get();
        if ($response->status === 200) {
            return $response->content;
        }
    }

    private function __sendPostRequest($url, $data = null)
    {
        $response = Curl::to($url)
            ->withData($data)
            ->asJson()
            ->setCookieFile(storage_path('cookie/wego'))
            ->setCookieJar(storage_path('cookie/wego'))
            ->returnResponseObject()
            ->post();
        if ($response->status === 200) {
            return $response->content;
        }
    }

    private function __sendOptionsRequest($url)
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "OPTIONS");

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_VERBOSE, true);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_HEADER, true);

        $buffer = curl_exec($ch);
        curl_close($ch);

        unset($ch);
        return $buffer;
    }
}