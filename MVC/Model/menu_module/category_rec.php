<?php
class categ_rec{
    
    private int $id_cat_rec;
    private string $name_cat_rec;
    private string $img_cat_rec;
    private string $color_cat_rec;
    //constructor
    public function __construct(int $id_cat_rec,string $name_cat_rec,string $img_cat_rec,string $color_cat_rec){
        $this->id_cat_rec=$id_cat_rec;
        $this->name_cat_rec=$name_cat_rec;
        $this->img_cat_rec=$img_cat_rec;
        $this->color_cat_rec=$color_cat_rec;

    }

    //getters
    public function getIdCatRec(){
        return $this->id_cat_rec;
    } 
    public function getNameCatRec(){
        return $this->name_cat_rec;
    }
    public function getImgCatRec(){
        return $this->img_cat_rec;
    }
    public function getColorCatRec(){
        return $this->color_cat_rec;
    }

    //setters
    public function setIdCatRec(int $id_cat_rec){
        $this->id_cat_rec=$id_cat_rec;
    }
    public function setNameCatRec(string $name_cat_rec){
        $this->name_cat_rec=$name_cat_rec;
    }
    public function setImgCatRec(string $img_cat_rec){
        $this->img_cat_rec=$img_cat_rec;
    }
    public function setColorCatRec(string $color_cat_rec){
        $this->color_cat_rec=$color_cat_rec;
    }
}