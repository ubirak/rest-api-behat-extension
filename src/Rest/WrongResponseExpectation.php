<?php

namespace Rezzza\RestApiBehatExtension\Rest;

use Rezzza\RestApiBehatExtension\ExpectationFailed;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\RequestInterface;

class WrongResponseExpectation extends ExpectationFailed
{
    private $request;

    private $response;

    public function __construct($message, RequestInterface $request, ResponseInterface $response, $previous = null)
    {
        $this->request = $request;
        $this->response = $response;
        parent::__construct($message, 0, $previous);
    }

    public function getContextText()
    {
        $formatter = new HttpExchangeFormatter($this->request, $this->response);

        return $formatter->formatFullExchange();
    }
}
