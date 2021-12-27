<?php

namespace Ubirak\RestApiBehatExtension\Json;

use atoum\atoum\asserter\generator as asserter;
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
        $this->asserter = new asserter();

        if (null !== $jsonSchemaBaseUrl) {
            $jsonSchemaBaseUrl = rtrim($jsonSchemaBaseUrl, '/');
        }
        $this->jsonSchemaBaseUrl = $jsonSchemaBaseUrl;
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
        $this->assert(function () use ($jsonNode, $expectedValue) {
            $realValue = $this->evaluateJsonNodeValue($jsonNode);
            $expectedValue = $this->evaluateExpectedValue($expectedValue);
            $this->asserter->variable($realValue)->isEqualTo($expectedValue);
        });
    }

    /**
     * @Then /^the JSON node "(?P<jsonNode>[^"]*)" should have (?P<expectedNth>\d+) elements?$/
     * @Then /^the JSON array node "(?P<jsonNode>[^"]*)" should have (?P<expectedNth>\d+) elements?$/
     */
    public function theJsonNodeShouldHaveElements($jsonNode, $expectedNth)
    {
        $this->assert(function () use ($jsonNode, $expectedNth) {
            $realValue = $this->evaluateJsonNodeValue($jsonNode);
            $this->asserter->phpArray($realValue)->hasSize($expectedNth);
        });
    }

    /**
     * @Then /^the JSON array node "(?P<jsonNode>[^"]*)" should contain "(?P<expectedValue>.*)" element$/
     */
    public function theJsonArrayNodeShouldContainElements($jsonNode, $expectedValue)
    {
        $this->assert(function () use ($jsonNode, $expectedValue) {
            $realValue = $this->evaluateJsonNodeValue($jsonNode);
            $this->asserter->phpArray($realValue)->contains($expectedValue);
        });
    }

    /**
     * @Then /^the JSON array node "(?P<jsonNode>[^"]*)" should not contain "(?P<expectedValue>.*)" element$/
     */
    public function theJsonArrayNodeShouldNotContainElements($jsonNode, $expectedValue)
    {
        $this->assert(function () use ($jsonNode, $expectedValue) {
            $realValue = $this->evaluateJsonNodeValue($jsonNode);
            $this->asserter->phpArray($realValue)->notContains($expectedValue);
        });
    }

    /**
     * @Then /^the JSON node "(?P<jsonNode>[^"]*)" should contain "(?P<expectedValue>.*)"$/
     */
    public function theJsonNodeShouldContain($jsonNode, $expectedValue)
    {
        $this->assert(function () use ($jsonNode, $expectedValue) {
            $realValue = $this->evaluateJsonNodeValue($jsonNode);
            $this->asserter->string((string) $realValue)->contains($expectedValue);
        });
    }

    /**
     * Checks, that given JSON node does not contain given value.
     *
     * @Then /^the JSON node "(?P<jsonNode>[^"]*)" should not contain "(?P<unexpectedValue>.*)"$/
     */
    public function theJsonNodeShouldNotContain($jsonNode, $unexpectedValue)
    {
        $this->assert(function () use ($jsonNode, $unexpectedValue) {
            $realValue = $this->evaluateJsonNodeValue($jsonNode);
            $this->asserter->string((string) $realValue)->notContains($unexpectedValue);
        });
    }

    /**
     * Checks, that given JSON node exist.
     *
     * @Given /^the JSON node "(?P<jsonNode>[^"]*)" should exist$/
     */
    public function theJsonNodeShouldExist($jsonNode)
    {
        try {
            $this->evaluateJsonNodeValue($jsonNode);
        } catch (\Exception $e) {
            throw new WrongJsonExpectation(sprintf("The node '%s' does not exist.", $jsonNode), $this->readJson(), $e);
        }
    }

    /**
     * Checks, that given JSON node does not exist.
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
            throw new WrongJsonExpectation(
                sprintf("The node '%s' exists and contains '%s'.", $jsonNode, json_encode($realValue)),
                $this->readJson(),
                $e
            );
        }
    }

    /**
     * @Then /^the JSON should be valid according to this schema:$/
     */
    public function theJsonShouldBeValidAccordingToThisSchema(PyStringNode $jsonSchemaContent)
    {
        $tempFilename = tempnam(sys_get_temp_dir(), 'rae');
        file_put_contents($tempFilename, $jsonSchemaContent);
        $this->assert(function () use ($tempFilename) {
            $this->jsonInspector->validateJson(
                new JsonSchema($tempFilename)
            );
        });
        unlink($tempFilename);
    }

    /**
     * @Then /^the JSON should be valid according to the schema "(?P<filename>[^"]*)"$/
     */
    public function theJsonShouldBeValidAccordingToTheSchema($filename)
    {
        $filename = $this->resolveFilename($filename);

        $this->assert(function () use ($filename) {
            $this->jsonInspector->validateJson(
                new JsonSchema($filename)
            );
        });
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

        $this->assert(function () use ($realJsonValue, $expectedJsonValue) {
            $this->asserter->castToString($realJsonValue)->isEqualTo((string) $expectedJsonValue);
        });
    }

    /**
     * @Then the JSON path expression :pathExpression should be equal to json :expectedJson
     */
    public function theJsonPathExpressionShouldBeEqualToJson($pathExpression, $expectedJson)
    {
        $expectedJson = new Json($expectedJson);
        $actualJson = Json::fromRawContent($this->jsonInspector->searchJsonPath($pathExpression));

        $this->asserter->castToString($actualJson)->isEqualTo((string) $expectedJson);
    }

    /**
     * @Then the JSON path expression :pathExpression should be equal to:
     */
    public function theJsonExpressionShouldBeEqualTo($pathExpression, PyStringNode $expectedJson)
    {
        $this->theJsonPathExpressionShouldBeEqualToJson($pathExpression, (string) $expectedJson);
    }

    /**
     * @Then the JSON path expression :pathExpression should have result
     */
    public function theJsonPathExpressionShouldHaveResult($pathExpression)
    {
        $json = $this->jsonInspector->searchJsonPath($pathExpression);
        $this->asserter->variable($json)->isNotNull();
        $this->asserter->variable($json)->isNotEqualTo([]);
    }

    /**
     * @Then the JSON path expression :pathExpression should not have result
     */
    public function theJsonPathExpressionShouldNotHaveResult($pathExpression)
    {
        $json = $this->jsonInspector->searchJsonPath($pathExpression);
        if (is_array($json) && empty($json)) {
            $this->asserter->variable($json)->isEqualTo([]);
        } else {
            $this->asserter->variable($json)->isNull();
        }
    }

    private function evaluateJsonNodeValue($jsonNode)
    {
        return $this->jsonInspector->readJsonNodeValue($jsonNode);
    }

    private function evaluateExpectedValue($expectedValue)
    {
        if (in_array($expectedValue, array('true', 'false'))) {
            return filter_var($expectedValue, FILTER_VALIDATE_BOOLEAN);
        }

        if ($expectedValue === 'null') {
            return null;
        }

        return $expectedValue;
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

        $filename = $this->jsonSchemaBaseUrl.'/'.$filename;

        if (false === is_file($filename)) {
            throw new \RuntimeException(sprintf(
                'The JSON schema file "%s" doesn\'t exist',
                $filename
            ));
        }

        return realpath($filename);
    }

    private function assert(callable $assertion)
    {
        try {
            $assertion();
        } catch (\Exception $e) {
            throw new WrongJsonExpectation($e->getMessage(), $this->readJson(), $e);
        }
    }
}
