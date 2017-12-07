<?php

namespace Ubirak\RestApiBehatExtension\Rest;

interface ResponseStorage
{
    public function writeRawContent($rawContent);
}
