<?php

namespace Rezzza\RestApiBehatExtension\Json;

use JsonSchema\RefResolver;
use JsonSchema\Validator;

class JsonSchema
{
    private $filename;

    /**
     * @param string $filename
     */
    public function __construct($filename)
    {
        $this->filename = $filename;
    }

    public function validate(Json $json, Validator $validator, RefResolver $refResolver)
    {
        $schema = $refResolver->resolve('file://' . realpath($this->filename));

        $validator->check($json->getRawContent(), $schema);

        if (!$validator->isValid()) {
            $msg = "JSON does not validate. Violations:" . PHP_EOL;
            foreach ($validator->getErrors() as $error) {
                $msg .= sprintf("  - [%s] %s" . PHP_EOL, $error['property'], $error['message']);
            }
            throw new \Exception($msg);
        }

        return true;
    }
}
