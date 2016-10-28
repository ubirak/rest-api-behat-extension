<?php

namespace Rezzza\RestApiBehatExtension\Tests\Units\Rest;

use atoum;

class WrongResponseExpectation extends atoum
{
    public function test_it_display_pretty_response_when_cast_to_string()
    {
        $this
            ->given(
                $uri = new \mock\Psr\Http\Message\UriInterface,
                $this->calling($uri)->__toString = 'http://test.com/foo',
                $request = new \mock\Psr\Http\Message\RequestInterface,
                $this->calling($request)->getMethod = 'GET',
                $this->calling($request)->getUri = $uri,
                $response = new \mock\Psr\Http\Message\ResponseInterface,
                $this->calling($response)->getStatusCode = 200,
                $this->calling($response)->getReasonPhrase = 'OK',
                $this->calling($response)->getHeaders = ['Content-Type' => 'application/json'],
                $this->calling($response)->getBody = '{"status":"ok"}',
                $this->newTestedInstance('Error', $request, $response)
            )
            ->when(
                $result = $this->testedInstance->__toString()
            )
            ->then
                ->string($result)
                    ->contains(<<<EOF
|  GET http://test.com/foo :
|  200 OK
|  Content-Type: application/json
|  
|  {"status":"ok"}
EOF
                    )
        ;
    }
}
