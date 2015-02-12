<?php

use Behat\Behat\Context\BehatContext;

use mageekguy\atoum\asserter;
use Guzzle\Http\Client as HttpClient;
use Behat\Gherkin\Node\PyStringNode;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\PhpExecutableFinder;

use Rezzza\JsonApiBehatExtension\RestApiContext;
use Rezzza\JsonApiBehatExtension\Json\JsonContext;
use Rezzza\JsonApiBehatExtension\Json\JsonInspector;

/**
 * Test workflow totally copied from https://github.com/Behat/WebApiExtension/blob/master/features/bootstrap/FeatureContext.php
 */
class FeatureContext extends BehatContext
{
    private $phpBin;

    private $process;

    private $workingDir;

    private $asserter;

    public function __construct()
    {
        $this->asserter = new asserter\generator();
        $httpClient = new HttpClient('http://localhost:8888');
        $jsonInspector = new \Rezzza\JsonApiBehatExtension\Json\JsonInspector('javascript');

        $this->useContext(
            'rest_api',
            new RestApiContext($httpClient, $this->asserter, true)
        );
        $this->useContext(
            'json',
            new JsonContext($jsonInspector, $this->asserter, __DIR__)
        );
    }

    /**
     * @BeforeSuite
     * @AfterSuite
     */
    public static function cleanTestFolders()
    {
        $dir = self::workingDir();

        if (is_dir($dir)) {
            self::clearDirectory($dir);
        }
    }

    /**
     * @BeforeScenario
     */
    public function prepareScenario()
    {
        $dir = self::workingDir() . DIRECTORY_SEPARATOR . md5(microtime() * rand(0, 10000));
        mkdir($dir . '/features/bootstrap', 0777, true);

        $phpFinder = new PhpExecutableFinder();

        if (false === $php = $phpFinder->find()) {
            throw new \RuntimeException('Unable to find the PHP executable.');
        }

        $this->workingDir = $dir;
        $this->phpBin = $php;
        $this->process = new Process(null);
    }

    /**
     * @Given /^a file named "(?P<filename>[^"]*)" with:$/
     */
    public function aFileNamedWith($filename, PyStringNode $fileContent)
    {
        $content = strtr((string) $fileContent, array("'''" => '"""'));
        $this->createFile($this->workingDir . '/' . $filename, $content);
    }

    /**
     * @When /^I run behat "(?P<arguments>[^"]*)"$/
     */
    public function iRunBehat($arguments)
    {
        $argumentsString = strtr($arguments, array('\'' => '"'));
        $this->process->setWorkingDirectory($this->workingDir);
        $this->process->setCommandLine(sprintf(
            '%s %s %s %s',
            $this->phpBin,
            escapeshellarg(BEHAT_BIN_PATH),
            $argumentsString,
            strtr('--no-ansi', array('\'' => '"', '"' => '\"'))
        ));
        $this->process->start();
        $this->process->wait();
    }

    /**
     * @Then /^it should (fail|pass) with:$/
     */
    public function itShouldTerminateWithStatusAndContent($exitStatus, PyStringNode $string)
    {
        if ('fail' === $exitStatus) {
            $this->asserter->variable($this->getExitCode())->isEqualTo(1);
        } elseif ('success' === $exitStatus) {
            $this->asserter->variable($this->getExitCode())->isEqualTo(0);
        } else {
            throw new \LogicException('Accepts only "fail" or "pass"');
        }

        $this->asserter->string($this->getOutput())->contains((string) $string);
    }

    private function getExitCode()
    {
        return $this->process->getExitCode();
    }

    private function getOutput()
    {
        $output = $this->process->getErrorOutput() . $this->process->getOutput();

        // Normalize the line endings in the output
        if ("\n" !== PHP_EOL) {
            $output = str_replace(PHP_EOL, "\n", $output);
        }

        return trim(preg_replace("/ +$/m", '', $output));
    }

    private function createFile($filename, $content)
    {
        $path = dirname($filename);

        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }

        file_put_contents($filename, $content);
    }

    public static function workingDir()
    {
        return sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'json-api-behat';
    }

    private static function clearDirectory($path)
    {
        $files = scandir($path);
        array_shift($files);
        array_shift($files);

        foreach ($files as $file) {
            $file = $path . DIRECTORY_SEPARATOR . $file;
            if (is_dir($file)) {
                self::clearDirectory($file);
            } else {
                unlink($file);
            }
        }

        rmdir($path);
    }
}
