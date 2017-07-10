<?php
/**
 * Created by PhpStorm.
 * User: ivan.li
 * Date: 10/07/2017
 * Time: 9:56 AM
 */

namespace IvanCLI\Crawler\Repositories\WESTELM;


use IvanCLI\Crawler\Repositories\DefaultCrawler;
use Ixudra\Curl\Facades\Curl;

class APICrawler extends DefaultCrawler
{
    const ITEM_API_URL = 'http://www.westelm.com.au/api/items?fieldset=details&language=en&country=US&currency=AUD&pricelevel=5&custitem_is_preview_item=F&url=';
    const PRODUCT_API_URL = 'http://www.westelm.com.au/CustomWE/services/shopping/skuSelection.ss?groupId=';
    protected $itemInfo;
    protected $groupId;
    protected $products;

    public function getItem()
    {
        $path = array_get(parse_url($this->url), 'path');
        $path = str_replace_first('/', '', $path);

        $url = self::ITEM_API_URL . $path;

        $response = Curl::to($url)
            ->withHeaders($this->headers)
            ->returnResponseObject()
            ->withOption("FOLLOWLOCATION", true)
            ->get();
        if ($response->status == 200) {
            $content = $response->content;
            $itemInfo = $this->__decodeJsonObject($content);
            if ($itemInfo !== false) {
                $this->itemInfo = $itemInfo;
            }
        } else {
            $this->setStatus($response->status);
        }
    }

    public function getProducts()
    {
        if (!is_null($this->itemInfo)) {
            $item = array_first($this->itemInfo->items);
            if (!is_null($item)) {
                $this->groupId = $item->internalid;

                $url = self::PRODUCT_API_URL . $this->groupId;

                $response = Curl::to($url)
                    ->withHeaders($this->headers)
                    ->returnResponseObject()
                    ->withOption("FOLLOWLOCATION", true)
                    ->get();

                if ($response->status == 200) {
                    $content = $response->content;
                    $this->setContent($content);
                    $this->setStatus($response->status);
                }
            }
        }
    }

    public function fetch()
    {
        $this->getItem();
        $this->getProducts();
    }

    private function __decodeJsonObject($content)
    {
        $content = json_decode($content);
        if (!is_null($content) && json_last_error() === JSON_ERROR_NONE) {
            return $content;
        }
        return false;
    }
}