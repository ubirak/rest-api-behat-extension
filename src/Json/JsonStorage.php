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
        return new Json($this->responseStorage->read());
    }
}
