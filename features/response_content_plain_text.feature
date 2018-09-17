Feature: Response content value testing
  In order to test my API
  As a developper
  I want to be able to test response content

  Scenario: Testing if a plain text value of response content is desirable
    Given I add "content-type" header equal to "application/json"
    When I send a POST request to "response_content_plain_text_inspection" with body:
      """
      {
          "plain_text_value_i_want_to_have_in_response_body_content" : "b947c77c-ba52-11e8-9b2d-000000000000"          
      }
      """
    And the response content should not be empty
    And the response content should be equal to "b947c77c-ba52-11e8-9b2d-000000000000"
    And the response content should not be equal to "plain text value i have not sent in payload"

  Scenario: Testing if a plain text value of response content is empty
    Given I add "content-type" header equal to "application/json"
    When I send a POST request to "response_content_plain_text_inspection" with body:
      """
      {
          "plain_text_value_i_want_to_have_in_response_body_content" : ""          
      }
      """
    And the response content should be empty    
