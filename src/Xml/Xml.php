<?php

namespace Rezzza\RestApiBehatExtension\Xml;

class Xml
{
    private $content;

    public function __construct($content)
    {
        $this->parseXml($content);
        $this->content = $content;
    }

    public function read()
    {
        return $this->content;
    }

    public function pretty()
    {
        $dom = $this->parseXml($this->content);
        $dom->formatOutput = true;

        return $dom->saveXml();
    }

    public function isEqual(Xml $xml)
    {
        return $this->parseXml($this->content)->saveXml() === $this->parseXml($xml->read())->saveXml();
    }

    public function getNamespaces()
    {
        return simplexml_import_dom($this->parseXml($this->content))->getNamespaces(true);
    }

    public function xpath($element)
    {
        $dom = $this->parseXml($this->content);
        $xpath = new \DOMXpath($dom);
        $namespaces = $this->getNamespaces();
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

        return $xpath->query($element);
    }

    private function parseXml($content)
    {
        $dom = new \DomDocument();
        $dom->strictErrorChecking = false;
        $dom->validateOnParse = false;
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = false;
        $dom->loadXML($content, LIBXML_PARSEHUGE);
        $error = libxml_get_last_error();
        if (!empty($error)) {
            // https://bugs.php.net/bug.php?id=46465
            if ($error->message != 'Validation failed: no DTD found !') {
                throw new \DomException($error->message . ' at line ' . $error->line);
            }
        }

        return $dom;
    }
}
