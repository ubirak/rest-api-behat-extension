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
     * @dataProvider urlWithSlashesProvider
     * @param string $baseUrl
     * @param string $stepUrl
     * @param string $expectedUrl
     */
    public function test_create_request_with_slashes_to_clean($baseUrl, $stepUrl, $expectedUrl)
    {
        // Given
        $mockHttpClient = $this->mockHttpClient(200);
        $restApiContext = new SUT($baseUrl, $mockHttpClient);
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
        $mockHttpClient = $this->mockHttpClient($statusCode, $responseHeaders);

        $restApiContext = new SUT('http://verylastroom.com', $mockHttpClient);

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
     * @param string $baseUrl
     * @param int $responseStatusCode
     * @param array $headers
     *
     * @return \Ivory\HttpAdapter\HttpAdapterInterface
     */
    private function mockHttpClient($responseStatusCode, array $headers = [])
    {
        $mockHttpClient = new \Http\Mock\Client;
        $messageFactory = new \Http\Message\MessageFactory\GuzzleMessageFactory;
        $mockHttpClient->addResponse(
            $messageFactory->createResponse(
                $responseStatusCode,
                null,
                $headers
            )
        );

        return $mockHttpClient;
    }
}
