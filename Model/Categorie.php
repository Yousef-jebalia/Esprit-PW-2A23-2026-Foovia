<?php

declare(strict_types=1);

require_once __DIR__ . '/config.php';

final class Categorie
{
    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? Database::getConnection();
    }

    public function fetchAll(): array
    {
        $statement = $this->db->query(
            'SELECT id_categ, name_categ
             FROM categorie
             ORDER BY name_categ ASC'
        );

        return $statement->fetchAll();
    }
}
