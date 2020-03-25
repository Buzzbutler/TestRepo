<?php


namespace App\Tests\Feature;

use Liip\FunctionalTestBundle\Test\WebTestCase as LiipWebTestCase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Throwable;

class WebTestCase extends LiipWebTestCase
{
    protected static $logDir;


    public static function setUpBeforeClass(): void
    {
        // Create directory where the failing tests write their information

        self::$logDir = "/var/www/site6.loc/var/tests";
        if (!is_dir(self::$logDir)) {
            mkdir(self::$logDir, 0777, true);
        }
    }


    public function setUp(): void
    {
        $this->client = null;
    }


    // This has to be called by the tests to get and store the client

    protected function getMyClient(): KernelBrowser
    {
        $this->client = $this->makeAuthenticatedClient();


        return $this->client;
    }


    protected function onNotSuccessfulTest(Throwable $e): void
    {
        if ($this->client) {
            $this->writeErrorFile($e->getMessage(), $e->getTraceAsString());
        }

        parent::onNotSuccessfulTest($e);
    }


    protected function writeErrorFile(string $errorMessage, string $trace): void
    {
        $testClass = get_class($this);
        $testName = $this->getName();
        $response = $this->client->getResponse()->getContent();

        // Generate a file name containing the test file name and the test name, e.g. App_Tests_Controller_MyControllerTest___testDefault.html
//        $fileName = str_replace('\\', '_', "$testClass"."___$testName.html");
        $fileName = 'log_trace.html';
        $content = "<html>$response <pre>Error message: $errorMessage\nFailing test: $testClass::$testName\nStacktrace:\n$trace</pre></html>";
        file_put_contents(self::$logDir."//$fileName", $content);
    }
}