<?php
include_once(__DIR__ . '/../../Model/config.php');
include_once(__DIR__ . '/../../Model/menu_module/ingrediant.php');

class Controller_ingrediant {
	private string $lastError = '';

	private function get_next_ingrediant_id($db) {
		$sql = "SELECT COALESCE(MAX(id_ing), 0) + 1 AS next_id FROM ingrediant";
		$query = $db->query($sql);
		return (int)$query->fetchColumn();
	}

	public function getLastError() {
		return $this->lastError;
	}

	public function list_ingrediants() {
		$sql = "SELECT id_ing, name_ing, prot_ing, fat_ing, carb_ing, cal_ing, img_ing
				FROM ingrediant
				ORDER BY id_ing DESC";
		$db = config::getConnexion();

		try {
			$query = $db->query($sql);
			return $query->fetchAll();
		} catch (Exception $e) {
			echo 'Error: ' . $e->getMessage();
			return [];
		}
	}

	public function add_ingrediant(Ingrediant $ingrediant) {
		$db = config::getConnexion();
		$this->lastError = '';

		try {
			$nextIngrediantId = $ingrediant->getIdIng() > 0 ? (int)$ingrediant->getIdIng() : $this->get_next_ingrediant_id($db);
			$sql = "INSERT INTO ingrediant (id_ing, name_ing, prot_ing, fat_ing, carb_ing, cal_ing, img_ing)
					VALUES (:id_ing, :name_ing, :prot_ing, :fat_ing, :carb_ing, :cal_ing, :img_ing)";
			$query = $db->prepare($sql);
			return $query->execute([
				'id_ing' => $nextIngrediantId,
				'name_ing' => $ingrediant->getNameIng(),
				'prot_ing' => $ingrediant->getProtIng(),
				'fat_ing' => $ingrediant->getFatIng(),
				'carb_ing' => $ingrediant->getCarbIng(),
				'cal_ing' => $ingrediant->getCalIng(),
				'img_ing' => $ingrediant->getImgIng()
			]);
		} catch (Exception $e) {
			$this->lastError = $e->getMessage();
			return false;
		}
	}

	public function update_ingrediant(Ingrediant $ingrediant) {
		$sql = "UPDATE ingrediant SET name_ing = :name_ing, prot_ing = :prot_ing, fat_ing = :fat_ing, carb_ing = :carb_ing, cal_ing = :cal_ing, img_ing = :img_ing
				WHERE id_ing = :id_ing";
		$db = config::getConnexion();

		try {
			$query = $db->prepare($sql);
			$query->execute([
				'id_ing' => $ingrediant->getIdIng(),
				'name_ing' => $ingrediant->getNameIng(),
				'prot_ing' => $ingrediant->getProtIng(),
				'fat_ing' => $ingrediant->getFatIng(),
				'carb_ing' => $ingrediant->getCarbIng(),
				'cal_ing' => $ingrediant->getCalIng(),
				'img_ing' => $ingrediant->getImgIng()
			]);
		} catch (Exception $e) {
			echo 'Error: ' . $e->getMessage();
		}
	}

	public function get_ingrediant_by_id($id_ing) {
		$sql = "SELECT id_ing, name_ing, prot_ing, fat_ing, carb_ing, cal_ing, img_ing
				FROM ingrediant
				WHERE id_ing = :id_ing";
		$db = config::getConnexion();

		try {
			$query = $db->prepare($sql);
			$query->execute(['id_ing' => $id_ing]);
			return $query->fetch();
		} catch (Exception $e) {
			echo 'Error: ' . $e->getMessage();
			return false;
		}
	}

	public function delete_ingrediant($id_ing) {
		$sql = "DELETE FROM ingrediant WHERE id_ing = :id_ing";
		$db = config::getConnexion();

		try {
			$query = $db->prepare($sql);
			$query->execute(['id_ing' => $id_ing]);
		} catch (Exception $e) {
			echo 'Error: ' . $e->getMessage();
		}
	}
}
?>
