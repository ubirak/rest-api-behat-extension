<?php

namespace Rezzza\JsonApiBehatExtension\Tests\Units\Json;

use atoum;
use JsonSchema\Validator;
use Rezzza\JsonApiBehatExtension\Json\JsonInspector as SUT;

class JsonInspector extends atoum
{
    public function test_should_read_json()
    {
        $this
            ->given(
                $json = new \mock\Rezzza\JsonApiBehatExtension\Json\Json('{}'),
                $json->getMockController()->read = 'foobar'
            )
            ->and(
                $sut = new SUT('mode')
            )
            ->when(
                $result = $sut->evaluate($json, 'foo.bar')
            )
            ->then
                ->variable($result)
                    ->isEqualTo('foobar')

                ->mock($json)
                    ->call('read')
                    ->withArguments('foo.bar', new \Symfony\Component\PropertyAccess\PropertyAccessor(false))
                    ->once()
        ;
    }

    public function test_should_fail_if_json_reading_fail()
    {
        $this
            ->given(
                $json = new \mock\Rezzza\JsonApiBehatExtension\Json\Json('{}'),
                $json->getMockController()->read->throw = new \Exception()
            )
            ->and(
                $sut = new SUT('mode')
            )
                ->exception(function () use ($json, $sut) {
                    $sut->evaluate($json, 'foo.bar');
                })
                    ->hasMessage('Failed to evaluate expression "foo.bar"')
        ;
    }

    public function test_should_convert_expression_if_javascript_mode()
    {
        $this
            ->given(
                $json = new \mock\Rezzza\JsonApiBehatExtension\Json\Json('{}'),
                $json->getMockController()->read = 'foobar'
            )
            ->and(
                $sut = new SUT('javascript')
            )
            ->when(
                $result = $sut->evaluate($json, 'foo->bar')
            )
            ->then
                ->variable($result)
                    ->isEqualTo('foobar')

                ->mock($json)
                    ->call('read')
                    ->withArguments('foo.bar', new \Symfony\Component\PropertyAccess\PropertyAccessor(false))
                    ->once()
        ;
    }

    public function test_should_no_convert_expression_if_no_javascript_mode()
    {
        $this
            ->given(
                $json = new \mock\Rezzza\JsonApiBehatExtension\Json\Json('{}'),
                $json->getMockController()->read = 'foobar'
            )
            ->and(
                $sut = new SUT('foo')
            )
            ->when(
                $result = $sut->evaluate($json, 'foo->bar')
            )
            ->then
                ->variable($result)
                    ->isEqualTo('foobar')

                ->mock($json)
                    ->call('read')
                    ->withArguments('foo->bar', new \Symfony\Component\PropertyAccess\PropertyAccessor(false))
                    ->once()
        ;
    }

    public function test_should_valid_json_through_its_schema()
    {
        $this
            ->given(
                $json = new \mock\Rezzza\JsonApiBehatExtension\Json\Json('{}'),
                $schema = new \mock\Rezzza\JsonApiBehatExtension\Json\JsonSchema('{}'),
                $schema->getMockController()->validate = 'foobar',
                $sut = new SUT('foo')
            )
            ->when(
                $result = $sut->validate($json, $schema)
            )
            ->then
                ->variable($result)
                    ->isEqualTo('foobar')

                ->mock($schema)
                    ->call('validate')
                    ->withArguments($json, new Validator)
                    ->once()
        ;
    }
}
