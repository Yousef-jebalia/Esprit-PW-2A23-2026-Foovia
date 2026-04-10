<?php

declare(strict_types=1);

require_once __DIR__ . '/config.php';

function marketplace_fetch_stores(): array
{
    $statement = foovia_db()->query(
        'SELECT id_mag, name_mag, email_mag, phone_mag, adress_mag
         FROM magasin
         ORDER BY name_mag ASC'
    );

    return $statement->fetch_all(MYSQLI_ASSOC);
}

function marketplace_fetch_products(): array
{
    $statement = foovia_db()->query(
        'SELECT
            m.id_march,
            m.name_march,
            m.description_march,
            m.price_march,
            m.quantity_march,
            m.date_expiration_march,
            m.point_acces_march,
            mag.id_mag,
            mag.name_mag,
            mag.email_mag,
            mag.phone_mag,
            mag.adress_mag
         FROM marchandise m
         LEFT JOIN vendre v ON v.id_march = m.id_march
         LEFT JOIN magasin mag ON mag.id_mag = v.id_mag
         ORDER BY m.id_march DESC'
    );

    return $statement->fetch_all(MYSQLI_ASSOC);
}

function marketplace_fetch_product_by_id(int $productId): ?array
{
    $statement = foovia_db()->prepare(
        'SELECT
            m.id_march,
            m.name_march,
            m.description_march,
            m.price_march,
            m.quantity_march,
            m.date_expiration_march,
            m.point_acces_march,
            mag.id_mag
         FROM marchandise m
         LEFT JOIN vendre v ON v.id_march = m.id_march
         LEFT JOIN magasin mag ON mag.id_mag = v.id_mag
         WHERE m.id_march = ?'
    );
    $statement->bind_param('i', $productId);
    $statement->execute();
    $result = $statement->get_result()->fetch_assoc();

    return $result ?: null;
}

function marketplace_fetch_summary(): array
{
    $db = foovia_db();

    return [
        'products' => (int) $db->query('SELECT COUNT(*) AS total FROM marchandise')->fetch_assoc()['total'],
        'stores' => (int) $db->query('SELECT COUNT(*) AS total FROM magasin')->fetch_assoc()['total'],
        'quantity' => (int) $db->query('SELECT COALESCE(SUM(quantity_march), 0) AS total FROM marchandise')->fetch_assoc()['total'],
    ];
}

function marketplace_create_product(array $payload, array $image): int
{
    $db = foovia_db();
    $imageBinary = file_get_contents($image['tmp_name']);

    $db->begin_transaction();

    try {
        $statement = $db->prepare(
            'INSERT INTO marchandise
                (name_march, description_march, price_march, quantity_march, date_expiration_march, point_acces_march, img_march)
             VALUES (?, ?, ?, ?, ?, ?, ?)'
        );

        $null = null;
        $statement->bind_param(
            'ssiissb',
            $payload['name_march'],
            $payload['description_march'],
            $payload['price_march'],
            $payload['quantity_march'],
            $payload['date_expiration_march'],
            $payload['point_acces_march'],
            $null
        );
        $statement->send_long_data(6, $imageBinary);
        $statement->execute();

        $productId = (int) $db->insert_id;

        $linkStatement = $db->prepare('INSERT INTO vendre (id_march, id_mag) VALUES (?, ?)');
        $linkStatement->bind_param('ii', $productId, $payload['id_mag']);
        $linkStatement->execute();

        $db->commit();

        return $productId;
    } catch (Throwable $exception) {
        $db->rollback();
        throw $exception;
    }
}

function marketplace_delete_product(int $productId): void
{
    $db = foovia_db();
    $db->begin_transaction();

    try {
        $linkStatement = $db->prepare('DELETE FROM vendre WHERE id_march = ?');
        $linkStatement->bind_param('i', $productId);
        $linkStatement->execute();

        $productStatement = $db->prepare('DELETE FROM marchandise WHERE id_march = ?');
        $productStatement->bind_param('i', $productId);
        $productStatement->execute();

        $db->commit();
    } catch (Throwable $exception) {
        $db->rollback();
        throw $exception;
    }
}

function marketplace_update_product(array $payload, array $image): void
{
    $db = foovia_db();
    $db->begin_transaction();

    try {
        if (isset($image['tmp_name']) && $image['tmp_name'] !== '' && is_uploaded_file($image['tmp_name'])) {
            $imageBinary = file_get_contents($image['tmp_name']);
            $statement = $db->prepare(
                'UPDATE marchandise
                 SET name_march = ?, description_march = ?, price_march = ?, quantity_march = ?, date_expiration_march = ?, point_acces_march = ?, img_march = ?
                 WHERE id_march = ?'
            );
            $null = null;
            $statement->bind_param(
                'ssiissbi',
                $payload['name_march'],
                $payload['description_march'],
                $payload['price_march'],
                $payload['quantity_march'],
                $payload['date_expiration_march'],
                $payload['point_acces_march'],
                $null,
                $payload['id_march']
            );
            $statement->send_long_data(6, $imageBinary);
            $statement->execute();
        } else {
            $statement = $db->prepare(
                'UPDATE marchandise
                 SET name_march = ?, description_march = ?, price_march = ?, quantity_march = ?, date_expiration_march = ?, point_acces_march = ?
                 WHERE id_march = ?'
            );
            $statement->bind_param(
                'ssiissi',
                $payload['name_march'],
                $payload['description_march'],
                $payload['price_march'],
                $payload['quantity_march'],
                $payload['date_expiration_march'],
                $payload['point_acces_march'],
                $payload['id_march']
            );
            $statement->execute();
        }

        $linkStatement = $db->prepare('UPDATE vendre SET id_mag = ? WHERE id_march = ?');
        $linkStatement->bind_param('ii', $payload['id_mag'], $payload['id_march']);
        $linkStatement->execute();

        $db->commit();
    } catch (Throwable $exception) {
        $db->rollback();
        throw $exception;
    }
}
