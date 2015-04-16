<?php

namespace Rest;

namespace Rezzza\JsonApiBehatExtension\Rest;

interface ResponseStorage
{
    public function writeRawContent($rawContent);
}
