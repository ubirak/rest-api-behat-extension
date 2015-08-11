<?php

namespace Rezzza\RestApiBehatExtension\Rest;

use Guzzle\Http\Exception\BadResponseException;
use Guzzle\Http\ClientInterface as HttpClient;

class RestApiBrowser
{
    /** @var HttpClient */
    private $httpClient;

    /** @var array|\Guzzle\Http\Message\RequestInterface */
    private $request;

    /** @var \Guzzle\Http\Message\Response|array */
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
     * @param array $options
     */
    public function sendRequest($method, $url, $body = null, array $options = array())
    {
        $this->createRequest($method, $url, $body, $options);

        try {
            $this->response = $this->httpClient->send($this->request);
        } catch (BadResponseException $e) {
            $this->response = $e->getResponse();

            if (null === $this->response) {
                throw $e;
            }
        }

        if (null !== $this->responseStorage) {
            $this->responseStorage->writeRawContent($this->response->getBody(true));
        }
    }

    /**
     * @param string $name
     * @param string $value
     */
    public function addRequestHeader($name, $value)
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
     * @param array                 $options
     */
    private function createRequest($method, $uri, $body = null, array $options = array())
    {
        if (!$this->hasHost($uri)) {
            $uri = rtrim($this->httpClient->getBaseUrl(), '/') . '/' . ltrim($uri, '/');
        }

        $this->request = $this->httpClient->createRequest($method, $uri, $this->requestHeaders, $body, $options);
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
