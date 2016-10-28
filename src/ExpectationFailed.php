<?php

namespace Rezzza\RestApiBehatExtension;

abstract class ExpectationFailed extends \Exception
{
    abstract function getContextText();

    /**
     * Returns exception message with additional context info.
     *
     * @return string
     */
    public function __toString()
    {
        try {
            $contextText = $this->pipeString($this->trimString($this->getContextText())."\n");
            $string = sprintf("%s\n\n%s", $this->getMessage(), $contextText);
        } catch (\Exception $e) {
            return $this->getMessage();
        }

        return $string;
    }

    /**
     * Prepends every line in a string with pipe (|).
     *
     * @param string $string
     *
     * @return string
     */
    protected function pipeString($string)
    {
        return '|  '.strtr($string, array("\n" => "\n|  "));
    }

    /**
     * Trims string to specified number of chars.
     *
     * @param string $string response content
     * @param int    $count  trim count
     *
     * @return string
     */
    protected function trimString($string, $count = 1000)
    {
        $string = trim($string);
        if ($count < mb_strlen($string)) {
            return mb_substr($string, 0, $count - 3).'...';
        }

        return $string;
    }
}
