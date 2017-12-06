<?php

namespace Ubirak\RestApiBehatExtension\Tests\Units\Json;

use atoum;

class WrongJsonExpectation extends atoum
{
    public function test_it_display_pretty_json_when_cast_to_string()
    {
        $this
            ->given(
                $json = new \Ubirak\RestApiBehatExtension\Json\Json('{"foo":"bar"}'),
                $this->newTestedInstance('Error', $json)
            )
            ->when(
                $result = $this->testedInstance->__toString()
            )
            ->then
                ->string($result)
                    ->contains(<<<'EOF'
|  {
|      "foo": "bar"
|  }
EOF
                    )
        ;
    }
}
