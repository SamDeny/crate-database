<?php declare(strict_types=1);

use Crate\Database\Schema;

// Load Autoloader
require_once __DIR__ . '/vendor/autoload.php';

// Load Test Configuration
global $config;
$config = require __DIR__ . '/config.php';

// Drop-In Config function
function config(string $key) {
    global $config;

    if ($key === 'database.crate') {
        return $config['crate'];
    } else if ($key === 'database.default') {
        return $config['default'];
    } else if (str_starts_with($key, 'database.drivers.')) {
        return $config['drivers'][substr($key, 17)] ?? null;
    } else {
        return null;
    }
}

?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <style type="text/css">
        *, *:before, *:after {
            box-sizing: border-box;
        }
        html, body {
            color: #E4E4E7;
            margin: 0;
            padding: 0;
            display: block;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #18181B;
        }
        .container {
            width: 100%;
            max-width: 1400px;
            padding: 50px;
            margin: 100px auto;
        }
    </style>
</head>
<body>
    <div class="container">
        <?php 
            $doctor = new \Crate\Database\Migrations\Doctor;

            $status = $doctor->execute('core', __DIR__ . '/migrations/000.php');
            if ($status) {
                var_dump('YaY');
            } else {
                var_dump($doctor->lastError());
            }
            echo "<br /><br />";

            $status = $doctor->execute('core', __DIR__ . '/migrations/001.php');
            if ($status) {
                var_dump('YaY');
            } else {
                var_dump($doctor->lastError());
            }
            echo "<br /><br />";

        ?>
    </div>
</body>
</html>