<?php

$domains = file_get_contents('./domains.txt');
$domains = explode(PHP_EOL, $domains);

var_dump($domains);