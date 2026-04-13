<?php

declare(strict_types=1);

require_once __DIR__ . '/config.php';

final class Magasin
{
    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? Database::getConnection();
    }

    public function fetchAll(): array
    {
        $statement = $this->db->query(
            'SELECT id_mag, name_mag, email_mag, phone_mag, adress_mag
             FROM magasin
             ORDER BY name_mag ASC'
        );

        return $statement->fetchAll();
    }

    public function countAll(): int
    {
        return (int) $this->db->query('SELECT COUNT(*) FROM magasin')->fetchColumn();
    }
}
