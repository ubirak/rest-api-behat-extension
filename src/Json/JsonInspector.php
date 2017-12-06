<?php

namespace Ubirak\RestApiBehatExtension\Json;

class JsonInspector
{
    private $jsonParser;

    private $jsonStorage;

    private $jsonSearcher;

    public function __construct(JsonStorage $jsonStorage, JsonParser $jsonParser, JsonSearcher $jsonSearcher)
    {
        $this->jsonParser = $jsonParser;
        $this->jsonStorage = $jsonStorage;
        $this->jsonSearcher = $jsonSearcher;
    }

    public function readJsonNodeValue($jsonNodeExpression)
    {
        return $this->jsonParser->evaluate(
            $this->readJson(),
            $jsonNodeExpression
        );
    }

    public function searchJsonPath($pathExpression)
    {
        return $this->jsonSearcher->search($this->readJson(), $pathExpression);
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
