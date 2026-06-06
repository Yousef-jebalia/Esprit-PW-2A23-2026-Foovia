<?php
include_once __DIR__ . '/../../Model/config.php';
include_once __DIR__ . '/../../Model/SUPPORT_MODULE/traitements.php';

class Controller_traitement
{
    public function add_traitement(Traitements $traitement): void
    {
        $sql = "INSERT INTO traitement (id_reclam, comment_trait, status_trait, date__trait, id_user)
                VALUES (:id_reclam, :comment, :status, :date_trait, :id_user)";
        $db = config::getConnexion();

        try {
            $query = $db->prepare($sql);
            $query->execute([
                'id_reclam' => $traitement->getIdReclamation(),
                'comment' => $traitement->getCommentaire(),
                'status' => $traitement->getStatus(),
                'date_trait' => $traitement->getDateTraitemants(),
                'id_user' => $traitement->getIdAdmin(),
            ]);
        } catch (Exception $e) {
            error_log('Controller_traitement::add_traitement failed: ' . $e->getMessage());
        }
    }

    public function get_traitements(): array
    {
        $sql = "SELECT * FROM traitement";
        $db = config::getConnexion();

        try {
            $query = $db->prepare($sql);
            $query->execute();
            return $query->fetchAll();
        } catch (Exception $e) {
            error_log('Controller_traitement::get_traitements failed: ' . $e->getMessage());
            return [];
        }
    }

    public function update_traitement(Traitements $traitement): void
    {
        $sql = "UPDATE traitement SET
                id_reclam = :id_reclam, comment_trait = :comment, status_trait = :status, date__trait = :date_trait, id_user = :id_user
                WHERE id_traitement = :id_traitement";
        $db = config::getConnexion();

        try {
            $query = $db->prepare($sql);
            $query->execute([
                'id_reclam' => $traitement->getIdReclamation(),
                'comment' => $traitement->getCommentaire(),
                'status' => $traitement->getStatus(),
                'date_trait' => $traitement->getDateTraitemants(),
                'id_user' => $traitement->getIdAdmin(),
                'id_traitement' => $traitement->getIdTraitement(),
            ]);
        } catch (Exception $e) {
            error_log('Controller_traitement::update_traitement failed: ' . $e->getMessage());
        }
    }

    public function get_traitement_by_id(string $id_traitement): ?array
    {
        $sql = "SELECT * FROM traitement WHERE id_traitement = :id_traitement";
        $db = config::getConnexion();

        try {
            $query = $db->prepare($sql);
            $query->execute(['id_traitement' => $id_traitement]);
            $result = $query->fetch();
            return $result ?: null;
        } catch (Exception $e) {
            error_log('Controller_traitement::get_traitement_by_id failed: ' . $e->getMessage());
            return null;
        }
    }

    public function suppression_traitement(Traitements $traitement): void
    {
        $sql = "DELETE FROM traitement WHERE id_traitement = :id_traitement";
        $db = config::getConnexion();

        try {
            $query = $db->prepare($sql);
            $query->execute([
                'id_traitement' => $traitement->getIdTraitement(),
            ]);
        } catch (Exception $e) {
            error_log('Controller_traitement::suppression_traitement failed: ' . $e->getMessage());
        }
    }
}
