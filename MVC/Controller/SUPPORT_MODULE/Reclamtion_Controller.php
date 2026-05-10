<?php
include_once __DIR__ . '/../../Model/config.php';
include_once __DIR__ . '/../../Model/SUPPORT_MODULE/reclamation.php';


class Controller_reclamation {
    //Ajout reclamation

    public function add_reclamation(Reclamations $reclamation): bool {
        $db = config::getConnexion();
        $userId = (int) $reclamation->getIdUser();
        $dateOverture = trim($reclamation->getDateOverture());
        $dateOverture = $dateOverture !== '' ? $dateOverture : date('Y-m-d');

        if ($userId <= 0) {
            throw new Exception('Missing authenticated user.');
        }

        try {
            $sql = "INSERT INTO reclamation (id_user, description_reclam, etat_reclam, type_reclam, dateouvert_reclam, dateferm_reclam)
                    VALUES (:id_user, :description, :etat, :type, :date_overture, :date_fermiture)";
            $params = [
                'id_user' => $userId,
                'description' => $reclamation->getDescription(),
                'etat' => $reclamation->getEtat(),
                'type' => $reclamation->getType(),
                'date_overture' => $dateOverture,
                'date_fermiture' => null
            ];

            $query = $db->prepare($sql);
            $query->execute($params);
            return true;
        } catch (Exception $e) {
            throw new Exception('Reclamation insert failed: ' . $e->getMessage());
        }
    }

    public function get_reclamations(): array {
        $sql = "SELECT * FROM reclamation ORDER BY dateouvert_reclam DESC, id_reclam DESC";
        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute();
            return $query->fetchAll();
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
            return [];
        }
    }

    public function get_reclamations_by_user(int $userId): array {
        $sql = "SELECT * FROM reclamation WHERE id_user = :id_user ORDER BY dateouvert_reclam DESC, id_reclam DESC";
        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute(['id_user' => $userId]);
            return $query->fetchAll();
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
            return [];
        }
    }

    /**
     * Fetch one claim by primary key. Accepts int or string (e.g. from $_GET['id']).
     */
    public function get_reclamation_by_id(int|string $id): ?array {
        $db = config::getConnexion();
        try {
            $stmt = $db->prepare('SELECT * FROM reclamation WHERE id_reclam = :id LIMIT 1');
            $stmt->execute(['id' => $id]);
            $row = $stmt->fetch();
            return $row !== false ? $row : null;
        } catch (Exception $e) {
            return null;
        }
    }

    //Chnagement reclamation
    public function update_reclamation(Reclamations $reclamation): bool {
        $sql = "update reclamation set  
        description_reclam = :description, etat_reclam = :etat, type_reclam = :type
        where id_reclam = :id_reclamation";
        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute([
                'description' => $reclamation->getDescription(),
                'etat' => $reclamation->getEtat(),
                'type' => $reclamation->getType(),
                'id_reclamation' => $reclamation->getIdReclamation()
            ]);
            return true;
        } catch (Exception $e) {
            throw new Exception('Reclamation update failed: ' . $e->getMessage());
        }
    }

    public function update_reclamation_status_and_close(Reclamations $reclamation): bool {
        $sql = "UPDATE reclamation SET etat_reclam = :etat, dateferm_reclam = :date_fermiture WHERE id_reclam = :id_reclamation";
        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute([
                'etat' => $reclamation->getEtat(),
                'date_fermiture' => $reclamation->getDateFermiture(),
                'id_reclamation' => $reclamation->getIdReclamation()
            ]);
            return true;
        } catch (Exception $e) {
            throw new Exception('Reclamation status update failed: ' . $e->getMessage());
        }
    }

    //Suppression reclamation
    public function suppression_reclamation(Reclamations $reclamation) {
        $sql = "DELETE FROM reclamation WHERE id_reclam = :id_reclamation";
        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute([
                'id_reclamation' => $reclamation->getIdReclamation()
            ]);
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
        }
    }

    public function suppression_reclamation_for_user(Reclamations $reclamation, int $userId): bool {
        $sql = "DELETE FROM reclamation WHERE id_reclam = :id_reclamation AND id_user = :id_user";
        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute([
                'id_reclamation' => $reclamation->getIdReclamation(),
                'id_user' => $userId
            ]);
            return $query->rowCount() > 0;
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
            return false;
        }
    }

    public function get_all_claim_ids(): array {
        $sql = "SELECT id_reclam FROM reclamation";
        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute();
            return $query->fetchAll(PDO::FETCH_COLUMN);
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
            return [];
        }
    }

    public function get_all_user_ids(): array {
        $sql = "SELECT DISTINCT id_user FROM reclamation";
        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute();
            return $query->fetchAll(PDO::FETCH_COLUMN);
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
            return [];
        }
    }
}
?>
