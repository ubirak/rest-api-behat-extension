<?php

namespace Rezzza\RestApiBehatExtension;

use Behat\Behat\Context\BehatContext;
use Behat\Gherkin\Node\PyStringNode;
use Guzzle\Http\Client as HttpClient;
use Guzzle\Http\Exception\BadResponseException;
use Rezzza\RestApiBehatExtension\Response\ResponseStorage;
use Rezzza\RestApiBehatExtension\Response\ResponseStorageAware;
use mageekguy\atoum\asserter;

class RestApiContext extends BehatContext implements ResponseStorageAware
{
    /** @var \mageekguy\atoum */
    private $asserter;

    /** @var HttpClient */
    private $httpClient;

    /** @var array|\Guzzle\Http\Message\RequestInterface */
    private $request;

    /** @var \Guzzle\Http\Message\Response|array */
    private $response;

    /** @var array */
    private $requestHeaders = array();

    /** @var bool */
    private $enableResponseInspection = true;

    /** @var ResponseStorage */
    private $responseStorage;

    /** @var bool */
    private $enableFollowRedirects = true;

    public function __construct(HttpClient $httpClient, $asserter, $enableResponseInspection)
    {
        $this->requestHeaders = array();
        $this->httpClient = $httpClient;
        $this->asserter = $asserter;
        $this->enableResponseInspection = (bool) $enableResponseInspection;
    }

    /**
     * {@inheritdoc}
     */
    public function setResponseStorage(ResponseStorage $responseStorage)
    {
        $this->responseStorage = $responseStorage;
    }

    /**
     * @Given /^(?:I )?won't follow next redirect$/
     */
    public function iWontFollowNextRedirect()
    {
        $this->enableFollowRedirects = false;
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
     * @param string $headerKey
     * @param string $expectedValue
     *
     * @Then /^(?:the )?response header "(?P<headerKey>[^"]*)" should have value "(?P<expectedValue>[^"]*)"$/
     */
    public function theResponseHeaderShouldHave($headerKey, $expectedValue)
    {
        $hasValue = $this->response->getHeader($headerKey)->hasValue($expectedValue);
        $this->asserter->boolean($hasValue)
            ->isTrue(
                sprintf(
                    'Header "%s" is not containing in last response:
"%s"
Received:
"%s"',
                    $headerKey,
                    $expectedValue,
                    implode(', ', $this->response->getHeader($headerKey)->toArray())
                )
            );
    }

    /**
     * @Given /^I set "([^"]*)" header equal to "([^"]*)"$/
     */
    public function iSetHeaderEqualTo($headerName, $headerValue)
    {
        $this->setRequestHeader($headerName, $headerValue);
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
        $this->removeRequestHeader('Authorization');
        $authorization = base64_encode($username . ':' . $password);
        $this->addRequestHeader('Authorization', 'Basic ' . $authorization);
    }

    /**
     * @Then print response
     */
    public function printResponse()
    {
        $request = $this->request;
        $response = $this->response;

        echo sprintf(
            "%s %s :\n%s%s\n",
            $request->getMethod(),
            $request->getUrl(),
            $response->getRawHeaders(),
            $response->getBody()
        );
    }

    /**
     * @return array|\Guzzle\Http\Message\RequestInterface
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @return array|\Guzzle\Http\Message\Response
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @return array
     * @deprecated BC Alias, prefer using getRequestHeaders()
     */
    public function getHeaders()
    {
        return $this->getRequestHeaders();
    }

    /**
     * @return array
     */
    public function getRequestHeaders()
    {
        return $this->requestHeaders;
    }

    /**
     * @param string $name
     * @param string $value
     */
    protected function addRequestHeader($name, $value)
    {
        if (isset($this->requestHeaders[$name])) {
            if (!is_array($this->requestHeaders[$name])) {
                $this->requestHeaders[$name] = array($this->requestHeaders[$name]);
            }
            $this->requestHeaders[$name][] = $value;
        } else {
            $this->requestHeaders[$name] = $value;
        }
    }

    /**
     * @param string $headerName
     */
    protected function removeRequestHeader($headerName)
    {
        if (array_key_exists($headerName, $this->requestHeaders)) {
            unset($this->requestHeaders[$headerName]);
        }
    }

    /**
     * @param string $name
     * @param string $value
     */
    protected function setRequestHeader($name, $value)
    {
        $this->removeRequestHeader($name);
        $this->addRequestHeader($name, $value);
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

        if (null !== $this->responseStorage && $this->enableResponseInspection) {
            $this->responseStorage->writeRawContent($this->response->getBody(true));
        }
    }

    /**
     * @param string                $method
     * @param string                $uri    With or without host
     * @param string|resource|array $body
     */
    private function createRequest($method, $uri, $body = null)
    {
        if (!$this->hasHost($uri)) {
            $uri = rtrim($this->httpClient->getBaseUrl(), '/') . '/' . ltrim($uri, '/');
        }

        $options = [];
        if (false === $this->enableFollowRedirects) {
            $options['allow_redirects'] = false;
            $this->enableFollowRedirects = true;
        }

        $this->request = $this->httpClient->createRequest($method, $uri, $this->requestHeaders, $body, $options);
        // Reset headers used for the HTTP request
        $this->requestHeaders = array();
    }

    /**
     * @param string $uri
     *
     * @return bool
     */
    private function hasHost($uri)
    {
        return strpos($uri, '://') !== false;
    }
}
