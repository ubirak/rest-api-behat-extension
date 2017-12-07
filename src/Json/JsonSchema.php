<?php

namespace Ubirak\RestApiBehatExtension\Json;

use JsonSchema\SchemaStorage;
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

    public function validate(Json $json, Validator $validator, SchemaStorage $schemaStorage)
    {
        $schema = $schemaStorage->resolveRef('file://'.realpath($this->filename));
        $data = $json->getRawContent();

        $validator->check($data, $schema);

        if (!$validator->isValid()) {
            $msg = 'JSON does not validate. Violations:'.PHP_EOL;
            foreach ($validator->getErrors() as $error) {
                $msg .= sprintf('  - [%s] %s'.PHP_EOL, $error['property'], $error['message']);
            }
            throw new \Exception($msg);
        }

        return true;
    }
}
