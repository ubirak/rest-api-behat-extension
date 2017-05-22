<?php

namespace Rezzza\RestApiBehatExtension\Tests\Units;

use atoum;

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
                $mockRestApiBrowser = $this->mockRestApiBrowser(),
                $this->calling($mockRestApiBrowser)->sendRequest = null,
                $this->newTestedInstance($mockRestApiBrowser)
            )
        ;

        foreach ($addHeadersSteps as $addHeadersStep) {
            foreach($addHeadersStep as $headerName => $headerValue) {
                $this->testedInstance->iAddHeaderEqualTo($headerName, $headerValue);
            }
        }

        $this
            ->when($this->testedInstance->iSendARequest('GET', '/'))
            ->then
                ->mock($mockRestApiBrowser)
                    ->call('sendRequest')
                    ->withArguments('GET', '/', null, $expectedHeaders)
                    ->once()
        ;
    }

    public function addHeaderDataProvider()
    {
        return [
            [[], []],
            [[["name" => "value"]], ["name" => "value"]],
            [[["name" => "value"], ["name" => "value2"]], ["name" => "value, value2"]],
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
                $mockRestApiBrowser = $this->mockRestApiBrowser(),
                $this->calling($mockRestApiBrowser)->sendRequest = null,
                $this->newTestedInstance($mockRestApiBrowser)
            )
        ;

        foreach ($setHeadersSteps as $addHeadersStep) {
            foreach($addHeadersStep as $headerName => $headerValue) {
                $this->testedInstance->iSetHeaderEqualTo($headerName, $headerValue);
            }
        }

        $this
            ->when($this->testedInstance->iSendARequest('GET', '/'))
            ->then
                ->mock($mockRestApiBrowser)
                    ->call('sendRequest')
                    ->withArguments('GET', '/', null, $expectedHeaders)
                    ->once()
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

    private function mockRestApiBrowser()
    {
        $this->mockGenerator->orphanize('__construct');

        return new \mock\Rezzza\RestApiBehatExtension\Rest\RestApiBrowser;
    }
}
