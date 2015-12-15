<?php

namespace Rezzza\RestApiBehatExtension\Xml;

use Behat\Behat\Context\BehatContext;
use Behat\Gherkin\Node\PyStringNode;
use Rezzza\RestApiBehatExtension\Response\ResponseStorage;
use Rezzza\RestApiBehatExtension\Response\ResponseStorageAware;
use mageekguy\atoum\asserter\generator as asserter;

class XmlContext extends BehatContext implements ResponseStorageAware
{
    private $asserter;

    private $xmlStorage;

    public function __construct(asserter $asserter)
    {
        $this->asserter = $asserter;
    }

    public function setResponseStorage(ResponseStorage $responseStorage)
    {
        $this->xmlStorage = new XmlStorage($responseStorage);
    }

    /**
     * @When /^I load XML:$/
     */
    public function iLoadXml(PyStringNode $jsonContent)
    {
        $this->xmlStorage->writeRawContent($jsonContent);
    }

    /**
     * Checks that the response is correct XML
     *
     * @Then /^the response should be in XML$/
     */
    public function theResponseShouldBeInXml()
    {
        $this->xmlStorage->readXml(true);
    }

    /**
     * Checks that the response is not correct XML
     *
     * @Then /^the response should not be in XML$/
     */
    public function theResponseShouldNotBeInXml()
    {
        try {
            $this->xmlStorage->readXml(true);
        } catch (\Exception $e) {
        }

        if (!isset($e)) {
            throw new \Exception("The response is in XML");
        }
    }

    /**
     * Checks that the specified XML element exists
     *
     * @param string $element
     * @throws \Exception
     * @return \DomNodeList
     *
     * @Then /^the XML element "(?P<element>[^"]*)" should exists?$/
     */
    public function theXmlElementShouldExist($element)
    {
        $elements = $this->xpath($element);
        if ($elements->length == 0) {
            throw new \Exception(sprintf("The element '%s' does not exist.", $element));
        }
        return $elements;
    }

    /**
     * Checks that the specified XML element does not exist
     *
     * @Then /^the XML element "(?P<element>[^"]*)" should not exists?$/
     */
    public function theXmlElementShouldNotExist($element)
    {
        $elements = $this->xpath($element);
        if ($elements->length != 0) {
            throw new \Exception(sprintf("The element '%s' exists.", $element));
        }
    }


    /**
     * @Then /^the XML response should be equal to:$/
     */
    public function theResponseXmlShouldBeEqualTo(PyStringNode $expected)
    {
        $expected = str_replace('\\"', '"', $expected);
        $actual   = $this->xmlStorage->readXml(false)->saveXml();

        $message = sprintf(
            'The string "%s" is not equal to the response of the current page: %s',
            $expected,
            $actual
        );

        $expected = new \SimpleXMLElement($expected);
        $actual = new \SimpleXMLElement($actual);

        if ($expected->asXML() != $actual->asXML()) {
            throw new \Exception($message);
        }
    }


    /**
     * Checks that the specified XML element is equal to the given value
     *
     * @Then /^the XML element "(?P<element>(?:[^"]|\\")*)" should be equal to "(?P<text>[^"]*)"$/
     */
    public function theXmlElementShouldBeEqualTo($element, $text)
    {
        $elements = $this->theXmlElementShouldExist($element);
        $actual = $elements->item(0)->nodeValue;
        if ($text != $actual) {
            throw new \Exception(sprintf("The element value is `%s`", $actual));
        }
    }

    /**
     * Checks that the specified XML element is not equal to the given value
     *
     * @Then /^the XML element "(?P<element>(?:[^"]|\\")*)" should not be equal to "(?P<text>[^"]*)"$/
     */
    public function theXmlElementShouldNotBeEqualTo($element, $text)
    {
        $elements = $this->theXmlElementShouldExist($element);
        $actual = $elements->item(0)->nodeValue;
        if ($text == $actual) {
            throw new \Exception(sprintf("The element value is `%s`", $actual));
        }
    }

    /**
     * Checks that the XML attribute on the specified element exists
     *
     * @Then /^the XML attribute "(?P<attribute>[^"]*)" on element "(?P<element>(?:[^"]|\\")*)" should exists?$/
     */
    public function theXmlAttributeShouldExist($attribute, $element)
    {
        $elements = $this->theXmlElementShouldExist("{$element}[@{$attribute}]");
        $actual = $elements->item(0)->getAttribute($attribute);
        if (empty($actual)) {
            throw new \Exception(sprintf("The attribute value is `%s`", $actual));
        }
        return $actual;
    }

    /**
     * Checks that the XML attribute on the specified element does not exist
     *
     * @Then /^the XML attribute "(?P<attribute>[^"]*)" on element "(?P<element>(?:[^"]|\\")*)" should not exists?$/
     */
    public function theXmlAttributeShouldNotExist($attribute, $element)
    {
        try {
            $elements = $this->theXmlElementShouldExist("{$element}[@{$attribute}]");
            $actual = $elements->item(0)->getAttribute($attribute);
            if (!empty($actual)) {
                throw new \Exception(sprintf("The element '%s' exists and contains '%s'.", $element , $elements));
            }
        }
        catch (\Exception $e) {
        }
    }

    /**
     * Checks that the XML attribute on the specified element is equal to the given value
     *
     * @Then /^the XML attribute "(?P<attribute>[^"]*)" on element "(?P<element>(?:[^"]|\\")*)" should be equal to "(?P<text>[^"]*)"$/
     */
    public function theXmlAttributeShouldBeEqualTo($attribute, $element, $text)
    {
        $actual = $this->theXmlAttributeShouldExist($attribute, $element);
        if ($text != $actual) {
            throw new \Exception(sprintf("The attribute value is `%s`", $actual));
        }
    }

    /**
     * Checks that the XML attribute on the specified element is not equal to the given value
     *
     * @Then /^the XML attribute "(?P<attribute>[^"]*)" on element "(?P<element>(?:[^"]|\\")*)" should not be equal to "(?P<text>[^"]*)"$/
     */
    public function theXmlAttributeShouldNotBeEqualTo($attribute, $element, $text)
    {
        $actual = $this->theXmlAttributeShouldExist($attribute, $element);
        if ($text === $actual) {
            throw new \Exception(sprintf("The attribute value is `%s`", $actual));
        }
    }

    /**
     * Checks that the given XML element has N child element(s)
     *
     * @Then /^the XML element "(?P<element>[^"]*)" should have (?P<nth>\d+) elements?$/
     */

    public function theXmlElementShouldHaveNChildElements($element, $nth)
    {
        $elements = $this->theXmlElementShouldExist($element);
        $length = 0;
        foreach ($elements->item(0)->childNodes as $node) {
            if ($node->hasAttributes() || (trim($node->nodeValue) != '')) {
                ++$length;
            }
        }

        $this->asserter
            ->integer((int) $nth)
            ->isEqualTo($length);
    }

    /**
     * Checks that the given XML element contains the given value
     *
     * @Then /^the XML element "(?P<element>[^"]*)" should contain "(?P<text>[^"]*)"$/
     */
    public function theXmlElementShouldContain($element, $text)
    {
        $elements = $this->theXmlElementShouldExist($element);

        $this->asserter
            ->phpString($elements->item(0)->nodeValue)
            ->contains($text);
    }

    /**
     * Checks that the given XML element does not contain the given value
     *
     * @Then /^the XML element "(?P<element>[^"]*)" should not contain "(?P<text>[^"]*)"$/
     */
    public function theXmlElementShouldNotContain($element, $text)
    {
        $elements = $this->theXmlElementShouldExist($element);

        $this->asserter
            ->phpString($elements->item(0)->nodeValue)
            ->notContains($text);
    }

    /**
     * Checks that the XML uses the specified namespace
     *
     * @Then /^[Tt]he XML should use the namespace "(?P<namespace>[^"]*)"$/
     */
    public function theXmlShouldUseTheNamespace($namespace)
    {
        $namespaces = $this->getNamespaces();
        if (!in_array($namespace, $namespaces)) {
            throw new \Exception(sprintf("The namespace '%s' is not used", $namespace));
        }
    }

    /**
     * Checks that the XML does not use the specified namespace
     *
     * @Then /^[Tt]he XML should not use the namespace "(?P<namespace>[^"]*)"$/
     */
    public function theXmlShouldNotUseTheNamespace($namespace)
    {
        $namespaces = $this->getNamespaces();
        if (in_array($namespace, $namespaces)) {
            throw new \Exception(sprintf("The namespace '%s' is used", $namespace));
        }
    }

    /**
     * Optimistically (ignoring errors) attempt to pretty-print the last XML response
     *
     * @Then /^print last XML response$/
     */
    public function printLastXmlResponse()
    {
        $dom = $this->xmlStorage->readXml(false, false);
        $dom->formatOutput = true;
        $content = $dom->saveXML();
        $this->printDebug($content);
    }

    /**
     * @param string $element
     * @return \DomNodeList
     */
    public function xpath($element)
    {
        $dom = $this->xmlStorage->readXml(false);
        $xpath = new \DOMXpath($dom);
        $namespaces = $this->getNamespaces($dom);
        $defaultNamespaceUri = $dom->lookupNamespaceURI(null);
        $defaultNamespacePrefix = $defaultNamespaceUri ? $dom->lookupPrefix($defaultNamespaceUri) : null;
        foreach ($namespaces as $prefix => $namespace) {
            if (empty($prefix) && empty($defaultNamespacePrefix) && !empty($defaultNamespaceUri)) {
                $prefix = 'rootns';
            }
            $xpath->registerNamespace($prefix, $namespace);
        }
        // "fix" queries to the default namespace if any namespaces are defined
        if (!empty($namespaces) && empty($defaultNamespacePrefix) && !empty($defaultNamespaceUri)) {
            for ($i=0; $i < 2; ++$i) {
                $element = preg_replace('/\/(\w+)(\[[^]]+\])?\//', '/rootns:$1$2/', $element);
            }
            $element = preg_replace('/\/(\w+)(\[[^]]+\])?$/', '/rootns:$1$2', $element);
        }
        $elements = $xpath->query($element);
        return ($elements === false) ? new \DOMNodeList() : $elements;
    }
    /**
     * @return \SimpleXMLElement
     */
    private function getSimpleXml()
    {
        return simplexml_import_dom($this->xmlStorage->readXml(false));
    }
    /**
     * @return array
     */
    private function getNamespaces()
    {
        return $this->getSimpleXml()
            ->getNamespaces(true)
            ;
    }
    /**
     * @BeforeScenario
     */
    public function beforeScenario()
    {
        libxml_clear_errors();
        libxml_use_internal_errors(true);
    }
}
