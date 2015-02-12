<?php

namespace Rezzza\JsonApiBehatExtension\Json;

interface JsonStorageAware
{
    public function setJsonStorage(JsonStorage $jsonStorage);
}
