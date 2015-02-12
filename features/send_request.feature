Feature: Test send API request
    In order to test my API
    As a developper
    I want to be able to perform HTTP request

    Scenario: Sending GET request to non existing ressource should lead to 404
        When I send a GET request to "simpson.json"
        Then the response status code should be 404

    Scenario: Sending GET request to existing ressource should lead to 200
        When I send a GET request to "echo"
        Then the response status code should be 200

    Scenario: Sending POST request with body
        Given I add "content-type" header equal to "application/json"
        When I send a POST request to "echo" with body:
        """
        {
            "name" : "name",
            "pass": "pass"
        }
        """
        Then the JSON node "method" should be equal to "POST"
        And the JSON node "headers.content-type[0]" should be equal to "application/json"
