<?php
//Connection file to project database the database name must be foovia_db
if (!class_exists('config')) {
  class config
  {
        private static $pdo = null;
    public static function getConnexion()
    {
            if (!isset(self::$pdo)) {
        // $host     = '127.0.0.1'; 
            $servername="localhost";
            $username="root";
            $password="";
            $dbname="foovia_db";
            try {
            self::$pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);
            //echo "connected successfully";
            } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
            }
            }
            return self::$pdo;
            }
}
}
config::getConnexion();
?>