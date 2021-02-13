# 既存のMySQL製データベースを元にPlantUML製ER図を出力
## TL;DR
```
php artisan dump:mysql2puml
```
/storage/ER以下にPlantUML製ER図が出力されます。

## config
```
php artisan vendor:publish --provider=Mysql2PlantUml\Mysql2PlantUmlServiceProvider
```
設定ファイルが/config/以下にダンプされます。

以下の例では `Mysql2PlantUml\app\Models\ValueObjects\Relation` クラスのクラス定数を使っています。
`composer require --dev` でこのライブラリをインストールした場合、本番環境では存在しないクラスの参照でエラーが起きます。`require --dev`する場合、クラス定数内で定義されてある文字列を直に設定ファイルに書き込んでください。
### dist_dir
出力先のディレクトリを指定します。
```
'dist_dir' => 'storage/ER',
```
### connection
information_schemaを指すようにデータベース接続先を指定します。
```
    'connection' => [
        'driver' => 'mysql',
        'url' => env('DATABASE_URL'),
        'host' => env('DB_HOST', '127.0.0.1'),
        'port' => env('DB_PORT', '3306'),
        'database' => env('DB_DATABASE_INFORMATION_SCHEMA', 'information_schema'),
        'username' => env('DB_USERNAME', 'forge'),
        'password' => env('DB_PASSWORD', ''),
        'unix_socket' => env('DB_SOCKET', ''),
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
        'prefix' => '',
        'prefix_indexes' => true,
        'strict' => true,
        'engine' => null,
        'options' => extension_loaded('pdo_mysql') ? array_filter(
            [
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]
        ) : [],
    ],
```
### target_database
ER図に表現する対象のデータベース名を指定します。
```
'target_database' => env('DB_DATABASE'),
```
### without_tables
```
/*
 * ER図上に描かないテーブルを指定します
 */
'without_tables'  => [
    'migrations'
],
```
### relation_type
多重度の表現方法を指定します。
```
'target_database' => Relation::FORMAT_NUM,
// or
'target_database' => Relation::FORMAT_CROW,
```
### relations
テーブル間のリレーションとその図示を定義します。
何もなくとも外部キー制約を元にリレーションを図示します。
 - from : puml上の左辺。テーブル名
 - to : puml上の右辺。テーブル名
 - relation : 両者の関係。定数は\Mysql2PlantUml\app\Models\ValueObjects\Relation::RELATION_TYPESを参照。
 - direction : puml上の関係線の伸ばす方向。定数は\Mysql2PlantUml\app\Models\ValueObjects\Relation::DIRECTION_TYPESを参照。
 - arrowLength : puml上の関係線の長さ。常に($arrowLength > 0) === true
```
    'relations' => [
        [
            'from' => 'hoge',
            'to' => 'fuga',
            'relation' => Relation::MANY_MANDATORY_TO_ONE_MANDATORY,
            'direction' => Relation::DIRECTION_LEFT,
            'arrowLength' => 4,
        ],
        [
            'from' => 'foo',
            'to' => 'bar',
            'relation' => Relation::ONE_MANDATORY_TO_ONE_MANDATORY,
            'direction' => Relation::DIRECTION_UP,
            'arrowLength' => 4,
        ],
    ],
```
### packages
PlantUML上で複数テーブルをパッケージとしてまとめます。
```
    'packages' => [
        'hogefuga' => [
            'foo',
            'bar'
        ],
        'foobar' => [
            'hoge',
            'fuga'
        ]
    ]
```
### sub_files
特定のテーブルのみのPlantUMLファイルを作ります。
```
    'sub_files' => [
        'hoge.puml' => [
            'foo',
            'hoge',
        ],
        'fuga.puml' => [
            'foo',
            'bar',
            'fuga',
        ],
    ],
```
# example
## sql
```
create table migrations
(
    id        int unsigned auto_increment
        primary key,
    migration varchar(255) not null,
    batch     int          not null
)
    collate = utf8mb4_unicode_ci;

create table tags
(
    id    bigint unsigned auto_increment
        primary key,
    title varchar(255) not null
)
    collate = utf8mb4_unicode_ci;

create table task_tag
(
    task_id bigint unsigned not null,
    tag_id  bigint unsigned not null,
    constraint task_tag_custom_task_id_foreign
        foreign key (task_id) references tasks (id),
    constraint task_tag_tag_id_foreign
        foreign key (tag_id) references tags (id)
)
    collate = utf8mb4_unicode_ci;

create table tasks
(
    id      bigint unsigned auto_increment
        primary key,
    user_id bigint unsigned not null,
    content varchar(255)    not null,
    constraint tasks_user_id_unique
        unique (user_id),
    constraint tasks_user_id_foreign
        foreign key (user_id) references users (id)
            on update cascade on delete cascade
)
    collate = utf8mb4_unicode_ci;

create table users
(
    id   bigint unsigned auto_increment
        primary key,
    name varchar(255) not null
)
    collate = utf8mb4_unicode_ci;
```

## plantuml
```
@startuml
skinparam {
defaultFontName Monospaced
}
left to right direction
package dacapo_sample {
entity "migrations" as migrations {
    + id       [PK]              int(10) unsigned  not null 
      migration                  varchar(255)      not null 
      batch                      int(11)           not null 
}
entity "tags" as tags {
    + id   [PK]              bigint(20) unsigned  not null 
      title                  varchar(255)         not null 
}
entity "task_tag" as task_tag {
    # task_id    [FK]    [MUL] bigint(20) unsigned  not null 
    # tag_id     [FK]    [MUL] bigint(20) unsigned  not null 
}
entity "tasks" as tasks {
    + id     [PK]              bigint(20) unsigned  not null 
    # user_id    [FK][UK]      bigint(20) unsigned  not null 
      content                  varchar(255)         not null 
}
entity "users" as users {
    + id  [PK]              bigint(20) unsigned  not null 
      name                  varchar(255)         not null 
}
}

task_tag }o-- tasks
task_tag }o-- tags
tasks }o-- users
@enduml
```dacapo_sample_ER.svg
```
## ER図（puml to svg）
<img src="https://raw.githubusercontent.com/cpt-sugiura/laravel-mysql2plantuml/master/dacapo_sample_ER.svg" alt="ER図">
