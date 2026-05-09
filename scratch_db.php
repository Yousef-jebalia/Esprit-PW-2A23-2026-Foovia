<?php
require_once __DIR__ . '/MVC/Model/config.php';
$db = config::getConnexion();
$res = $db->query("DESCRIBE log_meal");
print_r($res->fetchAll(PDO::FETCH_ASSOC));
