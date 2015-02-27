<?php

namespace Rezzza\JsonApiBehatExtension;

use mageekguy\atoum\asserter;
use Behat\Behat\Context\BehatContext;
use Behat\Gherkin\Node\PyStringNode;
use Guzzle\Http\Exception\BadResponseException;
use Guzzle\Http\Client as HttpClient;
use Rezzza\JsonApiBehatExtension\Json\JsonStorage;
use Rezzza\JsonApiBehatExtension\Json\JsonStorageAware;

class RestApiContext extends BehatContext implements JsonStorageAware
{
    private $asserter;

    private $httpClient;

    private $request;

    private $response;

    private $headers = array();

    private $enableJsonInspection = true;

    private $jsonStorage;

    public function __construct(HttpClient $httpClient, $asserter, $enableJsonInspection)
    {
        $this->headers = array();
        $this->httpClient = $httpClient;
        $this->asserter = $asserter;
        $this->enableJsonInspection = (bool) $enableJsonInspection;
    }

    public function setJsonStorage(JsonStorage $jsonStorage)
    {
        $this->jsonStorage = $jsonStorage;
    }

    /**
     * @param string $method request method
     * @param string $url    relative url
     *
     * @When /^(?:I )?send a ([A-Z]+) request to "([^"]+)"$/
     */
    public function iSendARequest($method, $url)
    {
        $this->sendRequest($method, $url);
    }

    /**
     * Sends HTTP request to specific URL with raw body from PyString.
     *
     * @param string $method request method
     * @param string $url relative url
     *
     * @When /^(?:I )?send a ([A-Z]+) request to "([^"]+)" with body:$/
     */
    public function iSendARequestWithBody($method, $url, PyStringNode $body)
    {
        $this->sendRequest($method, $url, $body);
    }

    /**
     * @param string $code status code
     *
     * @Then /^(?:the )?response status code should be (\d+)$/
     */
    public function theResponseCodeShouldBe($code)
    {
        $expected = intval($code);
        $actual = intval($this->response->getStatusCode());
        $this->asserter->variable($actual)->isEqualTo($expected);
    }

    /**
     * @Given /^I set "([^"]*)" header equal to "([^"]*)"$/
     */
    public function iSetHeaderEqualTo($headerName, $headerValue)
    {
        $this->setHeader($headerName, $headerValue);
    }

    /**
     * @Given /^I add "([^"]*)" header equal to "([^"]*)"$/
     */
    public function iAddHeaderEqualTo($headerName, $headerValue)
    {
        $this->addHeader($headerName, $headerValue);
    }

    /**
     * Set login / password for next HTTP authentication
     *
     * @When /^I set basic authentication with "(?P<username>[^"]*)" and "(?P<password>[^"]*)"$/
     */
    public function iSetBasicAuthenticationWithAnd($username, $password)
    {
        $this->removeHeader('Authorization');
        $authorization = base64_encode($username . ':' . $password);
        $this->addHeader('Authorization', 'Basic ' . $authorization);
    }

    /**
     * @Then print response
     */
    public function printResponse()
    {
        $request = $this->request;
        $response = $this->response;

        echo sprintf(
            "%s %s => %d:\n%s\n",
            $request->getMethod(),
            $request->getUrl(),
            $response->getStatusCode(),
            $response->getBody()
        );
    }

    /**
     * @param string $name
     * @param string $value
     */
    protected function addHeader($name, $value)
    {
        if (isset($this->headers[$name])) {
            if (!is_array($this->headers[$name])) {
                $this->headers[$name] = array($this->headers[$name]);
            }
            $this->headers[$name][] = $value;
        } else {
            $this->headers[$name] = $value;
        }
    }

    /**
     * @param string $name
     * @param string $value
     */
    protected function setHeader($name, $value)
    {
        if (isset($this->headers[$name])) {
            unset($this->headers[$name]);
        }

        $this->addHeader($name, $value);
    }

    /**
     * @param string $method
     * @param string $url
     * @param PyStringNode $body
     */
    private function sendRequest($method, $url, $body = null)
    {
        $this->createRequest($method, $url, $body);

        try {
            $this->response = $this->httpClient->send($this->request);
        } catch (BadResponseException $e) {
            $this->response = $e->getResponse();

            if (null === $this->response) {
                throw $e;
            }
        }

        if (null !== $this->jsonStorage && $this->enableJsonInspection) {
            $this->jsonStorage->writeRawContent($this->response->getBody(true));
        }
    }

    private function createRequest($method, $url, $body = null)
    {
        $this->request = $this->httpClient->createRequest($method, $url, $this->headers, $body);
        // Reset headers used for the HTTP request
        $this->headers = array();
    }

    /**
     * @param string $headerName
     */
    protected function removeHeader($headerName)
    {
        if (array_key_exists($headerName, $this->headers)) {
            unset($this->headers[$headerName]);
        }
    }
}
