<?php

namespace Rezzza\RestApiBehatExtension\Tests\Units\Json;

use atoum;
use Rezzza\RestApiBehatExtension\Json\JsonSchema as SUT;

class JsonSchema extends atoum
{
    public function test_should_not_resolve_without_uri()
    {
        $this
            ->given(
                $sut = new SUT('{}'),
                $this->mockGenerator->orphanize('__construct'),
                $resolver = new \mock\JsonSchema\RefResolver
            )
            ->exception(function () use ($sut, $resolver) {
                $sut->resolve($resolver);
            })
                ->hasMessage('Cannot resolve JsonSchema without uri parameter')

                ->mock($resolver)
                    ->call('resolve')
                    ->never()
        ;
    }

    public function test_should_resolve_with_uri()
    {
        $this
            ->given(
                $sut = new SUT('{}', 'file://test'),
                $this->mockGenerator->orphanize('__construct'),
                $resolver = new \mock\JsonSchema\RefResolver,
                $resolver->getMockController()->resolve = true
            )
            ->when(
                $result = $sut->resolve($resolver)
            )
                ->mock($resolver)
                    ->call('resolve')
                    ->withArguments(new \stdClass, 'file://test')
                    ->once()

                ->object($result)
                    ->isIdenticalTo($sut)
        ;
    }

    public function test_should_validate_correct_json()
    {
        $this
            ->given(
                $sut = new SUT('{"schema": true}'),
                $json = new \Rezzza\RestApiBehatExtension\Json\Json('{"foo":"bar"}'),
                $validator = new \mock\JsonSchema\Validator,
                $validator->getMockController()->check = true
            )
            ->when(
                $result = $sut->validate($json, $validator)
            )
                ->mock($validator)
                    ->call('check')
                    ->withArguments(json_decode('{"foo":"bar"}'), json_decode('{"schema": true}'))
                    ->once()

                ->boolean($result)
                    ->isTrue()
        ;
    }

    public function test_should_throw_exception_for_incorrect_json()
    {
        $this
            ->given(
                $sut = new SUT('{}'),
                $json = new \Rezzza\RestApiBehatExtension\Json\Json('{}'),
                $validator = new \mock\JsonSchema\Validator,
                $validator->getMockController()->check = false,
                $validator->getMockController()->getErrors = [
                    ['property' => 'foo', 'message' => 'invalid'],
                    ['property' => 'bar', 'message' => 'not found']
                ]
            )
            ->exception(function () use ($sut, $json, $validator) {
                $sut->validate($json, $validator);
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
