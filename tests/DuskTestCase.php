<?php

namespace Tests;

use Appsolutely\AIO\Models\Administrator;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Laravel\Dusk\TestCase as BaseTestCase;
use Symfony\Component\Process\Process;

abstract class DuskTestCase extends BaseTestCase
{
    use BrowserExtension, CreatesApplication, InteractsWithDatabase;

    /**
     * @var Administrator
     */
    protected $user;

    /**
     * @var bool
     */
    protected $login = true;

    public function login(Browser $browser)
    {
        $browser->loginAs($this->getUser(), 'admin');
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->extendBrowser();

        $this->boot();
    }

    protected function tearDown(): void
    {
        $this->destory();

        parent::tearDown();
    }

    /**
     * Prepare for Dusk test execution.
     *
     * @beforeClass
     *
     * @return void
     */
    public static function prepare()
    {
        static::startChromeDriver();
    }

    /**
     * @param  RemoteWebDriver  $driver
     * @return \Laravel\Dusk\Browser
     */
    protected function newBrowser($driver)
    {
        $browser = (new Browser($driver))->resize(1566, 1080);

        $browser->resolver->prefix = 'html';

        if ($this->login) {
            $this->login($browser);
        }

        return $browser;
    }

    /**
     * Create the RemoteWebDriver instance.
     *
     * @return RemoteWebDriver
     */
    protected function driver()
    {
        $options = (new ChromeOptions())->addArguments([
            '--disable-gpu',
            '--headless',
            '--window-size=1920,1080',
        ]);

        return RemoteWebDriver::create(
            'http://localhost:9515', DesiredCapabilities::chrome()->setCapability(
                ChromeOptions::CAPABILITY_W3C, $options
            )
        );
    }

    /**
     * Build the process to run the Chromedriver.
     *
     * @return Process
     *
     * @throws \RuntimeException
     */
    protected static function buildChromeProcess(array $arguments = [])
    {
        return (new ChromeProcess(static::$chromeDriver))->toProcess($arguments);
    }
}
