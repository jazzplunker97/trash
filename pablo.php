<?php

function dd(...$data) {
    echo '<pre>';
    var_dump($data);
    echo '</pre>';
    die;
}

$main = '/var/sentora/hostdata/';
$homes = scandir($main);

$domains = file_get_contents('./domains.txt');
$domains = explode(PHP_EOL, $domains);

$targets = [];
foreach ($homes as $key => $home) {
    $currentPath = $main . $home . '/public_html/';
    $paths = scandir($currentPath);

    foreach ($paths as $path) {

        $currentDomain = str_replace('_', '.', $path);
        if (in_array($currentDomain, $domains)) {
            $targets[] = $currentPath . $path;
        }
    }
}

dd($targets);