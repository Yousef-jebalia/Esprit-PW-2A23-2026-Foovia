<?php
require_once __DIR__ . '/env.php';

//Connection file to project database the database name must be foovia_db
class config
{ 
        private static $pdo = null;
    public static function getConnexion()
    {
            if (!isset(self::$pdo)) {
            $servername = foovia_env('DB_HOST', 'localhost');
            $port = foovia_env('DB_PORT', '3306');
            $username = foovia_env('DB_USER', 'root');
            $password = foovia_env('DB_PASS', '');
            $dbname = foovia_env('DB_NAME', 'foovia_db');
            $charset = foovia_env('DB_CHARSET', 'utf8mb4');
            $dsn = "mysql:host=$servername;port=$port;dbname=$dbname;charset=$charset";
            try {
            self::$pdo = new PDO($dsn, $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]);

            } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
            }
            }
            return self::$pdo;
            }
}
config::getConnexion();
?>
