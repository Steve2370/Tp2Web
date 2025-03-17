<?php

namespace Models\Inventory\Services;


use Exception;
use Random\RandomException;

class TokenServices
{
    /**
     * @throws RandomException
     * @throws \Exception
     */
    public static function generateToken(int $userId): string
    {
        $token = bin2hex(random_bytes(16));

        $db = DatabaseService::getConnection();

        $result = pg_query_params(
            $db,
            "INSERT INTO tokens (user_id, token, created_at) VALUES ($1, $2, NOW())",
            [$userId, $token]
        );

        if (!$result) {
            throw new Exception("Erreur PostgreSQL : " . pg_last_error($db));
        }

        return $token;
    }

    public static function validateToken($token) {
        $db = DatabaseService::getConnection();
        $result = pg_query_params($db, "SELECT user_id FROM tokens WHERE token = $1", [$token]);

        if (!$result || pg_num_rows($result) === 0) {
            return null;
        }
        $row = pg_fetch_assoc($result);
        pg_query_params($db, "DELETE FROM tokens WHERE token = $1", [$token]);

        return $row['user_id'];
    }

}
