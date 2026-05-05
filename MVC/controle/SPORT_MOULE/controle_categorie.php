<?php

include_once __DIR__ . '/../../model/SPORT_MOULE/categorie.php';
include_once __DIR__ . '/../../model/config.php';

class controle_categorie_workout
{
	public function add_category(Categorie $categorie)
	{
		$db = config::getConnexion();
		$name = trim($categorie->getNameCat());

		try {
			if ($name === '') {
				return 'Category name is required.';
			}

			$existing = $db->prepare(
				"SELECT id_cat
				 FROM work_categorie
				 WHERE LOWER(name_cat) = LOWER(:name_cat)
				 LIMIT 1"
			);
			$existing->execute(['name_cat' => $name]);
			$row = $existing->fetch(PDO::FETCH_ASSOC);

			if ($row && isset($row['id_cat'])) {
				return (int)$row['id_cat'];
			}

			$insert = $db->prepare("INSERT INTO work_categorie (name_cat) VALUES (:name_cat)");
			$insert->execute(['name_cat' => $name]);
			return (int)$db->lastInsertId();
		} catch (Exception $e) {
			return $e->getMessage();
		}
	}

	public function update_category(Categorie $categorie, int $idCat)
	{
		$db = config::getConnexion();
		$name = trim($categorie->getNameCat());

		try {
			if ($idCat <= 0) {
				return 'Invalid category id.';
			}
			if ($name === '') {
				return 'Category name is required.';
			}

			$exists = $db->prepare("SELECT id_cat FROM work_categorie WHERE id_cat = :id_cat LIMIT 1");
			$exists->execute(['id_cat' => $idCat]);
			if (!$exists->fetch(PDO::FETCH_ASSOC)) {
				return 'Category not found.';
			}

			$checkDuplicate = $db->prepare(
				"SELECT id_cat
				 FROM work_categorie
				 WHERE LOWER(name_cat) = LOWER(:name_cat)
				   AND id_cat <> :id_cat
				 LIMIT 1"
			);
			$checkDuplicate->execute([
				'name_cat' => $name,
				'id_cat' => $idCat,
			]);

			if ($checkDuplicate->fetch(PDO::FETCH_ASSOC)) {
				return 'Another category with the same name already exists.';
			}

			$update = $db->prepare("UPDATE work_categorie SET name_cat = :name_cat WHERE id_cat = :id_cat");
			$update->execute([
				'name_cat' => $name,
				'id_cat' => $idCat,
			]);

			return true;
		} catch (Exception $e) {
			return $e->getMessage();
		}
	}

	public function delete_category(int $idCat)
	{
		$db = config::getConnexion();

		try {
			if ($idCat <= 0) {
				return 'Invalid category id.';
			}

			$exists = $db->prepare("SELECT id_cat FROM work_categorie WHERE id_cat = :id_cat LIMIT 1");
			$exists->execute(['id_cat' => $idCat]);
			if (!$exists->fetch(PDO::FETCH_ASSOC)) {
				return 'Category not found.';
			}

			$db->beginTransaction();

			$unlink = $db->prepare("UPDATE workout SET id_cat = NULL WHERE id_cat = :id_cat");
			$unlink->execute(['id_cat' => $idCat]);

			$delete = $db->prepare("DELETE FROM work_categorie WHERE id_cat = :id_cat");
			$delete->execute(['id_cat' => $idCat]);

			$db->commit();
			return true;
		} catch (Exception $e) {
			if ($db->inTransaction()) {
				$db->rollBack();
			}
			return $e->getMessage();
		}
	}

	public function get_category_by_id(int $idCat)
	{
		$db = config::getConnexion();

		try {
			if ($idCat <= 0) {
				return null;
			}

			$stmt = $db->prepare("SELECT id_cat, name_cat FROM work_categorie WHERE id_cat = :id_cat LIMIT 1");
			$stmt->execute(['id_cat' => $idCat]);
			$row = $stmt->fetch(PDO::FETCH_ASSOC);

			if (!$row) {
				return null;
			}

			return $row;
		} catch (Exception $e) {
			return $e->getMessage();
		}
	}

	public function resolve_workout_category_id(int $selectedCategoryId, string $newCategoryName)
	{
		$newCategoryName = trim($newCategoryName);

		if ($newCategoryName !== '') {
			return $this->add_category(new Categorie($newCategoryName));
		}

		if ($selectedCategoryId > 0) {
			$existing = $this->get_category_by_id($selectedCategoryId);
			if (is_array($existing) && isset($existing['id_cat'])) {
				return (int)$existing['id_cat'];
			}

			return 'Selected workout category does not exist.';
		}

		return 'Please select a workout category or create a new one.';
	}
}

