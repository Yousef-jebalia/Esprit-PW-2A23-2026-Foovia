
<?php
//Connection file to project database the database name must be foovia_db
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
            
            } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
            }
            }
            return self::$pdo;
            }
}
?>