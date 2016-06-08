<?php

namespace Rezzza\RestApiBehatExtension\Json;

use mageekguy\atoum\asserter\generator as asserter;
use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Gherkin\Node\PyStringNode;

class JsonContext implements Context, SnippetAcceptingContext
{
    private $jsonInspector;

    private $asserter;

    private $jsonSchemaBaseUrl;

    public function __construct(JsonInspector $jsonInspector, $jsonSchemaBaseUrl = null)
    {
        $this->jsonInspector = $jsonInspector;
        $this->asserter = new asserter;
        $this->jsonSchemaBaseUrl = rtrim($jsonSchemaBaseUrl, '/');
    }

    /**
     * @When /^I load JSON:$/
     */
    public function iLoadJson(PyStringNode $jsonContent)
    {
        $this->jsonInspector->writeJson((string) $jsonContent);
    }

    /**
     * @Then /^the response should be in JSON$/
     */
    public function responseShouldBeInJson()
    {
        $this->jsonInspector->readJson();
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
     * @Then /^the JSON array node "(?P<jsonNode>[^"]*)" should have (?P<expectedNth>\d+) elements?$/
     */
    public function theJsonNodeShouldHaveElements($jsonNode, $expectedNth)
    {
        $realValue = $this->evaluateJsonNodeValue($jsonNode);

        $this->asserter->phpArray($realValue)->hasSize($expectedNth);
    }

    /**
     * @Then /^the JSON array node "(?P<jsonNode>[^"]*)" should contain "(?P<expectedValue>.*)" element$/
     */
    public function theJsonArrayNodeShouldContainElements($jsonNode, $expectedValue)
    {
        $realValue = $this->evaluateJsonNodeValue($jsonNode);

        $this->asserter->phpArray($realValue)->contains($expectedValue);
    }
    
    /**
     * @Then /^the JSON array node "(?P<jsonNode>[^"]*)" should not contain "(?P<expectedValue>.*)" element$/
     */
    public function theJsonArrayNodeShouldNotContainElements($jsonNode, $expectedValue)
    {
        $realValue = $this->evaluateJsonNodeValue($jsonNode);

        $this->asserter->phpArray($realValue)->notContains($expectedValue);
    }

    /**
     * @Then /^the JSON node "(?P<jsonNode>[^"]*)" should contain "(?P<expectedValue>.*)"$/
     */
    public function theJsonNodeShouldContain($jsonNode, $expectedValue)
    {
        $realValue = $this->evaluateJsonNodeValue($jsonNode);

        $this->asserter->string((string) $realValue)->contains($expectedValue);
    }

    /**
     * Checks, that given JSON node does not contain given value
     *
     * @Then /^the JSON node "(?P<jsonNode>[^"]*)" should not contain "(?P<unexpectedValue>.*)"$/
     */
    public function theJsonNodeShouldNotContain($jsonNode, $unexpectedValue)
    {
        $realValue = $this->evaluateJsonNodeValue($jsonNode);

        $this->asserter->string((string) $realValue)->notContains($unexpectedValue);
    }

    /**
     * Checks, that given JSON node exist
     *
     * @Given /^the JSON node "(?P<jsonNode>[^"]*)" should exist$/
     */
    public function theJsonNodeShouldExist($jsonNode)
    {
        try {
            $this->evaluateJsonNodeValue($jsonNode);
        } catch (\Exception $e) {
            throw new \Exception(sprintf("The node '%s' does not exist.", $jsonNode), 0, $e);
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
            // If the node does not exist an exception should be throwed
        }

        if ($e === null) {
            throw new \Exception(sprintf("The node '%s' exists and contains '%s'.", $jsonNode, json_encode($realValue)));
        }
    }

    /**
     * @Then /^the JSON should be valid according to this schema:$/
     */
    public function theJsonShouldBeValidAccordingToThisSchema(PyStringNode $jsonSchemaContent)
    {
        $this->jsonInspector->validateJson(
            new JsonSchema($jsonSchemaContent)
        );
    }

    /**
     * @Then /^the JSON should be valid according to the schema "(?P<filename>[^"]*)"$/
     */
    public function theJsonShouldBeValidAccordingToTheSchema($filename)
    {
        $filename = $this->resolveFilename($filename);

        $this->jsonInspector->validateJson(
            new JsonSchema(
                file_get_contents($filename),
                'file://' . $filename
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
        return $this->jsonInspector->readJsonNodeValue($jsonNode);
    }

    private function readJson()
    {
        return $this->jsonInspector->readJson();
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

        $filename = $this->jsonSchemaBaseUrl . '/' . $filename;

        if (false === is_file($filename)) {
            throw new \RuntimeException(sprintf(
                'The JSON schema file "%s" doesn\'t exist',
                $filename
            ));
        }

        return realpath($filename);
    }
}
