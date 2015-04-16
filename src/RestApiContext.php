<?php

namespace Rezzza\JsonApiBehatExtension;

use mageekguy\atoum\asserter;
use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Gherkin\Node\PyStringNode;
use Guzzle\Http\Exception\BadResponseException;
use Guzzle\Http\ClientInterface as HttpClient;
use Rezzza\JsonApiBehatExtension\Rest\RestApiBrowser;

class RestApiContext implements Context, SnippetAcceptingContext
{
    private $asserter;

    private $restApiBrowser;

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
        $this->restApiBrowser->sendRequest($method, $url);
    }

    /**
     * Sends HTTP request to specific URL with raw body from PyString.
     *
     * @param string       $method request method
     * @param string       $url relative url
     * @param PyStringNode $body
     *
     * @When /^(?:I )?send a ([A-Z]+) request to "([^"]+)" with body:$/
     * @throws BadResponseException
     * @throws \Exception
     */
    public function iSendARequestWithBody($method, $url, PyStringNode $body)
    {
        $this->restApiBrowser->sendRequest($method, $url, (string) $body);
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
        $this->asserter->variable($actual)->isEqualTo($expected);
    }

    /**
     * @Given /^I set "([^"]*)" header equal to "([^"]*)"$/
     */
    public function iSetHeaderEqualTo($headerName, $headerValue)
    {
        $this->restApiBrowser->setRequestHeader($headerName, $headerValue);
    }

    /**
     * @Given /^I add "([^"]*)" header equal to "([^"]*)"$/
     */
    public function iAddHeaderEqualTo($headerName, $headerValue)
    {
        $this->restApiBrowser->addRequestHeader($headerName, $headerValue);
    }

    /**
     * Set login / password for next HTTP authentication
     *
     * @When /^I set basic authentication with "(?P<username>[^"]*)" and "(?P<password>[^"]*)"$/
     */
    public function iSetBasicAuthenticationWithAnd($username, $password)
    {
        $authorization = base64_encode($username . ':' . $password);
        $this->restApiBrowser->setRequestHeader('Authorization', 'Basic ' . $authorization);
    }

    /**
     * @Then print response
     */
    public function printResponse()
    {
        $request = $this->getRequest();
        $response = $this->getResponse();

        echo sprintf(
            "%s %s => %d:\n%s\n",
            $request->getMethod(),
            $request->getUrl(),
            $response->getStatusCode(),
            $response->getBody()
        );
    }

    /**
     * @return array|\Guzzle\Http\Message\RequestInterface
     */
    private function getRequest()
    {
        return $this->restApiBrowser->getRequest();
    }

    /**
     * @return array|\Guzzle\Http\Message\Response
     */
    private function getResponse()
    {
        return $this->restApiBrowser->getResponse();
    }
}
