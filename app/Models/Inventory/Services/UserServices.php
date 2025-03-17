<?php

namespace Models\Inventory\Services;

use Models\Inventory\Services\DatabaseService;

class UserServices
{
    public static function authenticate(string $username, string $password): ?object
    {
        $db = DatabaseService::getConnection();

        $result = pg_query_params(
            $db,
            "SELECT * FROM users WHERE username = $1",
            [$username]
        );

        $user = pg_fetch_object($result);

        if ($user && password_verify($password, $user->password)) {
            return $user;
        }

        return null;
    }

    public static function getUserByToken($token) {
        $userId = TokenServices::validateToken($token);
        if (!$userId) {
            return null;
        }

        $db = DatabaseService::getConnection();
        $result = pg_query_params($db, "SELECT * FROM users WHERE id = $1", [$userId]);

        if (!$result || pg_num_rows($result) === 0) {
            return null;
        }

        return pg_fetch_assoc($result);
    }

    public static function getUserTransactions($userId) {
        $db = DatabaseService::getConnection();
        $result = pg_query_params($db, "SELECT * FROM transactions WHERE user_id = $1", [$userId]);

        if (!$result) {
            return [];
        }

        return pg_fetch_all($result) ?: [];
    }


    public static function elevateUserToPremium($userId) {
        $db = DatabaseService::getConnection();
        pg_query_params($db, "UPDATE users SET type = 'PREMIUM' WHERE id = $1", [$userId]);
    }

}
