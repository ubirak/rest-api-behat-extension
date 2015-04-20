<?php

namespace Rezzza\RestApiBehatExtension\Json;

use Behat\Behat\Context\Initializer\InitializerInterface;
use Behat\Behat\Context\ContextInterface;

class JsonStorageInitializer implements InitializerInterface
{
    private $jsonStorage;

    public function __construct(JsonStorage $jsonStorage)
    {
        $this->jsonStorage = $jsonStorage;
    }

    public function supports(ContextInterface $context)
    {
        return $context instanceof JsonStorageAware;
    }

    public function initialize(ContextInterface $context)
    {
        $context->setJsonStorage($this->jsonStorage);
    }
}
