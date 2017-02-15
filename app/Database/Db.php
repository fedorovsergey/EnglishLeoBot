<?php

namespace Database;

use PDO;
use PDOException;

class Db
{
    /**
     * PDO object
     *
     * @var PDO
     */
    static protected $pdo;

    /**
     * Get the PDO object of the connected database
     *
     * @return PDO
     */
    public static function getPdo()
    {
        return self::$pdo;
    }

    /**
     * Provides access to PDO connection to database
     * @param array $credentials
     * @param string $encoding
     * @return PDO
     * @throws Exception
     */
    public static function initialize(array $credentials, $encoding = 'utf8mb4')
    {
        if (empty($credentials)) {
            throw new Exception('Credentials not provided!');
        }

        $dsn = 'mysql:host=' . $credentials['host'] . ';dbname=' . $credentials['database'];
        $options = [PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES ' . $encoding];
        try {
            $pdo = new PDO($dsn, $credentials['user'], $credentials['password'], $options);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            throw new Exception($e->getMessage());
        }

        self::$pdo = $pdo;

        return self::$pdo;
    }
}
