<?php

namespace Rezzza\RestApiBehatExtension\Response;

use Behat\Behat\Context\Initializer\InitializerInterface;
use Behat\Behat\Context\ContextInterface;

class ResponseStorageInitializer implements InitializerInterface
{
    private $responseStorage;

    public function __construct(ResponseStorage $responseStorage)
    {
        $this->responseStorage = $responseStorage;
    }

    public function supports(ContextInterface $context)
    {
        return $context instanceof ResponseStorageAware;
    }

    public function initialize(ContextInterface $context)
    {
        $context->setResponseStorage($this->responseStorage);
    }
}
