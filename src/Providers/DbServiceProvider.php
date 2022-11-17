<?php

namespace Koffin\Core\Providers;

use Illuminate\Database\Connection;
use Illuminate\Support\ServiceProvider;
use Koffin\Core\Database\MySqlConnection;
use Koffin\Core\Database\PgSqlConnection;

class DbServiceProvider extends ServiceProvider
{
    public function register()
    {
        Connection::resolverFor('mysql', function ($connection, $database, $prefix, $config) {
            return new MySqlConnection($connection, $database, $prefix, $config);
        });
        Connection::resolverFor('pgsql', function ($connection, $database, $prefix, $config) {
            return new PgSqlConnection($connection, $database, $prefix, $config);
        });
    }
}
