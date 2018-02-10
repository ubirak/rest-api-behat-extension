Feature: Test request to API sent like a HTML form
  In order to test my API
  As a developper
  I want to be able to perform HTTP request with HTML form data

  Scenario: Sending POST request as a HTML form
    When I send a POST request to "post-html-form" as HTML form with body:
      | object | name           | value |
      | field  | username       | pablo |
      | field  | password       | money |
      | field  | terms_accepted | 1     |
    Then the rest response status code should be 200
    And the response should be in JSON
    And the JSON node "content_type_header_value" should contain "application/x-www-form-urlencoded"
    And the JSON node "post_fields_count" should be equal to "3"
    And the JSON node "post_fields.username" should be equal to "pablo"
    And the JSON node "post_fields.password" should be equal to "money"
    And the JSON node "post_fields.terms_accepted" should be equal to "1"

  Scenario: Sending POST request as a HTML form with files
    When I send a POST request to "post-html-form-with-files" as HTML form with body:
      | object | name           | value                                        |
      | field  | username       | pablo                                        |
      | field  | password       | money                                        |
      | field  | terms_accepted | 1                                            |
      | file   | test-img       | features/bootstrap/fixtures/test-img.jpg     |
      | file   | json-schema    | features/bootstrap/fixtures/json-schema.json |
    Then the rest response status code should be 200
    And the response should be in JSON
    And the JSON node "content_type_header_value" should contain "multipart/form-data"
    And the JSON node "post_fields_count" should be equal to "3"
    And the JSON node "post_files_count" should be equal to "2"
    And the JSON node "post_fields.username" should be equal to "pablo"
    And the JSON node "post_fields.password" should be equal to "money"
    And the JSON node "post_fields.terms_accepted" should be equal to "1"
