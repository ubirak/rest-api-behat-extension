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
            ->and($sut = new SUT($httpClient))
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

    public function addHeaderDataProvider()
    {
        return array(
            array(array(), array()),
            array(array(array("name" => "value")), array("name" => "value")),
            array(array(array("name" => "value"), array("name" => "value2")), array("name" => array("value", "value2"))),
        );
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
            ->and($sut = new SUT($httpClient, null, false))
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
        return array(
            array(array(), array()),
            array(array(array("name" => "value")), array("name" => "value")),
            array(array(array("name" => "value"), array("name" => "value2")), array("name" => "value2")),
        );
    }

    /**
     * @dataProvider requestDataProvider
     * @param string $url
     * @param array  $requestHeaders
     */
    public function test_get_request($url, array $requestHeaders)
    {
        // Given
        $mockHttpClient = $this->mockHttpClient('http://verylastroom.com', 200, array());

        $restApiContext = new SUT($mockHttpClient, null, false);
        foreach ($requestHeaders as $requestHeaderKey => $requestHeaderValue) {
            $restApiContext->addRequestHeader($requestHeaderKey, $requestHeaderValue);
        }

        // When
        $restApiContext->sendRequest('GET', $url);

        // Then
        $request = $restApiContext->getRequest();
        $intersect = array_intersect_key($requestHeaders, $request->getHeaders()->toArray());

        $this->array($requestHeaders)->isEqualTo($intersect);
    }

    public function requestDataProvider()
    {
        return array(
            array(
                'url' => 'http://verylastroom.com/',
                'requestHeaders' => array(
                    array("name" => "value")
                )
            ),
            array(
                'url' => 'http://verylastroom.com/',
                'requestHeaders' => array(
                    array("name1" => "value1"), array("name2" => "value2")
                )
            ),
            array(
                'url' => '/?test=a:2', // Without host with weird query string
                'requestHeaders' => array(
                    array("name1" => "value1"), array("name2" => "value2")
                )
            )
        );
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
        $mockHttpClient = $this->mockHttpClient($baseUrl, 200, array());
        $restApiContext = new SUT($mockHttpClient, null, false);
        // When
        $restApiContext->sendRequest('GET', $stepUrl);
        // Then
        $request = $restApiContext->getRequest();
        $this->string($request->getUrl())->isEqualTo($expectedUrl);
    }
    public function urlWithSlashesProvider()
    {
        return array(
            array( // Trim right + left
                'baseUrl' => 'http://verylastroom.com/',
                'stepUrl' => '/contact/',
                'expectedUrl' => 'http://verylastroom.com/contact/'
            ),
            array( // Trim left
                'baseUrl' => 'http://verylastroom.com',
                'stepUrl' => '/contact/',
                'expectedUrl' => 'http://verylastroom.com/contact/'
            ),
            array( // Trim right
                'baseUrl' => 'http://verylastroom.com/',
                'stepUrl' => 'contact/',
                'expectedUrl' => 'http://verylastroom.com/contact/'
            ),
            array( // Add missing slash
                'baseUrl' => 'http://verylastroom.com',
                'stepUrl' => 'contact/',
                'expectedUrl' => 'http://verylastroom.com/contact/'
            )
        );
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

        $restApiContext = new SUT($mockHttpClient, null, false);

        // When
        $restApiContext->sendRequest('GET', 'http://verylastroom.com/');

        // Then
        $response = $restApiContext->getResponse();
        $intersect = array_intersect_key($responseHeaders, $response->getHeaders()->toArray());

        $this->array($responseHeaders)->isEqualTo($intersect);
    }

    public function responseDataProvider()
    {
        return array(
            array(
                'statusCode' => 200,
                'requestHeaders' => array(
                    array("name" => "value")
                )
            ),
            array(
                'statusCode' => 400,
                'requestHeaders' => array(
                    array("name1" => "value1"), array("name2" => "value2")
                )
            )
        );
    }

        /**
     * @param string $baseUrl
     * @param int    $responseStatusCode
     * @param array  $headers
     *
     * @return \Guzzle\Http\Client
     */
    private function mockHttpClient($baseUrl, $responseStatusCode, array $headers = array())
    {
        $mockHttpClient = new \mock\Guzzle\Http\Client();
        $mockHttpClient->getMockController()->send = new \Guzzle\Http\Message\Response(
            $responseStatusCode,
            $headers
        );
        $mockHttpClient->getMockController()->getBaseUrl = $baseUrl;
        return $mockHttpClient;
    }
}
