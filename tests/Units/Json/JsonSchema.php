<?php

namespace Rezzza\RestApiBehatExtension\Tests\Units\Json;

use atoum;
use Rezzza\RestApiBehatExtension\Json\JsonSchema as SUT;

class JsonSchema extends atoum
{
    public function test_should_validate_correct_json()
    {
        $this
            ->given(
                $sut = new SUT('schema.json'),
                $json = new \Rezzza\RestApiBehatExtension\Json\Json('{"foo":"bar"}'),
                $validator = new \mock\JsonSchema\Validator,
                $validator->getMockController()->check = true,
                $this->mockGenerator->orphanize('__construct'),
                $refResolver = new \mock\JsonSchema\RefResolver,
                $refResolver->getMockController()->resolve = 'mySchema'
            )
            ->when(
                $result = $sut->validate($json, $validator, $refResolver)
            )
                ->mock($validator)
                    ->call('check')
                    ->withArguments(json_decode('{"foo":"bar"}'), 'mySchema')
                    ->once()

                ->boolean($result)
                    ->isTrue()
        ;
    }

    public function test_should_throw_exception_for_incorrect_json()
    {
        $this
            ->given(
                $sut = new SUT('schema.json'),
                $json = new \Rezzza\RestApiBehatExtension\Json\Json('{}'),
                $validator = new \mock\JsonSchema\Validator,
                $validator->getMockController()->check = false,
                $validator->getMockController()->getErrors = [
                    ['property' => 'foo', 'message' => 'invalid'],
                    ['property' => 'bar', 'message' => 'not found']
                ],
                $this->mockGenerator->orphanize('__construct'),
                $refResolver = new \mock\JsonSchema\RefResolver,
                $refResolver->getMockController()->resolve = 'mySchema'
            )
            ->exception(function () use ($sut, $json, $validator, $refResolver) {
                $sut->validate($json, $validator, $refResolver);
            })
                ->hasMessage(<<<"ERROR"
JSON does not validate. Violations:
  - [foo] invalid
  - [bar] not found

ERROR
                )
        ;
    }
}
