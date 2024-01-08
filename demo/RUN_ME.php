<?php

include '../src/CSSParser.php';

$html = file_get_contents('sample.html');

$cssParser = new CSSParser();
$modifiedHtml = $cssParser->parse($html);

echo $modifiedHtml;
