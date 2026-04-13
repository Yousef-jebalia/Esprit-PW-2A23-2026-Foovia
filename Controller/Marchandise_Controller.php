<?php

declare(strict_types=1);

require_once __DIR__ . '/../Model/Marchandise.php';

final class MarchandiseController
{
    private Marchandise $marchandise;

    public function __construct(?Marchandise $marchandise = null)
    {
        $this->marchandise = $marchandise ?? new Marchandise();
    }

    public function handle(): void
    {
        $action = (string) ($_GET['action'] ?? $_POST['action'] ?? 'index');

        if ($action === 'save' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->save();
            return;
        }

        if ($action === 'delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->delete();
            return;
        }

        if ($action === 'image') {
            $this->showImage();
            return;
        }

        $this->redirect('../View/front_office/organic-1.0.0/marketplace.php');
    }

    private function save(): void
    {
        $payload = $this->buildProductPayload();
        $image = $_FILES['img_march'] ?? null;

        try {
            if ($payload['id_march'] > 0) {
                $this->marchandise->update($payload, is_array($image) ? $image : []);
                $this->redirect('../View/back_office/material_able-main/products.php?status=updated');
            }

            $this->marchandise->create($payload, is_array($image) ? $image : []);
            $this->redirect('../View/back_office/material_able-main/products.php?status=success');
        } catch (Throwable $exception) {
            $this->redirect('../View/back_office/material_able-main/products.php?status=dberror');
        }
    }

    private function delete(): void
    {
        try {
            $this->marchandise->delete((int) ($_POST['id_march'] ?? 0));
            $this->redirect('../View/back_office/material_able-main/products.php?status=deleted');
        } catch (Throwable $exception) {
            $this->redirect('../View/back_office/material_able-main/products.php?status=deleteerror');
        }
    }

    private function showImage(): void
    {
        $productId = (int) ($_GET['id'] ?? 0);

        if ($productId <= 0) {
            http_response_code(404);
            return;
        }

        $imageBinary = $this->marchandise->fetchImageById($productId);

        if ($imageBinary === false || $imageBinary === null) {
            http_response_code(404);
            return;
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->buffer($imageBinary) ?: 'image/jpeg';

        header('Content-Type: ' . $mimeType);
        echo $imageBinary;
    }

    private function buildProductPayload(): array
    {
        return [
            'id_march' => (int) ($_POST['id_march'] ?? 0),
            'id_mag' => (int) ($_POST['id_mag'] ?? 0),
            'name_march' => (string) ($_POST['name_march'] ?? ''),
            'description_march' => (string) ($_POST['description_march'] ?? ''),
            'price_march' => (int) ($_POST['price_march'] ?? 0),
            'quantity_march' => (int) ($_POST['quantity_march'] ?? 0),
            'date_expiration_march' => (string) ($_POST['date_expiration_march'] ?? ''),
            'point_acces_march' => (string) ($_POST['point_acces_march'] ?? ''),
        ];
    }

    private function redirect(string $location): void
    {
        header('Location: ' . $location);
        exit;
    }
}

(new MarchandiseController())->handle();
