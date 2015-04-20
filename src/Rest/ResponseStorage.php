<?php

namespace Rest;

namespace Rezzza\RestApiBehatExtension\Rest;

interface ResponseStorage
{
    public function writeRawContent($rawContent);
}
