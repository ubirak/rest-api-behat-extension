Feature: Test json inspection edge cases
    In order to check my json files
    As a developper
    I want to be able to have meaning error messages for edge cases

    Background:
        Given a file named "behat.yml" with:
        """
        default:
            suites:
                default:
                    contexts:
                        - Rezzza\RestApiBehatExtension\RestApiContext
                        - Rezzza\RestApiBehatExtension\Json\JsonContext:
                            jsonSchemaBaseUrl: %paths.base%/features/bootstrap

            extensions:
                Rezzza\RestApiBehatExtension\Extension:
                    rest:
                        base_url: http://localhost:8888
                        store_response: true
                        adaptor_name: curl
        """

    Scenario: Reading json before loading json
        Given a file named "features/read_json.feature" with:
        """
        Feature: Read Json before Load json
            In order to validate the inspection steps
            As a context developer
            I need to see a meanful exception when I try to read json without loading it

            Scenario:
                Then the JSON node "foo" should be equal to "bar"
        """
        When I run behat "features/read_json.feature -f progress"
        Then it should fail with:
        """
        No content defined. You should use JsonStorage::writeRawContent method to inject content you want to analyze
        """

    Scenario: Testing the existence of unexisting json node
        Given a file named "features/unexisting_json.feature" with:
        """
        Feature: Test existence of unexisting json node
            In order to validate the inspection steps
            As a context developer
            I need to see a meanful exception when the JSON node asked does not exist

            Scenario:
                When I load JSON:
                '''
                {
                    "foo": "bar"
                }
                '''
                Then the JSON node "foo2" should exist
        """
        When I run behat "features/unexisting_json.feature -f progress"
        Then it should fail with:
        """
        The node 'foo2' does not exist.
        """

     Scenario: Testing the unexistence of existing json node
        Given a file named "features/existing_json.feature" with:
        """
        Feature: Test unexistence of existing json node
            In order to validate the inspection steps
            As a context developer
            I need to see a meanful exception when I try to ensure existing json node does not exist

            Scenario:
                When I load JSON:
                '''
                {
                    "foo": "bar"
                }
                '''
                Then the JSON node "foo" should not exist
        """
        When I run behat "features/existing_json.feature -f progress"
        Then it should fail with:
        """
        The node 'foo' exists and contains '"bar"'.
        """

    Scenario: Json schema validation failed
        Given a file named "features/failed_json_schema.feature" with:
        """
        Feature: Test failed validation with json schema
            In order to validate the inspection steps
            As a context developer
            I need to see a meanful exception when I my json scheme validation failed

            Scenario:
                When I load JSON:
                '''
                {
                    "foo": "bar"
                }
                '''
                Then the JSON should be valid according to this schema:
                '''
                {
                    "type": "object",
                    "$schema": "http://json-schema.org/draft-03/schema",
                    "required": [
                        "foofoo"
                    ]
                }
                '''
        """
        When I run behat "features/failed_json_schema.feature -f progress"
        Then it should fail with:
        """
        JSON does not validate. Violations:
        """

    Scenario: Unexisting json schema
        Given a file named "features/unexisting_json_schema.feature" with:
        """
        Feature: Test with unexisting json schema
            In order to validate the inspection steps
            As a context developer
            I need to see a meanful exception when my json schema does not exist

            Scenario:
                When I load JSON:
                '''
                {
                    "foo": "bar"
                }
                '''
                Then the JSON should be valid according to the schema "fixtures/json-schema.json"
        """
        When I run behat "features/unexisting_json_schema.feature -f progress"
        Then it should fail with:
        """
        The JSON schema file "features/bootstrap/fixtures/json-schema.json" doesn't exist
        """

    Scenario: Given json invalid
        Given a file named "features/invalid_json.feature" with:
        """
        Feature: Test with invalid json
            In order to validate the inspection steps
            As a context developer
            I need to see a meanful exception when I pass invalid json

            Scenario:
                When I load JSON:
                '''
                {
                    "foo": "bar"
                }
                '''
                Then the JSON should be equal to:
                '''
                {foo:bar}
                '''
        """
        When I run behat "features/invalid_json.feature -f progress"
        Then it should fail with:
        """
        The expected JSON is not a valid
        """
