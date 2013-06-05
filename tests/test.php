<?php

include_once 'specification_parser.php';

$p = new SpecificationParser();
$specification = $p->parse_file($argv[1]);
$specification->run();