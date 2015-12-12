<?php

namespace Rezzza\RestApiBehatExtension\Json;

use JsonSchema\RefResolver;
use JsonSchema\Validator;
use JsonSchema\Uri\UriRetriever;

class JsonSchema extends Json
{
    private $uri;

    /**
     * @param string $uri
     */
    public function __construct($content, $uri = null)
    {
        $this->uri = $uri;
        parent::__construct($content);
    }

    public function resolve(RefResolver $resolver)
    {
        if (!$this->hasUri()) {
            throw new \LogicException('Cannot resolve JsonSchema without uri parameter');
        }

        $resolver->resolve($this->getRawContent(), $this->uri);

        return $this;
    }

    public function validate(Json $json, Validator $validator)
    {
        if ($this->hasUri()) {
            $this->resolve(new RefResolver(new UriRetriever));
        }

        $validator->check($json->getRawContent(), $this->getRawContent());

        if (!$validator->isValid()) {
            $msg = "JSON does not validate. Violations:" . PHP_EOL;
            foreach ($validator->getErrors() as $error) {
                $msg .= sprintf("  - [%s] %s" . PHP_EOL, $error['property'], $error['message']);
            }
            throw new \Exception($msg);
        }

        return true;
    }

    private function hasUri()
    {
        return null !== $this->uri;
    }
}
