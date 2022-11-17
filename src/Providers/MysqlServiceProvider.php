<?php

namespace Koffin\Core\Providers;

use Illuminate\Database\Connection;
use Illuminate\Support\ServiceProvider;
use Koffin\Core\Database\MySqlConnection;

class MysqlServiceProvider extends ServiceProvider
{
    public function register()
    {
        Connection::resolverFor('mysql', function ($connection, $database, $prefix, $config) {
            return new MySqlConnection($connection, $database, $prefix, $config);
        });
    }
}
