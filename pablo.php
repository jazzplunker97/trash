<?php

function dd(...$data) {
    echo '<pre>';
    var_dump($data);
    echo '</pre>';
    die;
}

$main = '/var/sentora/hostdata/';
$homes = scandir($main);

$targets = [];
foreach ($homes as $key => $home) {
    $targets[] = $main . $home;
}

dd($targets);