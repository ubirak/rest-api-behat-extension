<?php

namespace Rezzza\RestApiBehatExtension\Rest;

interface ResponseStorage
{
    public function writeRawContent($rawContent);
}
