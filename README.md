# JsonApiExtension for Behat
For now we only support **Behat 2.x**

[![Build Status](https://travis-ci.org/rezzza/json-api-behat-extension.png?branch=master)](https://travis-ci.org/rezzza/json-api-behat-extension)

## Install
Don't forget to load the extension in your `behat.yml` :
```yaml
default:
    extensions:
        Rezzza\JsonApiBehatExtension\Extension: ~
```

## Usage
Have a look to the [FeatureContext::__construct](https://github.com/rezzza/json-api-behat-extension/blob/master/features/bootstrap/FeatureContext.php#L28)

* `JsonContext` can be used alone without HTTP calls with the step `I load JSON:`
* `RestApiContext` can be used alone if you pass `false` as third argument.
