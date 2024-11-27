<?php

namespace Ubirak\RestApiBehatExtension\Rest;

use Http\Discovery\Psr17Factory;
use Http\Discovery\Psr18ClientDiscovery;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Tolerance\Operation\Callback;
use Tolerance\Operation\Runner\RetryOperationRunner;
use Tolerance\Operation\Runner\CallbackOperationRunner;
use Tolerance\Waiter\SleepWaiter;
use Tolerance\Waiter\TimeOut;

class RestApiBrowser
{
    /** @var ClientInterface */
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

    /** @var RequestFactoryInterface */
    private $messageFactory;

    /**
     * @param string $host
     */
    public function __construct($host, ClientInterface $httpClient = null)
    {
        $this->host = $host;
        $this->httpClient = $httpClient ?: Psr18ClientDiscovery::find();
        $this->messageFactory = new Psr17Factory();
    }

    /**
     * Allow to override the httpClient to use yours with specific middleware for example.
     */
    public function useHttpClient(ClientInterface $httpClient)
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
     * @param string       $method
     * @param string       $uri
     * @param string|array $body
     */
    public function sendRequest($method, $uri, $body = null)
    {
        if (false === $this->hasHost($uri)) {
            $uri = rtrim($this->host, '/').'/'.ltrim($uri, '/');
        }

        if (is_array($body)) {
            $html = new \Ubirak\RestApiBehatExtension\Html\Form($body);
            $body = $html->getBody();
            $this->setRequestHeader('Content-Type', $html->getContentTypeHeaderValue());
        }

        $this->request = $this->messageFactory->createRequest($method, $uri);
        foreach ($this->requestHeaders as $keyHeader => $valueHeader) {
            $this->request = $this->request->withHeader($keyHeader, $valueHeader);
        }
        if (null !== $body) {
            $this->request = $this->request->withBody($this->messageFactory->createStream($body));
        }

        $this->response = $this->httpClient->sendRequest($this->request);
        $this->requestHeaders = [];

        if (null !== $this->responseStorage) {
            $this->responseStorage->writeRawContent((string) $this->response->getBody());
        }
    }

    public function sendRequestUntil($method, $uri, $body, callable $assertion, $maxExecutionTime = 10)
    {
        $runner = new RetryOperationRunner(
            new CallbackOperationRunner(),
            new TimeOut(new SleepWaiter(), $maxExecutionTime)
        );
        $restApiBrowser = $this;
        $runner->run(new Callback(function () use ($restApiBrowser, $method, $uri, $body, $assertion) {
            $restApiBrowser->sendRequest($method, $uri, $body);

            return $assertion();
        }));
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
        $name = strtolower($name);
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
        $headerName = strtolower($headerName);
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
        return false !== strpos($uri, '://');
    }
}
