<?php

namespace Rezzza\RestApiBehatExtension\Json;

interface JsonStorageAware
{
    public function setJsonStorage(JsonStorage $jsonStorage);
}
