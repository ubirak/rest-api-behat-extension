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
     */
    public function iSendARequestWithBody($method, $url, PyStringNode $body)
    {
        $this->restApiBrowser->sendRequest($method, $url, (string) $body);
    }

    /**
     * Sends HTTP request to specific URL with POST parameters.
     *
     * @When I send a :method request to :url with form data:
     */
    public function iSendAPostRequestToWithFormData($method, $url, TableNode $formData)
    {
        $this->restApiBrowser->sendRequest($method, $url, $formData->getRowsHash());
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
     * @Then print request and response
     */
    public function printRequestAndResponse()
    {
        echo "REQUEST:\n";
        $this->printRequest();
        echo "\nRESPONSE:\n";
        $this->printResponse();
    }

    /**
     * @Then print request
     */
    public function printRequest()
    {
        $request = $this->getRequest();
        echo sprintf(
            "%s %s :\n%s%s\n",
            $request->getMethod(),
            $request->getUri(),
            $this->getRawHeaders($request->getHeaders()),
            $request->getBody()
        );
    }

    /**
     * @return RequestInterface
     */
    private function getRequest()
    {
        return $this->restApiBrowser->getRequest();
    }

    /**
     * @param array $headers
     * @return string
     */
    private function getRawHeaders(array $headers)
    {
        $rawHeaders = '';
        foreach ($headers as $key => $value) {
            $rawHeaders .= sprintf("%s: %s\n", $key, is_array($value) ? implode(", ", $value) : $value);

        }
        $rawHeaders .= "\n";
        return $rawHeaders;
    }

    /**
     * @Then print response
     */
    public function printResponse()
    {
        $request = $this->getRequest();
        $response = $this->getResponse();

        echo sprintf(
            "%s %s :\n%s %s\n%s%s\n",
            $request->getMethod(),
            $request->getUri()->__toString(),
            $response->getStatusCode(),
            $response->getReasonPhrase(),
            $this->getRawHeaders($response->getHeaders()),
            $response->getBody()
        );
    }
}
