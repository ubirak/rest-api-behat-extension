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

    /**
     * @return Xml
     */
    public function readXml($throwExceptions)
    {
        try {
            return new Xml($this->responseStorage->read());
        } catch(\DOMException $e) {
            if ($throwExceptions) {
                throw new \RuntimeException($e->getMessage());
            }
        }
    }
}
