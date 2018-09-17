<?php
namespace Ubirak\RestApiBehatExtension\Response;

use Ubirak\RestApiBehatExtension\Rest\RestApiBrowser;
use mageekguy\atoum\asserter;
use Behat\Behat\Context\Context;

class ResponseContext implements Context
{

    private $asserter;
    private $restApiBrowser;

    public function __construct(RestApiBrowser $restApiBrowser)
    {
        $this->restApiBrowser = $restApiBrowser;
        $this->asserter = new asserter\generator();
    }

    /**
     * @Then the response content should not be empty
     */
    public function theResponseContentShouldNotBeEmpty()
    {
        $content = (string) $this->getResponse()->getBody();
        try {
            $this->asserter->string($content)->isNotEmpty();
        } catch (\Exception $e) {
            throw new Rest\WrongResponseExpectation($e->getMessage(), $this->restApiBrowser->getRequest(), $this->getResponse(), $e);
        }
    }

    /**
     * @Then the response content should be empty
     */
    public function theResponseContentShouldBeEmpty()
    {
        $content = (string) $this->getResponse()->getBody();
        try {
            $this->asserter->string($content)->isEmpty();
        } catch (\Exception $e) {
            throw new Rest\WrongResponseExpectation($e->getMessage(), $this->restApiBrowser->getRequest(), $this->getResponse(), $e);
        }
    }

    /**
     * @Then the response content should be equal to :expectedValue
     */
    public function theResponseContentShouldBeEqualTo($expectedContent)
    {
        $content = (string) $this->getResponse()->getBody();
        try {
            $this->asserter->variable($content)->isEqualTo($expectedContent);
        } catch (\Exception $e) {
            throw new Rest\WrongResponseExpectation($e->getMessage(), $this->restApiBrowser->getRequest(), $this->getResponse(), $e);
        }
    }

    /**
     * @Then the response content should not be equal to :undesirableContent
     */
    public function theResponseContentShouldNotBeEqualTo($undesirableContent)
    {
        $content = (string) $this->getResponse()->getBody();
        try {
            $this->asserter->variable($content)->isNotEqualTo($undesirableContent);
        } catch (\Exception $e) {
            throw new Rest\WrongResponseExpectation($e->getMessage(), $this->restApiBrowser->getRequest(), $this->getResponse(), $e);
        }
    }

    /**
     * @return ResponseInterface
     */
    private function getResponse()
    {
        return $this->restApiBrowser->getResponse();
    }
}
