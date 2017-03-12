<?php

/**
 * Created by PhpStorm.
 * User: Ivan
 * Date: 11/03/2017
 * Time: 2:53 PM
 */
namespace IvanCLI\Crawler;

use Illuminate\Support\ServiceProvider;

class CrawlerServiceProvider extends ServiceProvider
{

    protected $defer = false;

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerCrawler();
    }

    private function registerCrawler()
    {
        $this->app->bind('crawler', function ($app) {
            return new Crawler($app);
        });
    }
}