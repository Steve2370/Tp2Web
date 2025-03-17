<?php

namespace Models\Inventory\Services;

class DatabaseService
{
    private static $connection = null;

    public static function getConnection()
    {
        if (is_null(self::$connection)) {
            $host = "zephyrus_database";
            $port = "5432";
            $dbname = "zephyrus";
            $user = "dev";
            $password = "dev";

            $connString = "host=$host port=$port dbname=$dbname user=$user password=$password";
            self::$connection = pg_connect($connString);

            if (!self::$connection) {
                throw new \Exception("Erreur de connexion à PostgreSQL");
            }
        }
        return self::$connection;
    }
}
