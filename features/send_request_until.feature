Feature: Send request until
    In order to test async system
    As a developer
    I should be able to try to send HTTP request until it validates my requirement

    Background:
        Given a file named "behat.yml" with:
            """
            default:
                extensions:
                    Ubirak\RestApiBehatExtension\Extension:
                        rest:
                            base_url: http://localhost:8888
                suites:
                    default:
                        contexts:
                            - FeatureContext
                            - Ubirak\RestApiBehatExtension\RestApiContext
            """

    Scenario: Send request until it works
        Given a file named "features/send_request_until.feature" with:
            """
            Feature: Send request until
                In order to deal with async system
                As a feature runner
                I need to continue to send request until it works

                Scenario: Send request that could fail
                    When I call my microservice
                    And I call my microservice
                    And I call my microservice
                    Then print response
            """
        And a file named "features/bootstrap/FeatureContext.php" with:
            """
            <?php
            use Behat\Behat\Context\Context;
            use Ubirak\RestApiBehatExtension\Rest\RestApiBrowser;
            use atoum\atoum\asserter;

            class FeatureContext implements Context
            {
                private $restApiBrowser;

                private $asserter;

                public function __construct(RestApiBrowser $restApiBrowser)
                {
                    $this->restApiBrowser = $restApiBrowser;
                    $this->asserter = new asserter\generator;
                }

                /**
                 * @When I call my microservice
                 */
                public function callMyMicroservice()
                {
                    $restApiBrowser = $this->restApiBrowser;
                    $asserter = $this->asserter;
                    $this->restApiBrowser->sendRequestUntil(
                        'GET', 'error_random', null, function () use ($restApiBrowser, $asserter) {
                            $asserter->integer($restApiBrowser->getResponse()->getStatusCode())->isEqualTo(200);
                        }
                    );
                }
            }
            """
        When I run behat "features/send_request_until.feature"
        Then it should pass with:
            """
            200 OK
            """

    Scenario: Send request that will fail always
        Given a file named "features/send_request_until.feature" with:
            """
            Feature: Send request until
                In order to deal with async system
                As a feature runner
                I need to continue to send request until it works

                Scenario: Send request that fail
                    When I call my microservice
                    Then print response
            """
        And a file named "features/bootstrap/FeatureContext.php" with:
            """
            <?php
            use Behat\Behat\Context\Context;
            use Ubirak\RestApiBehatExtension\Rest\RestApiBrowser;
            use atoum\atoum\asserter;

            class FeatureContext implements Context
            {
                private $restApiBrowser;

                private $asserter;

                public function __construct(RestApiBrowser $restApiBrowser)
                {
                    $this->restApiBrowser = $restApiBrowser;
                    $this->asserter = new asserter\generator;
                }

                /**
                 * @When I call my microservice
                 */
                public function callMyMicroservice()
                {
                    $restApiBrowser = $this->restApiBrowser;
                    $asserter = $this->asserter;
                    $this->restApiBrowser->sendRequestUntil(
                        'GET', 'always_error', null, function () use ($restApiBrowser, $asserter) {
                            $asserter->integer($restApiBrowser->getResponse()->getStatusCode())->isEqualTo(200);
                        },
                        5
                    );
                }
            }
            """
        When I run behat "features/send_request_until.feature"
        Then it should fail with:
            """
            integer(502) is not equal to integer(200)
            """
