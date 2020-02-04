<?php

use Mysql2PlantUml\app\Models\ValueObjects\Relation;

return [
    /*
     * 出力先のディレクトリを指定します
     */
    'dist_dir'        => 'storage/ER',
    'connection'      => [
        'driver'         => env('DB_CONNECTION'),
        'url'            => env('DATABASE_URL'),
        'host'           => env('DB_HOST', '127.0.0.1'),
        'port'           => env('DB_PORT', '3306'),
        'database'       => env('DB_DATABASE_INFORMATION_SCHEMA', 'information_schema'),
        'username'       => env('DB_USERNAME', 'forge'),
        'password'       => env('DB_PASSWORD', ''),
        'unix_socket'    => env('DB_SOCKET', ''),
        'charset'        => 'utf8mb4',
        'collation'      => 'utf8mb4_unicode_ci',
        'prefix'         => '',
        'prefix_indexes' => true,
        'strict'         => true,
        'engine'         => null,
        'options'        => extension_loaded('pdo_mysql') ? array_filter(
            [
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]
        ) : [],
    ],
    'target_database' => env('DB_DATABASE'),
    /*
     * ER図上に描かないテーブルを指定します
     */
    'without_tables'  => [
        'migrations'
    ],
    // 自由なコメントを末尾に追加
    'free_comment' => '',
    // リレーションの表記形式指定
    'relation_type'   => Relation::FORMAT_NUM,
//    'relations' => [
//        [
//            'from' => 'hoge',
//            'to' => 'fuga',
//        ],
//        [
//            'from' => 'foo',
//            'to' => 'bar',
//            'relation' => Relation::ONE_MANDATORY_TO_ONE_MANDATORY,
//            'direction' => Relation::DIRECTION_UP,
//            'arrowLength' => 4,
//            'arrowChar' => '.',
//        ],
//    ],
//    'packages' => [
//        'hogefuga' => [
//            'foo',
//            'bar'
//        ],
//        'foobar' => [
//            'hoge',
//            'fuga'
//        ]
//    ]
];
