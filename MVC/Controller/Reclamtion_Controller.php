<?php
include(__DIR__ . '/../Model/config.php');
include(__DIR__ . '/../Model/reclamation.php');

class Controller_reclamation {
    //Ajout reclamation

    public function add_reclamation(Reclamations $reclamation) {
        $sql = "INSERT INTO reclamation (id_reclam, id_user, description_reclam, etat_reclam, type_reclam, dateouvert_reclam, dateferm_reclam) 
                VALUES (:id_reclamation, :id_user, :description, :etat, :type, :date_overture,:date_fermiture)";
        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute([
                'id_reclamation' => $reclamation->getIdReclamation(),
                'id_user' => $reclamation->getIdUser(),
                'description' => $reclamation->getDescription(),
                'etat' => $reclamation->getEtat(),
                'type' => $reclamation->getType(),
                'date_overture' => $reclamation->getDateOverture(),
                'date_fermiture'=>null
               
            ]);
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
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
    
    //Chnagement reclamation
    public function update_reclamation(Reclamations $reclamation) {
        $sql = "update reclamation set  
        description_reclam = :description, etat_reclam = :etat, type_reclam = :type, dateouvert_reclam = :date_overture
        where id_reclam = :id_reclamation";
        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute([
                'description' => $reclamation->getDescription(),
                'etat' => $reclamation->getEtat(),
                'type' => $reclamation->getType(),
                'date_overture' => $reclamation->getDateOverture(),
                'id_reclamation' => $reclamation->getIdReclamation()
            ]);
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
        }
    }

    public function get_reclamation_by_id(string $id_reclamation): ?array {
        $sql = "SELECT * FROM reclamation WHERE id_reclam = :id_reclamation";
        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute(['id_reclamation' => $id_reclamation]);
            $result = $query->fetch();
            return $result ? $result : null;
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
            return null;
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
}
?>