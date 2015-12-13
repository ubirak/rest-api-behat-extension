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
            "username" : "pablo",
            "password": "money"
        }
        """
        Then the JSON node "method" should be equal to "POST"
        And the JSON node "headers.content-type[0]" should be equal to "application/json"
        And the JSON node "username" should be equal to "pablo"
        And the JSON node "password" should be equal to "money"

    Scenario: Sending POST request with form data
        When I send a POST request to "echo" with form data:
            | username | pablo |
            | password | money |
        Then the response status code should be 200
        And the JSON node "username" should be equal to "pablo"
        And the JSON node "password" should be equal to "money"

    Scenario: Add same header 2 times
        Given I add "header" header equal to "value"
        Given I add "header" header equal to "value2"
        When I send a POST request to "echo"
        Then the response status code should be 200
        And the JSON node "headers.header" should have 1 element
        And the JSON node "headers.header[0]" should be equal to "value, value2"

    Scenario: Set same header 2 times
        Given I set "header" header equal to "value"
        Given I set "header" header equal to "value2"
        When I send a POST request to "echo"
        Then the response status code should be 200
        And the JSON node "headers.header" should have 1 element
        And the JSON node "headers.header[0]" should be equal to "value2"
