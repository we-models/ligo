<?php

use Illuminate\Support\Str;

return [

    /*
    |--------------------------------------------------------------------------
    | Default Database Connection Name
    |--------------------------------------------------------------------------
    |
    | Here you may specify which of the database connections below you wish
    | to use as your default connection for all database work. Of course
    | you may use many connections at once using the Database library.
    |
    */

    'default' => env('DB_CONNECTION', 'mysql'),

    /*
    |--------------------------------------------------------------------------
    | Database Connections
    |--------------------------------------------------------------------------
    |
    | Here are each of the database connections setup for your application.
    | Of course, examples of configuring each database platform that is
    | supported by Laravel is shown below to make development simple.
    |
    |
    | All database work in Laravel is done through the PHP PDO facilities
    | so make sure you have the driver for your particular database of
    | choice installed on your machine before you begin development.
    |
    */

    'connections' => [

        'sqlite' => [
            'driver' => 'sqlite',
            'url' => env('DATABASE_URL'),
            'database' => env('DB_DATABASE', database_path('database.sqlite')),
            'prefix' => '',
            'foreign_key_constraints' => env('DB_FOREIGN_KEYS', true),
        ],

        'mysql' => [
            'driver' => 'mysql',
            'url' => env('DATABASE_URL'),
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE', 'forge'),
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ],

        'pgsql' => [
            'driver' => 'pgsql',
            'url' => env('DATABASE_URL'),
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '5432'),
            'database' => env('DB_DATABASE', 'forge'),
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
            'search_path' => 'public',
            'sslmode' => 'prefer',
        ],

        'sqlsrv' => [
            'driver' => 'sqlsrv',
            'url' => env('DATABASE_URL'),
            'host' => env('DB_HOST', 'localhost'),
            'port' => env('DB_PORT', '1433'),
            'database' => env('DB_DATABASE', 'forge'),
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
            // 'encrypt' => env('DB_ENCRYPT', 'yes'),
            // 'trust_server_certificate' => env('DB_TRUST_SERVER_CERTIFICATE', 'false'),
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Migration Repository Table
    |--------------------------------------------------------------------------
    |
    | This table keeps track of all the migrations that have already run for
    | your application. Using this information, we can determine which of
    | the migrations on disk haven't actually been run in the database.
    |
    */

    'migrations' => 'migrations',

    'tables' => [
        'WE_ST_EN' => [
            'table' => 'websockets_statistics_entries',
            'id' => 'websockets_statistics_entry_id'
        ],
        'USERS' => [
            'table' => 'users',
            'id' => 'user_id'
        ],
        'PA_RE_TO' => [
            'table' => 'password_reset_tokens',
            'id' => 'password_reset_token_id'
        ],
        'PA_RE' => [
            'table' => 'password_resets',
            'id' => 'password_reset_id'
        ],
        'OA_AU_CO' => [
            'table' => 'oauth_auth_codes',
            'id' => 'oauth_auth_code_id'
        ],
        'OA_AC_TO' => [
            'table' => 'oauth_access_tokens',
            'id' => 'oauth_access_token_id'
        ],
        'OA_RE_TO' => [
            'table' => 'oauth_refresh_tokens',
            'id' => 'oauth_refresh_token_id'
        ],
        'OA_CL' => [
            'table' => 'oauth_clients',
            'id' => 'oauth_clients_id'
        ],
        'OA_PE_AC_CL' => [
            'table' => 'oauth_personal_access_clients',
            'id' => 'oauth_personal_access_client_id'
        ],
        'FA_JO' => [
            'table' => 'failed_jobs',
            'id' => 'failed_job_id'
        ],
        'PE_AC_TO' => [
            'table' => 'personal_access_tokens',
            'id' => 'personal_access_token_id'
        ],
        'ROLES' => [
            'table' => 'roles',
            'id' => 'role_id'
        ],
        'PERMISSIONS' => [
            'table' => 'permissions',
            'id' => 'permission_id'
        ],
        'AC_LO' => [
            'table' => 'activity_log',
            'id' => 'activity_log_id'
        ],
        'GROUPS' => [
            'table' => 'groups',
            'id' => 'group_id'
        ],
        'MO_HA_GROUP' => [
            'table' => 'model_has_group',
            'id' => 'model_has_group_id'
        ],
        'SESSIONS' => [
            'table' => 'sessions',
            'id' => 'session_id'
        ],
        'DA_TY' => [
            'table' => 'data_types',
            'id' => 'data_type_id'
        ],
        'CONFIG' => [
            'table' => 'configurations',
            'id' => 'configuration_id'
        ],
        'ME_FI' => [
            'table' => 'media_files',
            'id' => 'media_file_id'
        ],
        'MO_HA_ME_FI' => [
            'table' => 'model_has_media_file',
        ],
        'OB_TYPE' => [
            'table' => 'object_types',
            'id' => 'object_type_id'
        ],
        'OB_TYPE_RE' => [
            'table' => 'object_type_relations',
            'id'  => 'object_type_relation_id'
        ],
        'FIELDS' => [
            'table' => 'fields',
            'id'  => 'field_id'
        ],
        'THE_OBJ' => [
            'table' => 'the_objects',
            'id'  => 'the_object_id'
        ],
        'OBJ_REL' => [
            'table' => 'object_relations',
            'id'  => 'object_relation_id'
        ],
        'OBJ_FI_VAL' => [
            'table' => 'object_field_value',
            'id'  => 'object_field_value_id'
        ],
        'ERR_LOG' => [
            'table' => 'error_logs',
            'id'  => 'error_log_id'
        ],
        'LINKS' => [
            'table' => 'links',
            'id'  => 'link_id'
        ],
        'US_MA' =>[
            'table' => 'user_manipulations',
            'id' => 'user_manipulation_id'
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | Redis Databases
    |--------------------------------------------------------------------------
    |
    | Redis is an open source, fast, and advanced key-value store that also
    | provides a richer body of commands than a typical key-value system
    | such as APC or Memcached. Laravel makes it easy to dig right in.
    |
    */

    'redis' => [

        'client' => env('REDIS_CLIENT', 'phpredis'),

        'options' => [
            'cluster' => env('REDIS_CLUSTER', 'redis'),
            'prefix' => env('REDIS_PREFIX', Str::slug(env('APP_NAME', 'laravel'), '_').'_database_'),
        ],

        'default' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'username' => env('REDIS_USERNAME'),
            'password' => env('REDIS_PASSWORD'),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_DB', '0'),
        ],

        'cache' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'username' => env('REDIS_USERNAME'),
            'password' => env('REDIS_PASSWORD'),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_CACHE_DB', '1'),
        ],

    ],

];
