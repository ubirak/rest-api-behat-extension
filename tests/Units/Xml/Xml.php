<?php

namespace Rezzza\RestApiBehatExtension\Tests\Units\Xml;

use atoum;

class Xml extends atoum
{
    public function test_it_compare_xml_content_with_different_whitespaces()
    {
        $this
            ->given(
                $xml1 = $this->newTestedInstance("<tests>\n<test><![CDATA[coucou]]></test>\n</tests>"),
                $xml2 = $this->newTestedInstance("<tests><test><![CDATA[coucou]]></test></tests>")
            )
            ->when(
                $result = $xml1->isEqual($xml2)
            )
            ->then
                ->boolean($result)->isTrue()
        ;
    }

    public function test_it_make_content_pretty()
    {
        $this
            ->given(
                $xml = $this->newTestedInstance("<tests><test><![CDATA[coucou]]></test></tests>")
            )
            ->when(
                $pretty = $xml->pretty()
            )
            ->then
                ->phpString($pretty)->isEqualTo('<?xml version="1.0"?>
<tests>
  <test><![CDATA[coucou]]></test>
</tests>
')
        ;
    }
}
