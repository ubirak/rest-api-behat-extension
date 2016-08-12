<?php

namespace Rezzza\RestApiBehatExtension\Rest;

use Http\Client\HttpClient;
use Http\Discovery\HttpClientDiscovery;
use Http\Discovery\MessageFactoryDiscovery;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class RestApiBrowser
{
    /** @var HttpClient */
    private $httpClient;

    /** @var RequestInterface */
    private $request;

    /** @var ResponseInterface */
    private $response;

    /** @var array */
    private $requestHeaders = [];

    /** @var ResponseStorage */
    private $responseStorage;

    /** @var string */
    private $host;

    /** @var MessageFactoryDiscovery */
    private $messageFactory;

    /**
     * @param string $host
     */
    public function __construct($host, HttpClient $httpClient = null)
    {
        $this->host = $host;
        $this->httpClient = $httpClient ?: HttpClientDiscovery::find();
        $this->messageFactory = MessageFactoryDiscovery::find();
    }

    /**
     * Allow to override the httpClient to use yours with specific middleware for example
     */
    public function useHttpClient(HttpClient $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    /**
     * @param ResponseStorage $responseStorage
     */
    public function enableResponseStorage(ResponseStorage $responseStorage)
    {
        $this->responseStorage = $responseStorage;
    }

    /**
     * @return ResponseInterface
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @param ResponseInterface $response
     */
    public function setResponse(ResponseInterface $response)
    {
        $this->response = $response;
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
     * @param string $uri
     * @param string|array $body
     */
    public function sendRequest($method, $uri, $body = null)
    {
        if (false === $this->hasHost($uri)) {
            $uri = rtrim($this->host, '/').'/'.ltrim($uri, '/');
        }

        $this->request = $this->messageFactory->createRequest($method, $uri, $this->requestHeaders, $body);
        $this->response = $this->httpClient->sendRequest($this->request);

        if (null !== $this->responseStorage) {
            $this->responseStorage->writeRawContent((string) $this->response->getBody());
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
     * @param string $name
     * @param string $value
     */
    public function addRequestHeader($name, $value)
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
