<?php

namespace Rezzza\RestApiBehatExtension\Json;

/**
 * Use https://github.com/jmespath/jmespath.php
 * alternative could be https://github.com/FlowCommunications/JSONPath
 */
class JsonSearcher
{
    public function search(Json $json, $pathExpression)
    {
        return \JmesPath\Env::search($pathExpression, $json->getRawContent());
    }
}
