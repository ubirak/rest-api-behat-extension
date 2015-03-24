<?php

namespace Rezzza\JsonApiBehatExtension\Tests\Units;

use atoum;
use Rezzza\JsonApiBehatExtension\RestApiContext as SUT;

/**
 * @author Mikaël FIMA <mika@verylastroom.com>
 * @author Guillaume MOREL <guillaume.morel@verylastroom.com>
 */
class RestApiContext extends atoum
{
    /**
     * Adding headers
     * @dataProvider addHeaderDataProvider
     */
    public function testAddRequestHeader(array $addHeadersSteps, array $expectedHeaders)
    {
        $this
            ->given(
                $httpClient = $this->mockHttpClient(200)
            )
            ->and($sut = new SUT($httpClient, null, false))
        ;

        foreach ($addHeadersSteps as $addHeadersStep) {
            foreach($addHeadersStep as $headerName => $headerValue) {
                $this->and($sut->iAddHeaderEqualTo($headerName, $headerValue));
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
                $httpClient = $this->mockHttpClient(200)
            )
            ->and($sut = new SUT($httpClient, null, false))
        ;

        foreach ($setHeadersSteps as $addHeadersStep) {
            foreach($addHeadersStep as $headerName => $headerValue) {
                $this->and($sut->iSetHeaderEqualTo($headerName, $headerValue));
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
     * @param array $requestHeaders
     */
    public function test_get_request(array $requestHeaders)
    {
        // Given
        $mockHttpClient = $this->mockHttpClient(200, array());

        $restApiContext = new SUT($mockHttpClient, null, false);
        foreach ($requestHeaders as $requestHeaderKey => $requestHeaderValue) {
            $restApiContext->iAddHeaderEqualTo($requestHeaderKey, $requestHeaderValue);
        }

        // When
        $restApiContext->iSendARequest('GET', 'http://verylastroom.com/');

        // Then
        $request = $restApiContext->getRequest();
        $intersect = array_intersect_key($requestHeaders, $request->getHeaders()->toArray());

        $this->array($requestHeaders)->isEqualTo($intersect);
    }

    public function requestDataProvider()
    {
        return array(
            array(
                'requestHeaders' => array(
                    array("name" => "value")
                )
            ),
            array(
                'requestHeaders' => array(
                    array("name1" => "value1"), array("name2" => "value2")
                )
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
        $mockHttpClient = $this->mockHttpClient($statusCode, $responseHeaders);

        $restApiContext = new SUT($mockHttpClient, null, false);

        // When
        $restApiContext->iSendARequest('GET', 'http://verylastroom.com/');

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
     * @param int   $responseStatusCode
     * @param array $headers
     *
     * @return \Guzzle\Http\Client
     */
    private function mockHttpClient($responseStatusCode, array $headers = array())
    {
        $mockHttpClient = new \mock\Guzzle\Http\Client();
        $mockHttpClient->getMockController()->send = new \Guzzle\Http\Message\Response(
            $responseStatusCode,
            $headers
        );

        return $mockHttpClient;
    }
}
