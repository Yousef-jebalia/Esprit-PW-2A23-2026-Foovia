<?php
include_once(__DIR__ . '/../../Model/config.php');
include_once(__DIR__ . '/../../Model/menu_module/menu.php');

class Controller_menu {
    private function ensure_recipe_favorite_table(PDO $db): void {
        $db->exec(
            "CREATE TABLE IF NOT EXISTS recipe_favorite (
                id_user INT NOT NULL,
                id_rec INT NOT NULL,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id_user, id_rec)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci"
        );
    }

    public function is_recipe_favorited_by_user(int $id_user, int $id_rec): bool {
        $db = config::getConnexion();

        try {
            $this->ensure_recipe_favorite_table($db);
            $query = $db->prepare("SELECT 1 FROM recipe_favorite WHERE id_user = :id_user AND id_rec = :id_rec LIMIT 1");
            $query->execute([
                'id_user' => $id_user,
                'id_rec' => $id_rec,
            ]);

            return (bool)$query->fetchColumn();
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
            return false;
        }
    }

    public function add_recipe_to_user_favorites(int $id_user, int $id_rec): bool {
        $db = config::getConnexion();

        try {
            $this->ensure_recipe_favorite_table($db);
            $existsQuery = $db->prepare("SELECT 1 FROM recipe_favorite WHERE id_user = :id_user AND id_rec = :id_rec LIMIT 1");
            $existsQuery->execute([
                'id_user' => $id_user,
                'id_rec' => $id_rec,
            ]);

            if ($existsQuery->fetchColumn()) {
                return true;
            }

            $insertQuery = $db->prepare("INSERT INTO recipe_favorite (id_user, id_rec) VALUES (:id_user, :id_rec)");
            return $insertQuery->execute([
                'id_user' => $id_user,
                'id_rec' => $id_rec,
            ]);
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
            return false;
        }
    }

    public function remove_recipe_from_user_favorites(int $id_user, int $id_rec): bool {
        $db = config::getConnexion();

        try {
            $this->ensure_recipe_favorite_table($db);
            $query = $db->prepare("DELETE FROM recipe_favorite WHERE id_user = :id_user AND id_rec = :id_rec");
            return $query->execute([
                'id_user' => $id_user,
                'id_rec' => $id_rec,
            ]);
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
            return false;
        }
    }

    private function get_next_recipe_id($db) {
        $sql = "SELECT COALESCE(MAX(id_rec), 0) + 1 AS next_id FROM recipe";
        $query = $db->query($sql);
        return (int)$query->fetchColumn();
    }

    public function list_recipe() {
        $sql = "SELECT r.id_rec, r.name_rec, COALESCE(GROUP_CONCAT(DISTINCT c.nom_categ ORDER BY c.nom_categ SEPARATOR ', '), '') AS categorie_rec, r.description_rec, r.prot_rec, r.fat_rec, r.carb_rec, r.cal_rec, r.instruction_rec, r.origin_rec, r.img_rec 
            FROM recipe r
            LEFT JOIN affecter_categ_rec a ON a.id_rec = r.id_rec
            LEFT JOIN categorie_recipe c ON c.id_categ_rec = a.id_categ_rec
            GROUP BY r.id_rec, r.name_rec, r.description_rec, r.prot_rec, r.fat_rec, r.carb_rec, r.cal_rec, r.instruction_rec, r.origin_rec, r.img_rec
            ORDER BY r.id_rec DESC";
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
        $sql = "INSERT INTO recipe (id_rec, name_rec, description_rec, prot_rec, fat_rec, carb_rec, cal_rec, instruction_rec, origin_rec, img_rec) 
            VALUES (:id_recipe, :nom, :description, :prot, :fat, :carb, :cal, :instructions, :origin, :imag)";
        $db = config::getConnexion();
        try {
            $nextRecipeId = $this->get_next_recipe_id($db);
            $query = $db->prepare($sql);
            $ok = $query->execute([
                'id_recipe' => $nextRecipeId,
                'nom' => $recipe->getNomRec(),
                'description' => $recipe->getDescriptionRec(),
                'prot' => $recipe->getProtRec(),
                'fat' => $recipe->getFatRec(),
                'carb' => $recipe->getCarbRec(),
                'cal' => $recipe->getCalRec(),
                'instructions' => $recipe->getInstructionsRec(),
                'origin' => $recipe->getOriginRec(),
                'imag' => $recipe->getImagRec()
            ]);
            if (!$ok) {
                return false;
            }
            return $nextRecipeId;
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
            return false;
        }
    }

    public function add_recipe_ingredients($recipeId, array $ingredients) {
        $db = config::getConnexion();

        try {
            $deleteQuery = $db->prepare("DELETE FROM contenir WHERE id_rec = :id_rec");
            $deleteQuery->execute(['id_rec' => $recipeId]);

            if (empty($ingredients)) {
                return true;
            }

            $insertQuery = $db->prepare("INSERT INTO contenir (id_rec, id_ing, quantity, unity) VALUES (:id_rec, :id_ing, :quantity, :unity)");

            foreach ($ingredients as $ingredientRow) {
                $ingredientId = isset($ingredientRow['id_ing']) ? (int)$ingredientRow['id_ing'] : 0;
                $quantity = isset($ingredientRow['quantity']) ? trim((string)$ingredientRow['quantity']) : '';
                $unity = isset($ingredientRow['unity']) ? trim((string)$ingredientRow['unity']) : '';

                if ($ingredientId <= 0 || $quantity === '' || $unity === '') {
                    continue;
                }

                $insertQuery->execute([
                    'id_rec' => (int)$recipeId,
                    'id_ing' => $ingredientId,
                    'quantity' => $quantity,
                    'unity' => $unity,
                ]);
            }

            return true;
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
            return false;
        }
    }

    public function update_recipe(Recipe $recipe) {
        $sql = "UPDATE recipe SET name_rec = :nom, description_rec = :description, prot_rec = :prot, fat_rec = :fat, carb_rec = :carb, cal_rec = :cal, instruction_rec = :instructions, origin_rec = :origin, img_rec = :imag 
            WHERE id_rec = :id_recipe";
        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute([
                'id_recipe' => $recipe->getIdRec(),
                'nom' => $recipe->getNomRec(),
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
        $sql = "SELECT r.id_rec, r.name_rec, COALESCE(GROUP_CONCAT(DISTINCT c.nom_categ ORDER BY c.nom_categ SEPARATOR ', '), '') AS categorie_rec, r.description_rec, r.prot_rec, r.fat_rec, r.carb_rec, r.cal_rec, r.instruction_rec, r.origin_rec, r.img_rec
            FROM recipe r
            LEFT JOIN affecter_categ_rec a ON a.id_rec = r.id_rec
            LEFT JOIN categorie_recipe c ON c.id_categ_rec = a.id_categ_rec
            WHERE r.id_rec = :id_recipe
            GROUP BY r.id_rec, r.name_rec, r.description_rec, r.prot_rec, r.fat_rec, r.carb_rec, r.cal_rec, r.instruction_rec, r.origin_rec, r.img_rec";
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

    public function get_recipe_ingredients($id_rec) {
        $sql = "SELECT ct.id_rec, ct.id_ing, ct.quantity, ct.unity, i.name_ing, i.img_ing
                FROM contenir ct
                LEFT JOIN ingrediant i ON i.id_ing = ct.id_ing
                WHERE ct.id_rec = :id_recipe
                ORDER BY ct.id_ing ASC";
        $db = config::getConnexion();

        try {
            $query = $db->prepare($sql);
            $query->execute(['id_recipe' => (int)$id_rec]);
            return $query->fetchAll();
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
            return [];
        }
    }

    public function delete_recipe($id_rec) {
        $db = config::getConnexion();
        try {
            $query = $db->prepare("DELETE FROM contenir WHERE id_rec = :id_recipe");
            $query->execute(['id_recipe' => $id_rec]);

            $query = $db->prepare("DELETE FROM affecter_categ_rec WHERE id_rec = :id_recipe");
            $query->execute(['id_recipe' => $id_rec]);

            $query = $db->prepare("DELETE FROM recipe WHERE id_rec = :id_recipe");
            $query->execute(['id_recipe' => $id_rec]);
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
        }
    }

    
    

    // Add other methods like list_reclamations, delete_reclamation, etc. as needed
}
?>
