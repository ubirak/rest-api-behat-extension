<?php

namespace Rezzza\RestApiBehatExtension\Rest;

use Ivory\HttpAdapter\HttpAdapterFactory;
use Ivory\HttpAdapter\HttpAdapterInterface as HttpClient;
use Ivory\HttpAdapter\HttpAdapterException;
use Ivory\HttpAdapter\Message\Request;
use Zend\Diactoros\Stream;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Behat\Gherkin\Node\PyStringNode;

class RestApiBrowser
{
    /** @var HttpClient */
    private $httpClient;

    /** @var RequestInterface */
    private $request;

    /** @var ResponseInterface */
    private $response;

    /** @var array */
    private $requestHeaders = array();

    /** @var ResponseStorage */
    private $responseStorage;

    /**
     * @param string $base_url
     * @param string|null $adaptor_name
     * @throws HttpAdapterException
     */
    public function __construct($base_url, $adaptor_name, HttpClient $httpClient = null)
    {
        if (!is_null($httpClient) && $httpClient instanceof HttpClient) {
            $this->httpClient = $httpClient;
        } else {
            if (is_string($adaptor_name) && HttpAdapterFactory::capable($adaptor_name)) {
                $this->httpClient = HttpAdapterFactory::create($adaptor_name);
            } else {
                $this->httpClient = HttpAdapterFactory::guess();
            }
            $this->httpClient->getConfiguration()->setBaseUri($base_url);
        }
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
     * @return HttpClient
     */
    public function getHttpClient()
    {
        return $this->httpClient;
    }

    /**
     * @param string $method
     * @param string $url
     * @param string $body
     */
    public function sendRequest($method, $url, $body = null)
    {
        try {
            $this->send($method, $url, $body);
        } catch (HttpAdapterException $e) {
            if ($e->hasResponse()) {
                $this->response = $e->getResponse();
            }

            if (null === $this->response) {
                throw $e;
            }
        }

        if (null !== $this->responseStorage) {
            $this->responseStorage->writeRawContent($this->response->getBody()->getContents());
        }
    }

    /**
     * @param string $method
     * @param string $uri With or without host
     * @param string|resource|array $body
     */
    private function send($method, $uri, $body = null)
    {
        if (!$this->hasHost($uri)) {
            $uri = rtrim($this->httpClient->getConfiguration()->getBaseUri(), '/') . '/' . ltrim($uri, '/');
        }
        $stream = new Stream('php://memory', 'rw');
        if ($body) {
            $stream->write($body);
        }
        $this->request = new Request($uri, $method, $stream, $this->requestHeaders);
        $this->response = $this->httpClient->send($uri, $method, $this->requestHeaders, $body);
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

    /**
     * @param string $name
     * @param string $value
     */
    public function setRequestHeader($name, $value)
    {
        $this->removeRequestHeader($name);
        $this->addRequestHeader($name, $value);
    }

    private function removeRequestHeader($headerName)
    {
        if (array_key_exists($headerName, $this->requestHeaders)) {
            unset($this->requestHeaders[$headerName]);
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
}
