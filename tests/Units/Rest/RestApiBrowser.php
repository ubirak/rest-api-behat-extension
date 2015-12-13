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
        $this->castToString($request->getUri())->isEqualTo($expectedUrl);
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
    public function test_get_return_the_response_we_expected($statusCode, array $responseHeaders)
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
                'responseHeaders' => [
                    "name" => "value"
                ]
            ],
            [
                'statusCode' => 400,
                'responseHeaders' => [
                    "name1" => "value1",
                    "name2" => "value2"
                ]
            ]
        ];
    }

    /**
     * @dataProvider formDataUseCase
     */
    public function test_we_can_send_body_as_form_data($formData, $expectedBody)
    {
        $this
            ->given(
                $mockHttpAdapter = $this->mockHttpClient('http://verylastroom.com', 200, []),
                $restApiBrowser = new SUT(null, null, $mockHttpAdapter)
            )
            ->when(
                $restApiBrowser->sendRequest('POST', '/api', $formData)
            )
            ->then
                ->castToString($mockHttpAdapter->getReceivedRequests()[0]->getBody())
                    ->isEqualTo($expectedBody)
        ;
    }

    public function formDataUseCase()
    {
        return [
            [[], ''],
            [['username' => 'jean-marc'], 'username=jean-marc'],
            [['username' => 'jean-marc', 'password' => 'ecureuil'], 'username=jean-marc&password=ecureuil'],
        ];
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
}
