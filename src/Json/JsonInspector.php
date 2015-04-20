<?php

namespace Rezzza\RestApiBehatExtension\Json;

use JsonSchema\Validator;
use Symfony\Component\PropertyAccess\PropertyAccess;

class JsonInspector
{
    private $jsonParser;

    private $jsonStorage;

    public function __construct(JsonStorage $jsonStorage, JsonParser $jsonParser)
    {
        $this->jsonParser = $jsonParser;
        $this->jsonStorage = $jsonStorage;
    }

    public function readJsonNodeValue($jsonNodeExpression)
    {
        return $this->jsonParser->evaluate(
            $this->readJson(),
            $jsonNodeExpression
        );
    }

    public function validateJson(JsonSchema $jsonSchema)
    {
        $this->jsonParser->validate(
            $this->readJson(),
            $jsonSchema
        );
    }

    public function readJson()
    {
        return $this->jsonStorage->readJson();
    }

    public function writeJson($jsonContent)
    {
        $this->jsonStorage->writeRawContent($jsonContent);
    }
}
