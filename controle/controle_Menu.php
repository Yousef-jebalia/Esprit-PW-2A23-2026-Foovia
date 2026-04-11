<?php
include(__DIR__ . '/../model/config.php');
include(__DIR__ . '/../model/menu.php');

class Controller_menu {

    public function list_recipe() {
        $sql = "SELECT id_rec, name_rec, categorie_rec, description_rec, prot_rec, fat_rec, carb_rec, cal_rec, instruction_rec, origin_rec, img_rec 
                FROM recipe 
                ORDER BY id_rec DESC";
        $db = config::getConnexion();

        try {
            $query = $db->query($sql);
            return $query->fetchAll();
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
            return [];
        }
    }

    public function add_recipe(Recipe $recipe) {
        $sql = "INSERT INTO recipe (id_rec, name_rec, categorie_rec, description_rec, prot_rec, fat_rec, carb_rec, cal_rec, instruction_rec, origin_rec, img_rec) 
                VALUES (:id_recipe, :nom, :categorie, :description, :prot, :fat, :carb, :cal, :instructions, :origin, :imag)";
        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute([
                'id_recipe' => $recipe->getIdRec(),
                'nom' => $recipe->getNomRec(),
                'categorie' => $recipe->getCategorieRec(),
                'description' => $recipe->getDescriptionRec(),
                'prot' => $recipe->getProtRec(),
                'fat' => $recipe->getFatRec(),
                'carb' => $recipe->getCarbRec(),
                'cal' => $recipe->getCalRec(),
                'instructions' => $recipe->getInstructionsRec(),
                'origin' => $recipe->getOriginRec(),
                'imag' => $recipe->getImagRec()
            ]);
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
        }
    }

    public function update_recipe(Recipe $recipe) {
        $sql = "UPDATE recipe SET name_rec = :nom, categorie_rec = :categorie, description_rec = :description, prot_rec = :prot, fat_rec = :fat, carb_rec = :carb, cal_rec = :cal, instruction_rec = :instructions, origin_rec = :origin, img_rec = :imag 
                WHERE id_rec = :id_recipe";
        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute([
                'id_recipe' => $recipe->getIdRec(),
                'nom' => $recipe->getNomRec(),
                'categorie' => $recipe->getCategorieRec(),
                'description' => $recipe->getDescriptionRec(),
                'prot' => $recipe->getProtRec(),
                'fat' => $recipe->getFatRec(),
                'carb' => $recipe->getCarbRec(),
                'cal' => $recipe->getCalRec(),
                'instructions' => $recipe->getInstructionsRec(),
                'origin' => $recipe->getOriginRec(),
                'imag' => $recipe->getImagRec()
            ]);
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
        }
    }

    public function get_recipe_by_id($id_rec) {
        $sql = "SELECT id_rec, name_rec, categorie_rec, description_rec, prot_rec, fat_rec, carb_rec, cal_rec, instruction_rec, origin_rec, img_rec
                FROM recipe
                WHERE id_rec = :id_recipe";
        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute(['id_recipe' => $id_rec]);
            return $query->fetch();
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
            return false;
        }
    }

    public function delete_recipe($id_rec) {
        $sql = "DELETE FROM recipe WHERE id_rec = :id_recipe";
        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute(['id_recipe' => $id_rec]);
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
        }
    }

    
    

    // Add other methods like list_reclamations, delete_reclamation, etc. as needed
}
?>