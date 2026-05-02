<?php
include_once(__DIR__ . '/../Model/config.php');
include(__DIR__ . '/../Model/reclamation.php');


class Controller_reclamation {
    //Ajout reclamation

    public function add_reclamation(Reclamations $reclamation): bool {
        $db = config::getConnexion();
        $dateOverture = trim($reclamation->getDateOverture());
        try {
            if ($dateOverture === '') {
                $sql = "INSERT INTO reclamation (id_user,description_reclam, etat_reclam, type_reclam, dateferm_reclam) 
                        VALUES (:id_user,:description, :etat, :type, :date_fermiture)";
                $params = [
                    'id_user'=>2,
                    'description' => $reclamation->getDescription(),
                    'etat' => $reclamation->getEtat(),
                    'type' => $reclamation->getType(),
                    'date_fermiture' => null
                ];
            } else {
                $sql = "INSERT INTO reclamation (description_reclam, etat_reclam, type_reclam, dateouvert_reclam, dateferm_reclam) 
                        VALUES (:description, :etat, :type, :date_overture, :date_fermiture)";
                $params = [
                    'description' => $reclamation->getDescription(),
                    'etat' => $reclamation->getEtat(),
                    'type' => $reclamation->getType(),
                    'date_overture' => $dateOverture,
                    'date_fermiture' => null
                ];
            }

            $query = $db->prepare($sql);
            $query->execute($params);
            return true;
        } catch (Exception $e) {
            throw new Exception('Reclamation insert failed: ' . $e->getMessage());
        }
    }

    public function get_reclamations(): array {
        $sql = "SELECT * FROM reclamation";
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