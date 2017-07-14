<?php
/**
 * Created by PhpStorm.
 * User: ivan.li
 * Date: 14/07/2017
 * Time: 2:39 PM
 */

namespace IvanCLI\Crawler\Repositories;


use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use IvanCLI\Crawler\Contracts\CrawlerContract;

/**
 * To install selenium, the easiest way will be using Laravel Selenium package "modelizer/selenium": "^1.2"
 *
 * Running the following code to install Java
 * # sudo apt-get install -y xorg xvfb firefox dbus-x11 xfonts-100dpi xfonts-75dpi xfonts-cyrillic
 *
 *
 * Running the following code to install and start firefox and headless driver
 * # sudo apt-get install firefox xvfb
 * # Xvfb :10 -ac &
 * # export DISPLAY=:10
 * # firefox
 *
 *
 * if firefox doesn't work, try uninstall it and install a very specific version
 * # wget https://ftp.mozilla.org/pub/firefox/releases/46.0.1/linux-x86_64/en-US/firefox-46.0.1.tar.bz2
 * # tar -vxjf firefox-46.0.1.tar.bz2
 * # ln -s /var/www/html/firefox/firefox-bin /usr/bin/firefox
 *
 *
 *
 *
 *
 * Running the following artisan command will automatically download and start selenium
 * # php artisan selenium:start
 *
 *
 * Class SeleniumCrawler
 * @package IvanCLI\Crawler\Repositories
 */
class SeleniumCrawler implements CrawlerContract
{
    const SELENIUM_HOST = 'http://localhost:4444/wd/hub';
    const SELENIUM_DRVIER = 'firefox';

    protected $driver;

    protected $url;
    protected $content = null;
    protected $status = null;

    public function __construct()
    {
        $this->__initiate();
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
     */
    public function fetch()
    {
        if (!is_null($this->driver) && !is_null($this->url)) {
            $this->driver->get($this->url);
            $this->driver->wait(3);
            $content = $this->driver->getPageSource();
            if (!is_null($content) && !empty($content)) {
                /*
                 * unfortunately there is no way to get response code from Selenium Webdriver at the moment
                 * hence, hard-coding 200 to bypass validation
                 */
                $this->setStatus(200);
                $this->setContent($content);
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

    private function __initiate()
    {
        $this->driver = RemoteWebDriver::create(self::SELENIUM_HOST, DesiredCapabilities::firefox());
    }
}