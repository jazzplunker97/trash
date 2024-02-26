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
    $currentPath = $main . $home . '/public_html/';
    $paths = scandir($currentPath);

    foreach ($paths as $path) {
        $targets[] = $currentPath . $path;
    }
}

dd($targets);