<?php

namespace Rezzza\RestApiBehatExtension;

use mageekguy\atoum\asserter;
use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Rezzza\RestApiBehatExtension\Rest\RestApiBrowser;

class RestApiContext implements Context, SnippetAcceptingContext
{
    private $asserter;

    private $restApiBrowser;

    private $requestHeaders = [];

    public function __construct(RestApiBrowser $restApiBrowser)
    {
        $this->restApiBrowser = $restApiBrowser;
        $this->asserter = new asserter\generator;
    }

    /**
     * @param string $method request method
     * @param string $url    relative url
     *
     * @When /^(?:I )?send a ([A-Z]+) request to "([^"]+)"$/
     */
    public function iSendARequest($method, $url)
    {
        $this->restApiBrowser->sendRequest($method, $url, null, $this->requestHeaders);
    }

    /**
     * Sends HTTP request to specific URL with raw body from PyString.
     *
     * @param string       $method request method
     * @param string       $url relative url
     * @param PyStringNode $body
     *
     * @When /^(?:I )?send a ([A-Z]+) request to "([^"]+)" with body:$/
     */
    public function iSendARequestWithBody($method, $url, PyStringNode $body)
    {
        $this->restApiBrowser->sendRequest($method, $url, (string) $body, $this->requestHeaders);
    }

    /**
     * @param string $code status code
     *
     * @Then /^(?:the )?response status code should be (\d+)$/
     */
    public function theResponseCodeShouldBe($code)
    {
        $expected = intval($code);
        $actual = intval($this->getResponse()->getStatusCode());
        try {
            $this->asserter->variable($actual)->isEqualTo($expected);
        } catch (\Exception $e) {
            throw new Rest\WrongResponseExpectation($e->getMessage(), $this->restApiBrowser->getRequest(), $this->getResponse(), $e);
        }
    }

    /**
     * @return ResponseInterface
     */
    private function getResponse()
    {
        return $this->restApiBrowser->getResponse();
    }

    /**
     * @Given /^I set "([^"]*)" header equal to "([^"]*)"$/
     */
    public function iSetHeaderEqualTo($headerName, $headerValue)
    {
        $this->removeRequestHeader($headerName);
        $this->addRequestHeader($headerName, $headerValue);
    }

    /**
     * @Given /^I add "([^"]*)" header equal to "([^"]*)"$/
     */
    public function iAddHeaderEqualTo($headerName, $headerValue)
    {
        $this->addRequestHeader($headerName, $headerValue);
    }

    /**
     * Set login / password for next HTTP authentication
     *
     * @When /^I set basic authentication with "(?P<username>[^"]*)" and "(?P<password>[^"]*)"$/
     */
    public function iSetBasicAuthenticationWithAnd($username, $password)
    {
        $authorization = base64_encode($username . ':' . $password);
        $this->setRequestHeader('Authorization', 'Basic ' . $authorization);
    }

    /**
     * @Then print request and response
     */
    public function printRequestAndResponse()
    {
        $formatter = $this->buildHttpExchangeFormatter();
        echo "REQUEST:\n";
        echo $formatter->formatRequest();
        echo "\nRESPONSE:\n";
        echo $formatter->formatFullExchange();
    }

    /**
     * @Then print request
     */
    public function printRequest()
    {
        echo $this->buildHttpExchangeFormatter()->formatRequest();
    }

    /**
     * @Then print response
     */
    public function printResponse()
    {
        echo $this->buildHttpExchangeFormatter()->formatFullExchange();
    }

    private function buildHttpExchangeFormatter()
    {
        return new Rest\HttpExchangeFormatter($this->restApiBrowser->getRequest(), $this->getResponse());
    }

    /**
     * @param string $name
     * @param string $value
     */
    private function addRequestHeader($name, $value)
    {
        if (isset($this->requestHeaders[$name])) {
            $this->requestHeaders[$name] .= ', '.$value;
        } else {
            $this->requestHeaders[$name] = $value;
        }
    }

    /**
     * @param string $headerName
     */
    private function removeRequestHeader($headerName)
    {
        if (array_key_exists($headerName, $this->requestHeaders)) {
            unset($this->requestHeaders[$headerName]);
        }
    }
}
