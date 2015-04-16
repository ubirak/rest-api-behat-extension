# RestApiExtension for Behat
[Branch behat2](https://github.com/rezzza/rest-api-behat-extension/tree/behat2) : **Behat 2.x**
[Branch master](https://github.com/rezzza/rest-api-behat-extension/tree/master) : **Behat 3.x**

[![Build Status](https://travis-ci.org/rezzza/rest-api-behat-extension.png?branch=master)](https://travis-ci.org/rezzza/rest-api-behat-extension)

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/rezzza/rest-api-behat-extension/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/rezzza/rest-api-behat-extension/?branch=master)

For now only JSON api is supported to analyze response, but you could use REST part to perform request on any type of api.

## Install
Don't forget to load the extension and the context if needed in your `behat.yml` :
```yaml
default:
    extensions:
        Rezzza\RestApiBehatExtension\Extension:
            rest:
                base_url: http://localhost:8888
                store_response: true
    suites:
        default:
            contexts:
                - Rezzza\RestApiBehatExtension\RestApiContext
                - Rezzza\RestApiBehatExtension\Json\JsonContext
```

## Usage
You can use directly the `JsonContext` or `RestApiContext` by loading them in your behat.yml or use the `RestApiBrowser` and `JsonInspector` by adding them in the construct of your own context.

```php
<?php
/**/

use Rezzza\RestApiBehatExtension\Rest\RestApiBrowser;
use Rezzza\RestApiBehatExtension\Json\JsonInspector;

class FeatureContext implements Context
{
    private $restApiBrowser;

    private $jsonInspector;

    public function __construct(RestApiBrowser $restApiBrowser, JsonInspector $jsonInspector)
    {
        $this->restApiBrowser = $restApiBrowser;
        $this->jsonInspector = $jsonInspector;
    }
}
```
