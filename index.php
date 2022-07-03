<?php declare(strict_types=1);

use Crate\Database\Document;
use Crate\Database\Repository;
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

// Reset
$path = explode('?', $_SERVER["REQUEST_URI"])[0];
if ($path === '/reset') {
    @unlink(__DIR__ . '/storage/temp.sqlite');
    header('Location: /?install=1');
    die();
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
        header {
            display: flex;
            justify-content: flex-end;
        }
        article {
            margin-top: 50px;
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <a href="/reset">Reset</a>
        </header>
        
        <article>
        <?php 

            if (($_GET['install'] ?? '0') === '1') {
                $doctor = new \Crate\Database\Migrations\Doctor;

                $status = $doctor->execute('core', __DIR__ . '/migrations/000_migrations-table.php');
                if ($status) {
                    var_dump('YaY');
                } else {
                    var_dump($doctor->lastError());
                }
                echo "<br /><br />";

                $status = $doctor->execute('core', __DIR__ . '/migrations/001_test-migrator-methods.php');
                if ($status) {
                    var_dump('YaY');
                } else {
                    var_dump($doctor->lastError());
                }
                echo "<br /><br />";

                $status = $doctor->execute('core', __DIR__ . '/migrations/002_users-strict-document.php');
                if ($status) {
                    var_dump('YaY');
                } else {
                    var_dump($doctor->lastError());
                }
                echo "<br /><br />";
            }
            $repo = new Repository('users');

            $user = new Document;
            $user->username = 'username';
            $user->email = 'email@info.com';
            $user->display_name = 'Display Name';
            if ($repo->validate($user)) {
                var_dump($repo->insert($user));
            } else {
                throw new \Exception('Document is invalid!');
            }

        ?>
        </article>
    </div>
</body>
</html>