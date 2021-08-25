<?php

return [
    'logPath'                => dirname(dirname(__FILE__)) . '/logs',
    'max_count_of_visitor'   => '30',
    'booked_id_prefix'       => '29',
    'databases' => [
        'main'   => [
            'host'    => 'localhost:C:\web\www\kasa_API\db\pos_client.fdb',
            'user'    => 'SYSDBA',
            'pswd'    => 'masterkey',
            'charset' => 'UTF-8'
        ],
        'server' => [
            'host'    => '',
            'user'    => '',
            'pswd'    => '',
            'charset' => 'UTF-8',
        ],
    ],
];

