<?php

namespace Ubirak\RestApiBehatExtension\Rest;

use Behat\Behat\Context\Argument\ArgumentResolver;

class RestApiBrowserResolver implements ArgumentResolver
{
    private $restApiBrowser;

    public function __construct(RestApiBrowser $restApiBrowser)
    {
        $this->restApiBrowser = $restApiBrowser;
    }

    public function resolveArguments(\ReflectionClass $classReflection, array $arguments)
    {
        $constructor = $classReflection->getConstructor();
        if ($constructor === null) {
            return $arguments;
        }

        $parameters = $constructor->getParameters();
        foreach ($parameters as $parameter) {
            if (null !== $parameter->getType() && ($parameter->getType()->getName()) === 'Ubirak\RestApiBehatExtension\Rest\RestApiBrowser') {
                $arguments[$parameter->name] = $this->restApiBrowser;
            }
        }

        return $arguments;
    }
}
