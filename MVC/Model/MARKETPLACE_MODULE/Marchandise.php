<?php

declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/Magasin.php';

final class Marchandise
{
    private PDO $db;
    private Magasin $magasin;

    public function __construct(?PDO $db = null, ?Magasin $magasin = null)
    {
        $this->db = $db ?? Database::getConnection();
        $this->magasin = $magasin ?? new Magasin($this->db);
    }

    public function fetchAllWithStores(): array
    {
        $statement = $this->db->query(
            'SELECT
                m.id_march,
                m.name_march,
                m.description_march,
                m.price_march,
                m.quantity_march,
                m.date_expiration_march,
                m.point_acces_march,
                m.reserved_count_march,
                GROUP_CONCAT(DISTINCT mag.id_mag ORDER BY mag.name_mag SEPARATOR \',\') AS store_ids,
                GROUP_CONCAT(DISTINCT mag.name_mag ORDER BY mag.name_mag SEPARATOR \', \') AS store_names,
                GROUP_CONCAT(DISTINCT mag.email_mag ORDER BY mag.name_mag SEPARATOR \', \') AS store_emails,
                GROUP_CONCAT(DISTINCT mag.phone_mag ORDER BY mag.name_mag SEPARATOR \', \') AS store_phones,
                GROUP_CONCAT(DISTINCT mag.adress_mag ORDER BY mag.name_mag SEPARATOR \' | \') AS store_addresses,
                GROUP_CONCAT(DISTINCT c.id_categ ORDER BY c.name_categ SEPARATOR \',\') AS category_ids,
                GROUP_CONCAT(DISTINCT c.name_categ ORDER BY c.name_categ SEPARATOR \', \') AS category_names
             FROM marchandise m
             LEFT JOIN vendre v ON v.id_march = m.id_march
             LEFT JOIN magasin mag ON mag.id_mag = v.id_mag
             LEFT JOIN marchandise_categorie mc ON mc.id_march = m.id_march
             LEFT JOIN categorie c ON c.id_categ = mc.id_categ
             GROUP BY
                m.id_march,
                m.name_march,
                m.description_march,
                m.price_march,
                m.quantity_march,
                m.date_expiration_march,
                m.point_acces_march,
                m.reserved_count_march
             ORDER BY m.id_march DESC'
        );

        return $statement->fetchAll();
    }

    public function findById(int $productId): ?array
    {
        $statement = $this->db->prepare(
            'SELECT
                m.id_march,
                m.name_march,
                m.description_march,
                m.price_march,
                m.quantity_march,
                m.date_expiration_march,
                m.point_acces_march,
                m.reserved_count_march,
                GROUP_CONCAT(DISTINCT mag.id_mag ORDER BY mag.name_mag SEPARATOR \',\') AS store_ids,
                GROUP_CONCAT(DISTINCT c.id_categ ORDER BY c.name_categ SEPARATOR \',\') AS category_ids,
                GROUP_CONCAT(DISTINCT c.name_categ ORDER BY c.name_categ SEPARATOR \', \') AS category_names
             FROM marchandise m
             LEFT JOIN vendre v ON v.id_march = m.id_march
             LEFT JOIN magasin mag ON mag.id_mag = v.id_mag
             LEFT JOIN marchandise_categorie mc ON mc.id_march = m.id_march
             LEFT JOIN categorie c ON c.id_categ = mc.id_categ
             WHERE m.id_march = :id_march
             GROUP BY
                m.id_march,
                m.name_march,
                m.description_march,
                m.price_march,
                m.quantity_march,
                m.date_expiration_march,
                m.point_acces_march,
                m.reserved_count_march'
        );
        $statement->execute(['id_march' => $productId]);
        $result = $statement->fetch();

        return $result ?: null;
    }

    public function fetchSummary(): array
    {
        return [
            'products' => $this->countAll(),
            'stores' => $this->magasin->countAll(),
            'quantity' => (int) $this->db->query('SELECT COALESCE(SUM(quantity_march), 0) FROM marchandise')->fetchColumn(),
        ];
    }

    public function fetchRecommendedProducts(int $productId, int $limit = 3): array
    {
        $statement = $this->db->prepare(
            'SELECT
                m.id_march,
                m.name_march,
                m.description_march,
                m.price_march,
                m.quantity_march,
                GROUP_CONCAT(DISTINCT c.name_categ ORDER BY c.name_categ SEPARATOR \', \') AS category_names,
                COUNT(DISTINCT matched.id_categ) AS shared_categories
             FROM marchandise m
             INNER JOIN marchandise_categorie matched ON matched.id_march = m.id_march
             INNER JOIN marchandise_categorie current_categories
                ON current_categories.id_categ = matched.id_categ
                AND current_categories.id_march = :current_id
             LEFT JOIN marchandise_categorie mc ON mc.id_march = m.id_march
             LEFT JOIN categorie c ON c.id_categ = mc.id_categ
             WHERE m.id_march <> :excluded_id
             GROUP BY
                m.id_march,
                m.name_march,
                m.description_march,
                m.price_march,
                m.quantity_march
             ORDER BY shared_categories DESC, m.id_march DESC
             LIMIT ' . max(1, $limit)
        );
        $statement->execute([
            'current_id' => $productId,
            'excluded_id' => $productId,
        ]);

        return $statement->fetchAll();
    }

    public function countAll(): int
    {
        return (int) $this->db->query('SELECT COUNT(*) FROM marchandise')->fetchColumn();
    }

    public function create(array $payload, array $image): int
    {
        $imageBinary = file_get_contents($image['tmp_name']);

        $this->db->beginTransaction();

        try {
            $statement = $this->db->prepare(
                'INSERT INTO marchandise
                    (name_march, description_march, price_march, quantity_march, date_expiration_march, point_acces_march, img_march)
                 VALUES (:name_march, :description_march, :price_march, :quantity_march, :date_expiration_march, :point_acces_march, :img_march)'
            );

            $statement->execute([
                'name_march' => $payload['name_march'],
                'description_march' => $payload['description_march'],
                'price_march' => $payload['price_march'],
                'quantity_march' => $payload['quantity_march'],
                'date_expiration_march' => $payload['date_expiration_march'],
                'point_acces_march' => $payload['point_acces_march'],
                'img_march' => $imageBinary,
            ]);

            $productId = (int) $this->db->lastInsertId();
            $this->linkToStores($productId, $payload['id_mag']);
            $this->linkToCategories($productId, $payload['id_categ']);

            $this->db->commit();

            return $productId;
        } catch (Throwable $exception) {
            $this->db->rollBack();
            throw $exception;
        }
    }

    public function update(array $payload, array $image): void
    {
        $this->db->beginTransaction();

        try {
            if ($this->hasNewImage($image)) {
                $this->updateWithImage($payload, file_get_contents($image['tmp_name']));
            } else {
                $this->updateWithoutImage($payload);
            }

            $this->updateStoreLinks((int) $payload['id_march'], $payload['id_mag']);
            $this->updateCategoryLinks((int) $payload['id_march'], $payload['id_categ']);
            $this->db->commit();
        } catch (Throwable $exception) {
            $this->db->rollBack();
            throw $exception;
        }
    }

    public function delete(int $productId): void
    {
        $this->db->beginTransaction();

        try {
            $linkStatement = $this->db->prepare('DELETE FROM vendre WHERE id_march = :id_march');
            $linkStatement->execute(['id_march' => $productId]);

            $categoryStatement = $this->db->prepare('DELETE FROM marchandise_categorie WHERE id_march = :id_march');
            $categoryStatement->execute(['id_march' => $productId]);

            $reservationStatement = $this->db->prepare('DELETE FROM marchandise_reservation WHERE id_march = :id_march');
            $reservationStatement->execute(['id_march' => $productId]);

            $productStatement = $this->db->prepare('DELETE FROM marchandise WHERE id_march = :id_march');
            $productStatement->execute(['id_march' => $productId]);

            $this->db->commit();
        } catch (Throwable $exception) {
            $this->db->rollBack();
            throw $exception;
        }
    }

    public function fetchImageById(int $productId): string|false|null
    {
        $statement = $this->db->prepare('SELECT img_march FROM marchandise WHERE id_march = :id_march');
        $statement->execute(['id_march' => $productId]);

        return $statement->fetchColumn();
    }

    public function fetchAvailabilityById(int $productId): array
    {
        $statement = $this->db->prepare(
            'SELECT
                mag.id_mag,
                mag.name_mag,
                mag.email_mag,
                mag.phone_mag,
                mag.adress_mag,
                mag.img_mag IS NOT NULL AS has_image,
                CASE WHEN v.id_march IS NULL THEN 0 ELSE 1 END AS is_available
             FROM magasin mag
             LEFT JOIN vendre v ON v.id_mag = mag.id_mag AND v.id_march = :id_march
             ORDER BY mag.name_mag ASC'
        );
        $statement->execute(['id_march' => $productId]);

        return $statement->fetchAll();
    }

    public function reserveFromStore(int $productId, int $storeId, int $quantity): void
    {
        $quantity = max(1, $quantity);

        $this->db->beginTransaction();

        try {
            $insertStatement = $this->db->prepare(
                'INSERT INTO marchandise_reservation (id_march, id_mag, quantity_reservation)
                 VALUES (:id_march, :id_mag, :quantity_reservation)'
            );
            $insertStatement->execute([
                'id_march' => $productId,
                'id_mag' => $storeId,
                'quantity_reservation' => $quantity,
            ]);

            $updateStatement = $this->db->prepare(
                'UPDATE marchandise
                 SET reserved_count_march = reserved_count_march + :quantity_reservation
                 WHERE id_march = :id_march'
            );
            $updateStatement->execute([
                'quantity_reservation' => $quantity,
                'id_march' => $productId,
            ]);

            $this->db->commit();
        } catch (Throwable $exception) {
            $this->db->rollBack();
            throw $exception;
        }
    }

    public function fetchReservationsByProduct(): array
    {
        $statement = $this->db->query(
            'SELECT
                store_totals.id_march,
                GROUP_CONCAT(CONCAT(mag.name_mag, \': \', store_totals.quantity_total) ORDER BY mag.name_mag SEPARATOR \' | \') AS reservation_details
             FROM (
                SELECT id_march, id_mag, SUM(quantity_reservation) AS quantity_total
                FROM marchandise_reservation
                GROUP BY id_march, id_mag
             ) store_totals
             INNER JOIN magasin mag ON mag.id_mag = store_totals.id_mag
             GROUP BY store_totals.id_march'
        );

        $reservations = [];
        foreach ($statement->fetchAll() as $row) {
            $reservations[(int) $row['id_march']] = (string) $row['reservation_details'];
        }

        return $reservations;
    }

    public function resetReservations(int $productId): void
    {
        $this->db->beginTransaction();

        try {
            $deleteStatement = $this->db->prepare('DELETE FROM marchandise_reservation WHERE id_march = :id_march');
            $deleteStatement->execute(['id_march' => $productId]);

            $updateStatement = $this->db->prepare(
                'UPDATE marchandise SET reserved_count_march = 0 WHERE id_march = :id_march'
            );
            $updateStatement->execute(['id_march' => $productId]);

            $this->db->commit();
        } catch (Throwable $exception) {
            $this->db->rollBack();
            throw $exception;
        }
    }

    private function linkToStores(int $productId, array $storeIds): void
    {
        $statement = $this->db->prepare('INSERT INTO vendre (id_march, id_mag) VALUES (:id_march, :id_mag)');

        foreach ($storeIds as $storeId) {
            $statement->execute([
                'id_march' => $productId,
                'id_mag' => (int) $storeId,
            ]);
        }
    }

    private function updateStoreLinks(int $productId, array $storeIds): void
    {
        $deleteStatement = $this->db->prepare('DELETE FROM vendre WHERE id_march = :id_march');
        $deleteStatement->execute(['id_march' => $productId]);

        $this->linkToStores($productId, $storeIds);
    }

    private function linkToCategories(int $productId, array $categoryIds): void
    {
        if ($categoryIds === []) {
            return;
        }

        $statement = $this->db->prepare(
            'INSERT INTO marchandise_categorie (id_march, id_categ)
             VALUES (:id_march, :id_categ)'
        );

        foreach (array_unique($categoryIds) as $categoryId) {
            $statement->execute([
                'id_march' => $productId,
                'id_categ' => (int) $categoryId,
            ]);
        }
    }

    private function updateCategoryLinks(int $productId, array $categoryIds): void
    {
        $deleteStatement = $this->db->prepare('DELETE FROM marchandise_categorie WHERE id_march = :id_march');
        $deleteStatement->execute(['id_march' => $productId]);

        $this->linkToCategories($productId, $categoryIds);
    }

    private function hasNewImage(array $image): bool
    {
        return isset($image['tmp_name']) && $image['tmp_name'] !== '' && is_uploaded_file($image['tmp_name']);
    }

    private function updateWithImage(array $payload, string $imageBinary): void
    {
        $statement = $this->db->prepare(
            'UPDATE marchandise
             SET name_march = :name_march,
                 description_march = :description_march,
                 price_march = :price_march,
                 quantity_march = :quantity_march,
                 date_expiration_march = :date_expiration_march,
                 point_acces_march = :point_acces_march,
                 img_march = :img_march
             WHERE id_march = :id_march'
        );
        $statement->execute([
            'name_march' => $payload['name_march'],
            'description_march' => $payload['description_march'],
            'price_march' => $payload['price_march'],
            'quantity_march' => $payload['quantity_march'],
            'date_expiration_march' => $payload['date_expiration_march'],
            'point_acces_march' => $payload['point_acces_march'],
            'img_march' => $imageBinary,
            'id_march' => $payload['id_march'],
        ]);
    }

    private function updateWithoutImage(array $payload): void
    {
        $statement = $this->db->prepare(
            'UPDATE marchandise
             SET name_march = :name_march,
                 description_march = :description_march,
                 price_march = :price_march,
                 quantity_march = :quantity_march,
                 date_expiration_march = :date_expiration_march,
                 point_acces_march = :point_acces_march
             WHERE id_march = :id_march'
        );
        $statement->execute([
            'name_march' => $payload['name_march'],
            'description_march' => $payload['description_march'],
            'price_march' => $payload['price_march'],
            'quantity_march' => $payload['quantity_march'],
            'date_expiration_march' => $payload['date_expiration_march'],
            'point_acces_march' => $payload['point_acces_march'],
            'id_march' => $payload['id_march'],
        ]);
    }
}
