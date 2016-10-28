<?php

namespace Rezzza\RestApiBehatExtension\Rest;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\RequestInterface;

class HttpExchangeFormatter
{
    private $request;

    private $response;

    public function __construct(RequestInterface $request, ResponseInterface $response)
    {
        $this->request = $request;
        $this->response = $response;
    }

    public function formatRequest()
    {
        return sprintf(
            "%s %s :\n%s%s\n",
            $this->request->getMethod(),
            $this->request->getUri(),
            $this->getRawHeaders($this->request->getHeaders()),
            $this->request->getBody()
        );
    }

    public function formatFullExchange()
    {
        return sprintf(
            "%s %s :\n%s %s\n%s%s\n",
            $this->request->getMethod(),
            $this->request->getUri()->__toString(),
            $this->response->getStatusCode(),
            $this->response->getReasonPhrase(),
            $this->getRawHeaders($this->response->getHeaders()),
            $this->response->getBody()
        );
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
}
