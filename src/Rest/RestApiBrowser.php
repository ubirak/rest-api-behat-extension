<?php

namespace Rezzza\RestApiBehatExtension\Rest;

use Http\Client\HttpClient;
use Http\Discovery\HttpClientDiscovery;
use Http\Discovery\MessageFactoryDiscovery;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Tolerance\Operation\Callback;
use Tolerance\Operation\Runner\RetryOperationRunner;
use Tolerance\Operation\Runner\CallbackOperationRunner;
use Tolerance\Waiter\SleepWaiter;
use Rezzza\RestApiBehatExtension\Tolerance\ExecutionTimeLimited;

class RestApiBrowser
{
    /** @var HttpClient */
    private $httpClient;

    /** @var RequestInterface */
    private $request;

    /** @var ResponseInterface */
    private $response;

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

    /**
     * @param string $method
     * @param string $uri
     * @param string|array $body
     * @param array $headers
     */
    public function sendRequest($method, $uri, $body = null, array $headers = [])
    {
        if (false === $this->hasHost($uri)) {
            $uri = rtrim($this->host, '/').'/'.ltrim($uri, '/');
        }

        $this->request = $this->messageFactory->createRequest($method, $uri, $headers, $body);
        $this->response = $this->httpClient->sendRequest($this->request);

        if (null !== $this->responseStorage) {
            $this->responseStorage->writeRawContent((string) $this->response->getBody());
        }
    }

    public function sendRequestUntil($method, $uri, $body, array $headers, callable $assertion, $maxExecutionTime = 10)
    {
        $runner = new RetryOperationRunner(
            new CallbackOperationRunner(),
            new ExecutionTimeLimited(new SleepWaiter(), $maxExecutionTime)
        );
        $restApiBrowser = $this;
        $runner->run(new Callback(function () use ($restApiBrowser, $method, $uri, $body, $assertion, $headers) {
            $restApiBrowser->sendRequest($method, $uri, $body, $headers);

            return $assertion();
        }));
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
