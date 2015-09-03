<?php

namespace Rezzza\RestApiBehatExtension\Xml;

use Rezzza\RestApiBehatExtension\Response\ResponseStorage;

/**
 * Store the XML that we could analyze it in XmlContext
 */
class XmlStorage
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

    public function readXml($throwExceptions, $preserveWhitespace = true)
    {
        $content = $this->responseStorage->read();

        $dom = new \DomDocument();
        try {
            $dom->strictErrorChecking = false;
            $dom->validateOnParse = false;
            $dom->preserveWhiteSpace = $preserveWhitespace;
            $dom->loadXML($content, LIBXML_PARSEHUGE);
            $error = libxml_get_last_error();
            if (!empty($error)) {
                // https://bugs.php.net/bug.php?id=46465
                if ($error->message != 'Validation failed: no DTD found !') {
                    throw new \DomException($error->message . ' at line ' . $error->line);
                }
            }
        }
        catch(\DOMException $e) {
            if ($throwExceptions) {
                throw new \RuntimeException($e->getMessage());
            }
        }

        return $dom;
    }
}
