<?php

namespace Rezzza\RestApiBehatExtension\Response;

interface ResponseStorageAware
{
    public function setResponseStorage(ResponseStorage $responseStorage);
}
