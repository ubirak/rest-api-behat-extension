# RestApiExtension for Behat
For now we only support **Behat 2.x**

[![Build Status](https://travis-ci.org/rezzza/rest-api-behat-extension.png?branch=behat-2.x)](https://travis-ci.org/rezzza/rest-api-behat-extension)

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/rezzza/rest-api-behat-extension/badges/quality-score.png?b=behat-2.x)](https://scrutinizer-ci.com/g/rezzza/rest-api-behat-extension/?branch=behat-2.x)

## Install
Don't forget to load the extension in your `behat.yml` :
```yaml
default:
    extensions:
        Rezzza\RestApiBehatExtension\Extension: ~
```

## Usage
Have a look to the [FeatureContext::__construct](https://github.com/rezzza/rest-api-behat-extension/blob/behat-2.x/features/bootstrap/FeatureContext.php#L28)

* `JsonContext` can be used alone without HTTP calls with the step `I load JSON:`
* `RestApiContext` can be used alone if you pass `false` as third argument.
