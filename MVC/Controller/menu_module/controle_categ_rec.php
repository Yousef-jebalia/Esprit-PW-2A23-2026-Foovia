<?php
include_once(__DIR__ . '/../../Model/config.php');
include_once(__DIR__ . '/../../Model/menu_module/category_rec.php');
class controle_categ_rec{
    private function get_next_categ_rec_id($db){
        $sql="SELECT COALESCE(MAX(id_categ_rec),0) + 1 AS next_id FROM categorie_recipe";
        $query=$db->query($sql);
        return (int)$query->fetchColumn();
    }

    public function list_categ_rec(){
        $sql="SELECT * FROM categorie_recipe";
        $db=config::getConnexion();
        try{
            $query=$db->query($sql);
            return $query->fetchAll();
        }catch(Exception $e){
            echo 'Error: '.$e->getMessage();
            return [];

        }
    }
    public function get_categ_rec_by_id($id_categ_rec){
        $sql="SELECT * FROM categorie_recipe WHERE 	id_categ_rec=:id_categ_rec";
        $db=config::getConnexion();
        try{
            $query=$db->prepare($sql);
            $query->execute(['id_categ_rec'=>$id_categ_rec]);
            return $query->fetch();
        }catch(Exception $e){
            echo 'Error: '.$e->getMessage();
            return null;

        }
    }
    public function add_categ_rec(categ_rec $categ_rec){
        $sql="INSERT INTO categorie_recipe (id_categ_rec,nom_categ,photo_categ,color_categ) VALUES (:id_categ_rec,:name_cat_rec,:img_cat_rec,:color_cat_rec)";
        $db=config::getConnexion();
        try{
            $nextCategoryId = $this->get_next_categ_rec_id($db);
            $query=$db->prepare($sql);
            return $query->execute([
                'id_categ_rec'=>$nextCategoryId,
                'name_cat_rec'=>$categ_rec->getNameCatRec(),
                'img_cat_rec'=>$categ_rec->getImgCatRec(),
                'color_cat_rec'=>$categ_rec->getColorCatRec()
            ]);
        }catch(Exception $e){
            echo 'Error: '.$e->getMessage();
            return false;

        }
    }
    public function delete_categ_rec($id_categ_rec){
        $sql="DELETE FROM categorie_recipe WHERE id_categ_rec=:id_categ_rec";
        $db=config::getConnexion();
        try{
            $query=$db->prepare($sql);
            return $query->execute(['id_categ_rec'=>$id_categ_rec]);
        }catch(Exception $e){
            echo 'Error: '.$e->getMessage();
            return false;

        }
    }
    public function update_categ_rec(categ_rec $categ_rec){
        $sql="UPDATE categorie_recipe SET nom_categ=:name_cat_rec,img_cat_rec=:photo_categ,color_categ=:color_cat_rec WHERE id_categ_rec=:id_categ_rec";
        $db=config::getConnexion();
        try{
            $query=$db->prepare($sql);
            return $query->execute([
                'id_categ_rec'=>$categ_rec->getIdCatRec(),
                'name_cat_rec'=>$categ_rec->getNameCatRec(),
                'img_cat_rec'=>$categ_rec->getImgCatRec(),
                'color_cat_rec'=>$categ_rec->getColorCatRec()
            ]);
        }catch(Exception $e){
            echo 'Error: '.$e->getMessage();
            return false;

        }
    }
    public function get_rec_cat_id($id_rec){
        $sql="SELECT id_categ_rec FROM affecter_categ_rec a,recipe r WHERE a.id_rec=r.id_rec AND r.id_rec=:id_rec";
        $db=config::getConnexion();
        try{
            $query=$db->prepare($sql);
            $query->execute(['id_rec'=>$id_rec]);
            return $query->fetchColumn();
        }catch(Exception $e){
            echo 'Error: '.$e->getMessage();
            return null;

        }
    }

    public function get_rec_cat_ids($id_rec){
        $sql="SELECT id_categ_rec FROM affecter_categ_rec WHERE id_rec=:id_rec ORDER BY id_categ_rec ASC";
        $db=config::getConnexion();
        try{
            $query=$db->prepare($sql);
            $query->execute(['id_rec'=>$id_rec]);
            return $query->fetchAll(PDO::FETCH_COLUMN);
        }catch(Exception $e){
            echo 'Error: '.$e->getMessage();
            return [];

        }
    }

    public function delete_affecter_categ_rec_by_recipe($id_rec){
        $sql="DELETE FROM affecter_categ_rec WHERE id_rec=:id_rec";
        $db=config::getConnexion();
        try{
            $query=$db->prepare($sql);
            return $query->execute(['id_rec'=>$id_rec]);
        }catch(Exception $e){
            echo 'Error: '.$e->getMessage();
            return false;

        }
    }

    public function get_categ_id_by_name($name_cat_rec){
        $sql="SELECT id_categ_rec FROM categorie_recipe WHERE nom_categ=:name_cat_rec LIMIT 1";
        $db=config::getConnexion();
        try{
            $query=$db->prepare($sql);
            $query->execute(['name_cat_rec'=>$name_cat_rec]);
            return $query->fetchColumn();
        }catch(Exception $e){
            echo 'Error: '.$e->getMessage();
            return null;

        }
    }

    public function affecter_categ_rec($id_rec,$id_categ_rec){
        $sql="INSERT INTO affecter_categ_rec (id_rec,id_categ_rec) VALUES (:id_rec,:id_categ_rec)";
        $db=config::getConnexion();
        try{
            $query=$db->prepare($sql);
            return $query->execute([
                'id_rec'=>$id_rec,
                'id_categ_rec'=>$id_categ_rec
            ]);
        }catch(Exception $e){
            echo 'Error: '.$e->getMessage();
            return false;

        }
    }

    public function count_recipes_by_category($id_categ_rec){
        $sql="SELECT COUNT(*) FROM affecter_categ_rec WHERE id_categ_rec = :id_categ_rec";
        $db=config::getConnexion();
        try{
            $query=$db->prepare($sql);
            $query->execute(['id_categ_rec'=>$id_categ_rec]);
            return (int)$query->fetchColumn();
        }catch(Exception $e){
            echo 'Error: '.$e->getMessage();
            return 0;
        }
    }
}