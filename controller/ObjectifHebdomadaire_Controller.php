<?php
require_once(__DIR__ . '/../model/config.php');
require_once(__DIR__ . '/../model/ObjectifHebdomadaire.php');

class ObjectifHebdomadaire_Controller {

    public function add_objHebdo(ObjectifHebdomadaire $objHebdo) {
        $sql = "INSERT INTO objectifhebdomadaire (id_suiv, id_obj, date_suiv, val_cal_suiv, val_fat_suiv, val_prot_suiv, val_carb_suiv, note_suiv, status_obj_quot_suiv, nb_verre_eau_suiv, nb_h_sommeil_suiv, nb_pas_suiv, id_user) 
                VALUES (:id_suiv, :id_obj, :date_suiv, :val_cal_suiv, :val_fat_suiv, :val_prot_suiv, :val_carb_suiv, :note_suiv, :status_obj_quot_suiv, :nb_verre_eau_suiv, :nb_h_sommeil_suiv, :nb_pas_suiv, :id_user)";
        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute([
                'id_suiv' => $objHebdo->getIdSuiv(),
                'id_obj' => $objHebdo->getIdObj(),
                'date_suiv' => $objectifHebdo->getDateSuiv(),
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
    

    // Add other methods like list_objHebdo, delete_objHebdo, etc. as needed
}
?>