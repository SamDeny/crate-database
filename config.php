<?php

return [

    'default' => 'sqlite',
    'crate' => 'sqlite',

    'drivers' => [

        'sqlite' => [
            'provider' => \Crate\Database\Drivers\SQLite::class,
            'path' => __DIR__ . '/storage/temp.sqlite',
            'encryptionKey' => null,
            'pragmas' => [
                'foreign_keys' => true,
                'journal_mode' => true
            ]
        ],

        'mongodb' => [
            'provider' => \Crate\Database\Drivers\MongoDB::class,
            'dns' => 'mongodb://localhost:27017',
            'database' => 'crate',
            'dnsOptions' => [],
            'driverOptions' => []
        ],

        'mysqli' => [
            'provider' => \Crate\Database\Drivers\MySQLi::Class,
            'hostname' => 'locahost',
            'port' => 5432,
            'username' => 'root',
            'password' => '',
            'database' => 'crate',
            'socket' => ''
        ]

    ]

];
