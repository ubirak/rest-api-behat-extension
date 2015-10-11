<?php

namespace Rezzza\RestApiBehatExtension\Rest;

use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\ClientInterface as HttpClient;
use GuzzleHttp\Psr7\Request;
use Behat\Gherkin\Node\PyStringNode;


class RestApiBrowser
{
    /** @var HttpClient */
    private $httpClient;

    /** @var array|\Psr\Http\Message\RequestInterface */
    private $request;

    /** @var \GuzzleHttp\Psr7\Response|array */
    private $response;

    /** @var array */
    private $requestHeaders = array();

    /** @var ResponseStorage */
    private $responseStorage;

    public function __construct(HttpClient $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    public function enableResponseStorage(ResponseStorage $responseStorage)
    {
        $this->responseStorage = $responseStorage;
    }

    public function getResponse()
    {
        return $this->response;
    }

    public function getRequest()
    {
        return $this->request;
    }

    public function getRequestHeaders()
    {
        return $this->requestHeaders;
    }

    /**
     * @param string $method
     * @param string $url
     * @param PyStringNode $body
     */
    public function sendRequest($method, $url, $body = null)
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

        if (null !== $this->responseStorage) {
            $this->responseStorage->writeRawContent($this->response->getBody()->getContents());
        }
    }

    /**
     * @param string $name
     * @param string $value
     */
    public function addRequestHeader($name, $value)
    {
        if (isset($this->requestHeaders[$name])) {
            $this->requestHeaders[$name] = implode(", ",array($this->requestHeaders[$name], $value));
        } else {
            $this->requestHeaders[$name] = $value;
        }
    }

    /**
     * @param string $name
     * @param string $value
     */
    public function setRequestHeader($name, $value)
    {
        $this->removeRequestHeader($name);
        $this->addRequestHeader($name, $value);
    }

    /**
     * @param string                $method
     * @param string                $uri With or without host
     * @param string|resource|array $body
     */
    private function createRequest($method, $uri, $body = null)
    {
        if (!$this->hasHost($uri)) {
            $uri = rtrim($this->httpClient->getConfig("base_uri"), '/') . '/' . ltrim($uri, '/');
        }
        if(is_null($body))
            $this->request = new Request($method, $uri, $this->requestHeaders);
        else {
            $this->request = new Request($method, $uri, $this->requestHeaders, $body);
        }

        // Reset headers used for the HTTP request
        $this->requestHeaders = array();
    }

    private function removeRequestHeader($headerName)
    {
        if (array_key_exists($headerName, $this->requestHeaders)) {
            unset($this->requestHeaders[$headerName]);
        }
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
