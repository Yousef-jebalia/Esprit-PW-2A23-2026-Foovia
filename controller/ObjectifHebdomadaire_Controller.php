<?php
require_once(__DIR__ . '/../model/config.php');
require_once(__DIR__ . '/../model/ObjectifHebdomadaire.php');

class ObjectifHebdomadaire_Controller {

    public function add_objHebdo(ObjectifHebdomadaire $objHebdo) {
        $sql = "INSERT INTO objectifhebdomadaire (id_obj, date_suiv, val_cal_suiv, val_fat_suiv, val_prot_suiv, val_carb_suiv, note_suiv, status_obj_quot_suiv, nb_verre_eau_suiv, nb_h_sommeil_suiv, nb_pas_suiv, id_user) 
                VALUES (:id_obj, :date_suiv, :val_cal_suiv, :val_fat_suiv, :val_prot_suiv, :val_carb_suiv, :note_suiv, :status_obj_quot_suiv, :nb_verre_eau_suiv, :nb_h_sommeil_suiv, :nb_pas_suiv, :id_user)";
        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute([
                'id_obj' => $objHebdo->getIdObj(),
                'date_suiv' => $objHebdo->getDateSuiv(),
                'val_cal_suiv' => $objHebdo->getValCalSuiv(),
                'val_fat_suiv' => $objHebdo->getValFatSuiv(),
                'val_prot_suiv' => $objHebdo->getValProtSuiv(),
                'val_carb_suiv' => $objHebdo->getValCarbSuiv(),
                'note_suiv' => $objHebdo->getNoteSuiv(),
                'status_obj_quot_suiv' => $objHebdo->getStatusObjQuotSuiv(),
                'nb_verre_eau_suiv' => $objHebdo->getNbVerreEauSuiv(),
                'nb_h_sommeil_suiv' => $objHebdo->getNbHSommeilSuiv(),
                'nb_pas_suiv' => $objHebdo->getNbPasSuiv(),
                'id_user' => $objHebdo->getIdUser()

                
            ]);
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
        }
    }

    public function get_objectif_by_user_and_date(int $id_user, string $date_suiv): ?array {
        $sql = "SELECT * FROM objectifhebdomadaire WHERE id_user = :id_user AND date_suiv = :date_suiv LIMIT 1";
        $db = config::getConnexion();

        try {
            $query = $db->prepare($sql);
            $query->execute([
                'id_user' => $id_user,
                'date_suiv' => $date_suiv,
            ]);

            $result = $query->fetch();
            return $result ?: null;
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
            return null;
        }
    }

    public function save_objectif_hebdo(array $data, ?int $id_suiv = null): bool {
        $db = config::getConnexion();

        try {
            if ($id_suiv !== null) {
                $sql = "UPDATE objectifhebdomadaire
                        SET id_obj = :id_obj,
                            date_suiv = :date_suiv,
                            val_cal_suiv = :val_cal_suiv,
                            val_fat_suiv = :val_fat_suiv,
                            val_prot_suiv = :val_prot_suiv,
                            val_carb_suiv = :val_carb_suiv,
                            note_suiv = :note_suiv,
                            status_obj_quot_suiv = :status_obj_quot_suiv,
                            nb_verre_eau_suiv = :nb_verre_eau_suiv,
                            nb_h_sommeil_suiv = :nb_h_sommeil_suiv,
                            nb_pas_suiv = :nb_pas_suiv,
                            id_user = :id_user
                        WHERE id_suiv = :id_suiv AND id_user = :id_user";
                $query = $db->prepare($sql);
                return $query->execute([
                    'id_suiv' => $id_suiv,
                    'id_obj' => $data['id_obj'],
                    'date_suiv' => $data['date_suiv'],
                    'val_cal_suiv' => $data['val_cal_suiv'],
                    'val_fat_suiv' => $data['val_fat_suiv'],
                    'val_prot_suiv' => $data['val_prot_suiv'],
                    'val_carb_suiv' => $data['val_carb_suiv'],
                    'note_suiv' => $data['note_suiv'],
                    'status_obj_quot_suiv' => $data['status_obj_quot_suiv'],
                    'nb_verre_eau_suiv' => $data['nb_verre_eau_suiv'],
                    'nb_h_sommeil_suiv' => $data['nb_h_sommeil_suiv'],
                    'nb_pas_suiv' => $data['nb_pas_suiv'],
                    'id_user' => $data['id_user'],
                ]);
            }

            $sql = "INSERT INTO objectifhebdomadaire (
                        id_obj,
                        date_suiv,
                        val_cal_suiv,
                        val_fat_suiv,
                        val_prot_suiv,
                        val_carb_suiv,
                        note_suiv,
                        status_obj_quot_suiv,
                        nb_verre_eau_suiv,
                        nb_h_sommeil_suiv,
                        nb_pas_suiv,
                        id_user
                    ) VALUES (
                        :id_obj,
                        :date_suiv,
                        :val_cal_suiv,
                        :val_fat_suiv,
                        :val_prot_suiv,
                        :val_carb_suiv,
                        :note_suiv,
                        :status_obj_quot_suiv,
                        :nb_verre_eau_suiv,
                        :nb_h_sommeil_suiv,
                        :nb_pas_suiv,
                        :id_user
                    )";
            $query = $db->prepare($sql);
            return $query->execute([
                'id_obj' => $data['id_obj'],
                'date_suiv' => $data['date_suiv'],
                'val_cal_suiv' => $data['val_cal_suiv'],
                'val_fat_suiv' => $data['val_fat_suiv'],
                'val_prot_suiv' => $data['val_prot_suiv'],
                'val_carb_suiv' => $data['val_carb_suiv'],
                'note_suiv' => $data['note_suiv'],
                'status_obj_quot_suiv' => $data['status_obj_quot_suiv'],
                'nb_verre_eau_suiv' => $data['nb_verre_eau_suiv'],
                'nb_h_sommeil_suiv' => $data['nb_h_sommeil_suiv'],
                'nb_pas_suiv' => $data['nb_pas_suiv'],
                'id_user' => $data['id_user'],
            ]);
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
            return false;
        }
    }

    public function delete_objectif_hebdo(int $id_suiv, int $id_user): bool {
        $sql = "DELETE FROM objectifhebdomadaire WHERE id_suiv = :id_suiv AND id_user = :id_user";
        $db = config::getConnexion();

        try {
            $query = $db->prepare($sql);
            return $query->execute([
                'id_suiv' => $id_suiv,
                'id_user' => $id_user,
            ]);
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
            return false;
        }
    }
    

    // Add other methods like list_objHebdo, delete_objHebdo, etc. as needed
}
?>