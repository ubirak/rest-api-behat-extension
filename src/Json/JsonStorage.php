<?php

namespace Rezzza\RestApiBehatExtension\Json;

/**
 * Store the JSON that we could analyze it in JsonContext
 */
class JsonStorage
{
    private $rawContent;

    public function writeRawContent($rawContent)
    {
        $this->rawContent = $rawContent;
    }

    public function readJson()
    {
        if ($this->rawContent === null) {
            throw new \LogicException('No content defined. You should use JsonContainer::setRawContent method to inject content you want to analyze');
        }

        return new Json($this->rawContent);
    }
}
