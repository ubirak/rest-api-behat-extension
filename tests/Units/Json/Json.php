<?php

namespace Ubirak\RestApiBehatExtension\Tests\Units\Json;

use atoum;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Ubirak\RestApiBehatExtension\Json\Json as SUT;

class Json extends atoum
{
    public function test_should_not_decode_invalid_json()
    {
        $this
            ->exception(function () {
                $sut = new SUT('{{json');
            })
                ->hasMessage('The string "{{json" is not valid json')
        ;
    }

    public function test_should_decode_valid_json()
    {
        try {
            $this
                ->given(
                    $hasException = false
                )
                ->when(
                    new SUT('{"foo": "bar"}')
                )
            ;
        } catch (\Exception $e) {
            $hasException = true;
        }

        $this->boolean($hasException)->isFalse();
    }

    public function test_should_encode_valid_json()
    {
        $this
            ->given(
                $content = '{"foo":"bar"}'
            )
            ->when(
                $sut = new SUT($content)
            )
            ->then
                ->castToString($sut)
                    ->isEqualTo($content)
        ;
    }

    public function test_should_not_read_invalid_expression()
    {
        $this
            ->given(
                $accessor = PropertyAccess::createPropertyAccessor(),
                $sut = new SUT('{"foo":"bar"}')
            )
            ->exception(function () use ($sut, $accessor) {
                $sut->read('jeanmarc', $accessor);
            })
                ->isInstanceOf('Symfony\Component\PropertyAccess\Exception\NoSuchPropertyException')
        ;
    }

    public function test_should_read_valid_expression()
    {
        $stringAsserterFunc = class_exists('mageekguy\\atoum\\asserters\\phpString') ? 'phpString' : 'string';
        $this
            ->given(
                $accessor = PropertyAccess::createPropertyAccessor(),
                $sut = new SUT('{"foo":"bar"}')
            )
            ->when(
                $result = $sut->read('foo', $accessor)
            )
                ->$stringAsserterFunc($result)
                    ->isEqualTo('bar')
        ;
    }
}
