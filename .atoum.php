<?php
use atoum\atoum;

$runner->addTestsFromDirectory(__DIR__.'/tests/Units');

$script
    ->addDefaultReport()
    ->addField(new atoum\report\fields\runner\result\logo())
;

$script->bootstrapFile(__DIR__ . DIRECTORY_SEPARATOR . '.atoum.bootstrap.php');
