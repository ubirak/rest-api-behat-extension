Feature: Test xml inspection payload
    In order to verify my xml response
    As a developper
    I want to be able to check xml content

    Background: load XML
        When I load XML:
            """
            <message attribute_key="1">
                <content>
                    <foo>bar</foo>
                </content>
                <items>
                    <item>0</item>
                    <item>1</item>
                    <item>2</item>
                    <item>3</item>
                </items>
            </message>
            """

    Scenario: Response should not be in XML
        When I load XML:
            """
            {"foo": "bar"}
            """
        Then the response should not be in XML

    Scenario: Response should be in XML
        Then the response should be in XML

    Scenario: Xml elements
        Then the XML element "//message/content" should exists
        And the XML element "content" should exists

        And the XML element "//massage/content" should not exists
        And the XML element "//contento" should not exists

        And the XML element "//message/content/foo" should be equal to "bar"
        And the XML element "//message/content/foo" should not be equal to "baz"

        And the XML element "//message/content/foo" should contain "ba"
        And the XML element "//message/content/foo" should not contain "bazz"

        And the XML attribute "attribute_key" on element "//message" should exist
        And the XML attribute "attribute_key" on element "//message/content" should not exist

        And the XML attribute "attribute_key" on element "//message" should be equal to "1"
        And the XML attribute "attribute_key" on element "//message" should not be equal to "1337"

        And the XML element "//message/items" should have 4 elements

    Scenario: Xml response
        Then the XML response should be equal to:
        """
        <message attribute_key="1">
            <content>
                <foo>bar</foo>
            </content>
            <items>
                <item>0</item>
                <item>1</item>
                <item>2</item>
                <item>3</item>
            </items>
        </message>
        """
