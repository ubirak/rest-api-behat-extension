<?php

namespace Rezzza\JsonApiBehatExtension\Json;

use mageekguy\atoum\asserter\generator as asserter;
use Behat\Behat\Context\BehatContext;
use Behat\Gherkin\Node\PyStringNode;

class JsonContext extends BehatContext implements JsonStorageAware
{
    private $jsonInspector;

    private $asserter;

    private $jsonStorage;

    private $jsonSchemaBaseUrl;

    public function __construct(JsonInspector $jsonInspector, asserter $asserter, $jsonSchemaBaseUrl = null)
    {
        $this->jsonInspector = $jsonInspector;
        $this->asserter = $asserter;
        $this->jsonSchemaBaseUrl = rtrim($jsonSchemaBaseUrl, '/');
    }

    public function setJsonStorage(JsonStorage $jsonStorage)
    {
        $this->jsonStorage = $jsonStorage;
    }

    /**
     * @When /^I load JSON:$/
     */
    public function iLoadJson(PyStringNode $jsonContent)
    {
        $this->jsonStorage->writeRawContent($jsonContent);
    }

    /**
     * @Then /^the response should be in JSON$/
     */
    public function responseShouldBeInJson()
    {
        $this->readJson();
    }

    /**
     * @Then /^the JSON node "(?P<jsonNode>[^"]*)" should be equal to "(?P<expectedValue>.*)"$/
     */
    public function theJsonNodeShouldBeEqualTo($jsonNode, $expectedValue)
    {
        $realValue = $this->evaluateJsonNodeValue($jsonNode);

        $this->asserter->variable($realValue)->isEqualTo($expectedValue);
    }

    /**
     * @Then /^the JSON node "(?P<jsonNode>[^"]*)" should have (?P<expectedNth>\d+) elements?$/
     */
    public function theJsonNodeShouldHaveElements($jsonNode, $expectedNth)
    {
        $realValue = $this->evaluateJsonNodeValue($jsonNode);

        $this->asserter->phpArray($realValue)->hasSize($expectedNth);
    }

    /**
     * @Then /^the JSON node "(?P<jsonNode>[^"]*)" should contain "(?P<expectedValue>.*)"$/
     */
    public function theJsonNodeShouldContain($jsonNode, $expectedValue)
    {
        $realValue = $this->evaluateJsonNodeValue($jsonNode);

        $this->asserter->string($realValue)->contains($expectedValue);
    }

    /**
     * Checks, that given JSON node does not contain given value
     *
     * @Then /^the JSON node "(?P<jsonNode>[^"]*)" should not contain "(?P<unexpectedValue>.*)"$/
     */
    public function theJsonNodeShouldNotContain($jsonNode, $unexpectedValue)
    {
        $realValue = $this->evaluateJsonNodeValue($jsonNode);

        $this->asserter->string($realValue)->notContains($unexpectedValue);
    }

    /**
     * Checks, that given JSON node exist
     *
     * @Given /^the JSON node "(?P<jsonNode>[^"]*)" should exist$/
     */
    public function theJsonNodeShouldExist($jsonNode)
    {
        try {
            $realValue = $this->evaluateJsonNodeValue($jsonNode);
        } catch (\Exception $e) {
            throw new \Exception(sprintf("The node '%s' does not exist.", $jsonNode));
        }
    }

    /**
     * Checks, that given JSON node does not exist
     *
     * @Given /^the JSON node "(?P<jsonNode>[^"]*)" should not exist$/
     */
    public function theJsonNodeShouldNotExist($jsonNode)
    {
        $e = null;

        try {
            $realValue = $this->evaluateJsonNodeValue($jsonNode);
        } catch (\Exception $e) {
        }

        if ($e === null) {
            throw new \Exception(sprintf("The node '%s' exists and contains '%s'.", $jsonNode , json_encode($realValue)));
        }
    }

    /**
     * @Then /^the JSON should be valid according to this schema:$/
     */
    public function theJsonShouldBeValidAccordingToThisSchema(PyStringNode $jsonSchemaContent)
    {
        $this->jsonInspector->validate(
            $this->readJson(),
            new JsonSchema($jsonSchemaContent)
        );
    }

    /**
     * @Then /^the JSON should be valid according to the schema "(?P<filename>[^"]*)"$/
     */
    public function theJsonShouldBeValidAccordingToTheSchema($filename)
    {
        $filename = $this->resolveFilename($filename);

        $this->jsonInspector->validate(
            $this->readJson(),
            new JsonSchema(
                file_get_contents($filename),
                'file://' . getcwd() . '/' . $filename
            )
        );
    }

    /**
     * @Then /^the JSON should be equal to:$/
     */
    public function theJsonShouldBeEqualTo(PyStringNode $jsonContent)
    {
        $realJsonValue = $this->readJson();

        try {
            $expectedJsonValue = new Json($jsonContent);
        } catch (\Exception $e) {
            throw new \Exception('The expected JSON is not a valid');
        }

        $this->asserter->castToString($realJsonValue)->isEqualTo((string) $expectedJsonValue);
    }

    private function evaluateJsonNodeValue($jsonNode)
    {
        $json = $this->readJson();

        return $this->jsonInspector->evaluate($json, $jsonNode);
    }

    private function readJson()
    {
        if (null === $this->jsonStorage) {
            throw new \LogicException('No jsonStorage defined. Check your "setJsonStorage" method');
        }

        return $this->jsonStorage->readJson();
    }

    private function resolveFilename($filename)
    {
        if (true === is_file($filename)) {
            return realpath($filename);
        }

        if (null === $this->jsonSchemaBaseUrl) {
            throw new \RuntimeException(sprintf(
                'The JSON schema file "%s" doesn\'t exist',
                $filename
            ));
        }

        $filename = $this->jsonSchemaBaseUrl.'/'.$filename;

        if (false === is_file($filename)) {
            throw new \RuntimeException(sprintf(
                'The JSON schema file "%s" doesn\'t exist',
                $filename
            ));
        }

        return realpath($filename);
    }
}
