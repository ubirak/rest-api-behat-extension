<?php

namespace Rezzza\JsonApiBehatExtension\Tests\Units;

use atoum;
use Rezzza\JsonApiBehatExtension\RestApiContext as SUT;

/**
 * Description of RestApiContext
 *
 * @author Mikaël FIMA <mika@verylastroom.com>
 */
class RestApiContext extends atoum
{

    /**
     * Adding headers
     * @dataProvider addHeaderDataProvider
     */
    public function testAddHeader(array $addHeadersSteps, array $expectedHeaders)
    {
        $this
            ->given(
                $httpClient = new \mock\Guzzle\Http\Client(),
                $httpClient->getMockController()->send = new \Guzzle\Http\Message\Response(200)
            )
            ->and($sut = new SUT($httpClient, null, false))
        ;

        foreach ($addHeadersSteps as $addHeadersStep) {
            foreach($addHeadersStep as $headerName => $headerValue) {
                $this->and($sut->iAddHeaderEqualTo($headerName, $headerValue));
            }
        }

        $this
            ->array($sut->getHeaders())->isIdenticalTo($expectedHeaders)
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
    public function testSetHeader(array $setHeadersSteps, array $expectedHeaders)
    {
        $this
            ->given(
                $httpClient = new \mock\Guzzle\Http\Client(),
                $httpClient->getMockController()->send = new \Guzzle\Http\Message\Response(200)
            )
            ->and($sut = new SUT($httpClient, null, false))
        ;

        foreach ($setHeadersSteps as $addHeadersStep) {
            foreach($addHeadersStep as $headerName => $headerValue) {
                $this->and($sut->iSetHeaderEqualTo($headerName, $headerValue));
            }
        }

        $this
            ->array($sut->getHeaders())->isIdenticalTo($expectedHeaders)
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
}
