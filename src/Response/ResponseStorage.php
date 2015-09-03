<?php

namespace Rezzza\RestApiBehatExtension\Response;

class ResponseStorage
{
    private $rawContent;

    public function writeRawContent($rawContent)
    {
        $this->rawContent = $rawContent;
    }

    public function read()
    {
        if ($this->rawContent === null) {
            throw new \LogicException('No content defined. You should use ResponseStorage::writeRawContent method to inject content you want to analyze');
        }

        return $this->rawContent;
    }
}
