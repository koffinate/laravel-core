<?php

namespace Koffin\Core\Providers;

use Illuminate\Database\Connection;
use Illuminate\Support\ServiceProvider;
use Koffin\Core\Database\PgSqlConnection;

class PgsqlServiceProvider extends ServiceProvider
{
    public function register()
    {
        Connection::resolverFor('pgsql', function ($connection, $database, $prefix, $config) {
            return new PgSqlConnection($connection, $database, $prefix, $config);
        });
    }
}
