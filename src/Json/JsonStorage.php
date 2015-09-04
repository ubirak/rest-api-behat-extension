<?php

namespace Rezzza\RestApiBehatExtension\Json;

use Rezzza\RestApiBehatExtension\Response\ResponseStorage;

/**
 * Store the JSON that we could analyze it in JsonContext
 */
class JsonStorage
{
    private $responseStorage;

    public function __construct(ResponseStorage $responseStorage)
    {
        $this->responseStorage = $responseStorage;
    }

    public function writeRawContent($content)
    {
        $this->responseStorage->writeRawContent($content);
    }

    public function readJson()
    {
        try {
            return new Json($this->responseStorage->read());
        } catch (\LogicException $e) {
            throw new \LogicException('No content defined. You should use JsonContainer::setRawContent method to inject content you want to analyze');
        }
    }
}
