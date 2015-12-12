<?php

namespace Rezzza\RestApiBehatExtension\Tests\Units\Rest;

use atoum;
use Rezzza\RestApiBehatExtension\Rest\RestApiBrowser as SUT;

/**
 * @author Mikaël FIMA <mika@verylastroom.com>
 * @author Guillaume MOREL <guillaume.morel@verylastroom.com>
 */
class RestApiBrowser extends atoum
{
    /**
     * Adding headers
     * @dataProvider addHeaderDataProvider
     */
    public function testAddRequestHeader(array $addHeadersSteps, array $expectedHeaders)
    {
        $this
            ->given(
                $httpClient = $this->mockHttpClient('http://verylastroom.com', 200)
            )
            ->and($sut = new SUT(null, null, $httpClient))
        ;

        foreach ($addHeadersSteps as $addHeadersStep) {
            foreach($addHeadersStep as $headerName => $headerValue) {
                $this->and($sut->addRequestHeader($headerName, $headerValue));
            }
        }

        $this
            ->array($sut->getRequestHeaders())->isIdenticalTo($expectedHeaders)
        ;
    }

    /**
     * @param string $baseUrl
     * @param int $responseStatusCode
     * @param array $headers
     *
     * @return \Ivory\HttpAdapter\HttpAdapterInterface
     */
    private function mockHttpClient($baseUrl, $responseStatusCode, array $headers = [])
    {
        $mockHttpClient = new \Ivory\HttpAdapter\MockHttpAdapter();
        $mockHttpClient->getConfiguration()->setBaseUri($baseUrl);
        $messageFactory = new \Ivory\HttpAdapter\Message\MessageFactory($baseUrl);
        $mockHttpClient->appendResponse(
            $messageFactory->createResponse(
                $responseStatusCode,
                \Ivory\HttpAdapter\Message\RequestInterface::PROTOCOL_VERSION_1_1,
                $headers
            )
        );
        return $mockHttpClient;
    }

    public function addHeaderDataProvider()
    {
        return [
            [[], []],
            [[["name" => "value"]], ["name" => "value"]],
            [[["name" => "value"], ["name" => "value2"]], ["name" => ["value", "value2"]]],
        ];
    }

    /**
     * Setting headers
     * @dataProvider setHeaderDataProvider
     */
    public function testSetRequestHeader(array $setHeadersSteps, array $expectedHeaders)
    {
        $this
            ->given(
                $httpClient = $this->mockHttpClient('http://verylastroom.com', 200)
            )
            ->and($sut = new SUT(null, null, $httpClient))
        ;

        foreach ($setHeadersSteps as $addHeadersStep) {
            foreach($addHeadersStep as $headerName => $headerValue) {
                $this->and($sut->setRequestHeader($headerName, $headerValue));
            }
        }

        $this
            ->array($sut->getRequestHeaders())->isIdenticalTo($expectedHeaders)
        ;
    }

    public function setHeaderDataProvider()
    {
        return [
            [[], []],
            [[["name" => "value"]], ["name" => "value"]],
            [[["name" => "value"], ["name" => "value2"]], ["name" => "value2"]],
        ];
    }

    /**
     * @dataProvider requestDataProvider
     * @param string $url
     * @param array  $requestHeaders
     */
    public function test_get_request($url, array $requestHeaders)
    {
        // Given
        $mockHttpClient = $this->mockHttpClient('http://verylastroom.com', 200, []);

        $restApiContext = new SUT(null, null, $mockHttpClient);
        foreach ($requestHeaders as $requestHeaderKey => $requestHeaderValue) {
            $restApiContext->addRequestHeader($requestHeaderKey, $requestHeaderValue);
        }

        // When
        $restApiContext->sendRequest('GET', $url);

        // Then
        $request = $restApiContext->getRequest();
        $intersect = array_intersect_key($requestHeaders, $request->getHeaders());

        $this->array($requestHeaders)->isEqualTo($intersect);
    }

    public function requestDataProvider()
    {
        return [
            [
                'url' => 'http://verylastroom.com/',
                'requestHeaders' => [
                    "name" => "value"
                ]
            ],
            [
                'url' => 'http://verylastroom.com/',
                'requestHeaders' => [
                    "name1" => "value1",
                    "name2" => "value2"

                ]
            ],
            [
                'url' => '/?test=a:2', // Without host with weird query string
                'requestHeaders' => [
                    "name1" => "value1",
                    "name2" => "value2"
                ]
            ]
        ];
    }

        /**
     * @dataProvider urlWithSlashesProvider
     * @param string $baseUrl
     * @param string $stepUrl
     * @param string $expectedUrl
     */
    public function test_create_request_with_slashes_to_clean($baseUrl, $stepUrl, $expectedUrl)
    {
        // Given
        $mockHttpClient = $this->mockHttpClient($baseUrl, 200, []);
        $restApiContext = new SUT(null, null, $mockHttpClient);
        // When
        $restApiContext->sendRequest('GET', $stepUrl);
        // Then
        $request = $restApiContext->getRequest();
        $this->phpString($request->getUri()->__toString())->isEqualTo($expectedUrl);
    }

    public function urlWithSlashesProvider()
    {
        return [
            [ // Trim right + left
                'baseUrl' => 'http://verylastroom.com/',
                'stepUrl' => '/contact/',
                'expectedUrl' => 'http://verylastroom.com/contact/'
            ],
            [ // Trim left
                'baseUrl' => 'http://verylastroom.com',
                'stepUrl' => '/contact/',
                'expectedUrl' => 'http://verylastroom.com/contact/'
            ],
            [ // Trim right
                'baseUrl' => 'http://verylastroom.com/',
                'stepUrl' => 'contact/',
                'expectedUrl' => 'http://verylastroom.com/contact/'
            ],
            [ // Add missing slash
                'baseUrl' => 'http://verylastroom.com',
                'stepUrl' => 'contact/',
                'expectedUrl' => 'http://verylastroom.com/contact/'
            ]
        ];
    }

    /**
     * @dataProvider responseDataProvider
     * @param int   $statusCode
     * @param array $responseHeaders
     */
    public function test_get_response($statusCode, array $responseHeaders)
    {
        // Given
        $mockHttpClient = $this->mockHttpClient('http://verylastroom.com', $statusCode, $responseHeaders);

        $restApiContext = new SUT(null, null, $mockHttpClient);

        // When
        $restApiContext->sendRequest('GET', 'http://verylastroom.com/');

        // Then
        $response = $restApiContext->getResponse();
        $intersect = array_intersect_key($responseHeaders, $response->getHeaders());

        $this->array($responseHeaders)->isEqualTo($intersect);
    }

    public function responseDataProvider()
    {
        return [
            [
                'statusCode' => 200,
                'requestHeaders' => [
                    "name" => "value"
                ]
            ],
            [
                'statusCode' => 400,
                'requestHeaders' => [
                    "name1" => "value1",
                    "name2" => "value2"
                ]
            ]
        ];
    }
}
