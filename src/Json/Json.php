<?php

namespace Ubirak\RestApiBehatExtension\Json;

use Symfony\Component\PropertyAccess\PropertyAccessor;

class Json
{
    private $content;

    public function __construct($content, $encodedAsString = true)
    {
        $this->content = true === $encodedAsString ? $this->decode((string) $content) : $content;
    }

    public static function fromRawContent($content)
    {
        return new static($content, false);
    }

    public function read($expression, PropertyAccessor $accessor)
    {
        if (is_array($this->content)) {
            $expression = preg_replace('/^root/', '', $expression);
        } else {
            $expression = preg_replace('/^root./', '', $expression);
        }

        // If root asked, we return the entire content
        if (strlen(trim($expression)) <= 0) {
            return $this->content;
        }

        return $accessor->getValue($this->content, $expression);
    }

    public function getRawContent()
    {
        return $this->content;
    }

    public function encode($pretty = true)
    {
        if (true === $pretty && defined('JSON_PRETTY_PRINT')) {
            return json_encode($this->content, JSON_PRETTY_PRINT);
        }

        return json_encode($this->content);
    }

    public function __toString()
    {
        return $this->encode(false);
    }

    private function decode($content)
    {
        $result = json_decode($content);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception(
                sprintf('The string "%s" is not valid json', $content)
            );
        }

        return $result;
    }
}
