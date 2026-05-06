<?php
require_once(__DIR__ . '/../../Model/config.php');
require_once(__DIR__ . '/../../Model/tracking/ObjectifHebdomadaire.php');

class ObjectifHebdomadaire_Controller {

    private const MAX_MACRO_VALUE = 9999;

    private function areMacronutrientsPositive(float $fat, float $protein, float $carbs): bool {
        return $fat > 0 && $protein > 0 && $carbs > 0;
    }

    private function areMacronutrientsWithinLimit(float $fat, float $protein, float $carbs): bool {
        return $fat <= self::MAX_MACRO_VALUE && $protein <= self::MAX_MACRO_VALUE && $carbs <= self::MAX_MACRO_VALUE;
    }

    public function add_objHebdo(ObjectifHebdomadaire $objHebdo) {
        if (!$this->areMacronutrientsPositive($objHebdo->getValFatSuiv(), $objHebdo->getValProtSuiv(), $objHebdo->getValCarbSuiv()) || !$this->areMacronutrientsWithinLimit($objHebdo->getValFatSuiv(), $objHebdo->getValProtSuiv(), $objHebdo->getValCarbSuiv())) {
            throw new InvalidArgumentException('Macronutrients must be strictly positive and no greater than 9999.');
        }

        $sql = "INSERT INTO objectifhebdomadaire (id_suiv, id_obj, date_suiv, val_cal_suiv, poids_suiv, val_fat_suiv, val_prot_suiv, val_carb_suiv, note_suiv, status_obj_quot_suiv, nb_verre_eau_suiv, nb_h_sommeil_suiv, nb_pas_suiv, id_user) 
            VALUES (:id_suiv, :id_obj, :date_suiv, :val_cal_suiv, :poids_suiv, :val_fat_suiv, :val_prot_suiv, :val_carb_suiv, :note_suiv, :status_obj_quot_suiv, :nb_verre_eau_suiv, :nb_h_sommeil_suiv, :nb_pas_suiv, :id_user)";
        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute([
                'id_suiv' => $objHebdo->getIdSuiv(),
                'id_obj' => $objHebdo->getIdObj(),
                'date_suiv' => $objHebdo->getDateSuiv(),
                'val_cal_suiv' => $objHebdo->getValCalSuiv(),
                'poids_suiv' => $objHebdo->getPoidsSuiv(),
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

    public function get_next_suivi_id(): int {
        $sql = "SELECT COALESCE(MAX(id_suiv), 0) + 1 AS next_id FROM objectifhebdomadaire";
        $db = config::getConnexion();

        try {
            $query = $db->query($sql);
            $result = $query->fetch();
            return isset($result['next_id']) ? (int) $result['next_id'] : 1;
        } catch (Exception $e) {
            return 1;
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

    public function get_recent_objectifs_by_user(int $id_user, int $days = 7): array {
        $safeDays = max(1, $days);
        $sql = "SELECT *
                FROM objectifhebdomadaire
                WHERE id_user = :id_user
                ORDER BY date_suiv DESC
                LIMIT " . (int) $safeDays;
        $db = config::getConnexion();

        try {
            $query = $db->prepare($sql);
            $query->execute([
                'id_user' => $id_user,
            ]);

            $rows = $query->fetchAll();
            return is_array($rows) ? $rows : [];
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
            return [];
        }
    }

    public function list_objectifs_by_user(int $id_user): array {
        $sql = "SELECT *
                FROM objectifhebdomadaire
                WHERE id_user = :id_user
                ORDER BY date_suiv DESC, id_suiv DESC";
        $db = config::getConnexion();

        try {
            $query = $db->prepare($sql);
            $query->execute([
                'id_user' => $id_user,
            ]);

            $rows = $query->fetchAll();
            return is_array($rows) ? $rows : [];
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
            return [];
        }
    }

    public function save_objectif_hebdo(array $data, ?int $id_suiv = null): bool {
        $db = config::getConnexion();

        $valFatSuiv = (float) ($data['val_fat_suiv'] ?? 0);
        $valProtSuiv = (float) ($data['val_prot_suiv'] ?? 0);
        $valCarbSuiv = (float) ($data['val_carb_suiv'] ?? 0);

        if (!$this->areMacronutrientsPositive($valFatSuiv, $valProtSuiv, $valCarbSuiv) || !$this->areMacronutrientsWithinLimit($valFatSuiv, $valProtSuiv, $valCarbSuiv)) {
            return false;
        }

        try {
            if ($id_suiv !== null) {
                $sql = "UPDATE objectifhebdomadaire
                        SET id_obj = :id_obj,
                            date_suiv = :date_suiv,
                            val_cal_suiv = :val_cal_suiv,
                            poids_suiv = :poids_suiv,
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
                    'poids_suiv' => $data['poids_suiv'],
                    'val_fat_suiv' => $valFatSuiv,
                    'val_prot_suiv' => $valProtSuiv,
                    'val_carb_suiv' => $valCarbSuiv,
                    'note_suiv' => $data['note_suiv'],
                    'status_obj_quot_suiv' => $data['status_obj_quot_suiv'],
                    'nb_verre_eau_suiv' => $data['nb_verre_eau_suiv'],
                    'nb_h_sommeil_suiv' => $data['nb_h_sommeil_suiv'],
                    'nb_pas_suiv' => $data['nb_pas_suiv'],
                    'id_user' => $data['id_user'],
                ]);
            }

            $new_id_suiv = $this->get_next_suivi_id();
            
            $sql = "INSERT INTO objectifhebdomadaire (
                        id_suiv,
                        id_obj,
                        date_suiv,
                        val_cal_suiv,
                        poids_suiv,
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
                        :id_suiv,
                        :id_obj,
                        :date_suiv,
                        :val_cal_suiv,
                        :poids_suiv,
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
                'id_suiv' => $new_id_suiv,
                'id_obj' => $data['id_obj'],
                'date_suiv' => $data['date_suiv'],
                'val_cal_suiv' => $data['val_cal_suiv'],
                'poids_suiv' => $data['poids_suiv'],
                'val_fat_suiv' => $valFatSuiv,
                'val_prot_suiv' => $valProtSuiv,
                'val_carb_suiv' => $valCarbSuiv,
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

    public function save_logged_meals(int $id_suiv, array $meals): bool {
        $db = config::getConnexion();
        try {
            $db->prepare("DELETE FROM log_meal WHERE id_suiv = :id_suiv")->execute(['id_suiv' => $id_suiv]);
            
            if (empty($meals)) {
                return true;
            }

            $sql = "INSERT INTO log_meal (id_rec, id_suiv, meal_time, meal_type, meal_image) VALUES (:id_rec, :id_suiv, :meal_time, :meal_type, :meal_image)";
            $query = $db->prepare($sql);

            foreach ($meals as $meal) {
                $id_rec = isset($meal['id_rec']) ? (int)$meal['id_rec'] : 0;
                $query->execute([
                    'id_rec' => $id_rec,
                    'id_suiv' => $id_suiv,
                    'meal_time' => date('H:i:s'),
                    'meal_type' => 'Snack',
                    'meal_image' => ''
                ]);
            }
            return true;
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