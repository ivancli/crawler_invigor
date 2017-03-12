<?php

/**
 * Created by PhpStorm.
 * User: Ivan
 * Date: 11/03/2017
 * Time: 2:57 PM
 */
namespace IvanCLI\Crawler;

use Illuminate\Support\Facades\Facade;

class CrawlerFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'crawler';
    }
}