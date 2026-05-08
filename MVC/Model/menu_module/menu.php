<?php

class Recipe{
    private int $id_rec;
    private string $nom_rec;
    private string $categorie_rec;
    private string $description_rec;
    private float $prot_rec;
    private float $fat_rec;
    private float $carb_rec;
    private float $cal_rec;
    private string $instructions_rec;
    private string $origin_rec;
    private string $imag_rec;

    //constructor
    public function __construct(int $id_rec,string $nom_rec,string $categorie_rec,string $description_rec,float $prot_rec,float $fat_rec,float $carb_rec,float $cal_rec,string $instructions_rec,string $origin_rec,string $imag_rec){
        $this->id_rec=$id_rec;
        $this->nom_rec=$nom_rec;
        $this->categorie_rec=$categorie_rec;
        $this->description_rec=$description_rec;
        $this->prot_rec=$prot_rec;
        $this->fat_rec=$fat_rec;
        $this->carb_rec=$carb_rec;
        $this->cal_rec=$cal_rec;
        $this->instructions_rec=$instructions_rec;
        $this->origin_rec=$origin_rec;
        $this->imag_rec=$imag_rec;

    }

    //getters
    public function getIdRec(){
        return $this->id_rec;
    }
    public function getNomRec(){
        return $this->nom_rec;
    }
    public function getCategorieRec(){
        return $this->categorie_rec;
    }
    public function getDescriptionRec(){
        return $this->description_rec;
    }
    public function getProtRec(){
        return $this->prot_rec;
    }
    public function getFatRec(){
        return $this->fat_rec;
    }
    public function getCarbRec(){
        return $this->carb_rec;
    }
    public function getCalRec(){
        return $this->cal_rec;
    }
    public function getInstructionsRec(){
        return $this->instructions_rec;
    }
    public function getOriginRec(){
        return $this->origin_rec;
    }
    public function getImagRec(){
        return $this->imag_rec;
    }

    //setters
    public function setIdRec(int $id_rec){
        $this->id_rec=$id_rec;
    }
    public function setNomRec(string $nom_rec){
        $this->nom_rec=$nom_rec;
    }
    public function setCategorieRec(string $categorie_rec){
        $this->categorie_rec=$categorie_rec;
    }
    public function setDescriptionRec(string $description_rec){
        $this->description_rec=$description_rec;
    }
    public function setProtRec(float $prot_rec){
        $this->prot_rec=$prot_rec;
    }
    public function setFatRec(float $fat_rec){
        $this->fat_rec=$fat_rec;
    }
    public function setCarbRec(float $carb_rec){
        $this->carb_rec=$carb_rec;
    }
    public function setCalRec(float $cal_rec){
        $this->cal_rec=$cal_rec;
    }
    public function setInstructionsRec(string $instructions_rec){
        $this->instructions_rec=$instructions_rec;
    }
    public function setOriginRec(string $origin_rec){
        $this->origin_rec=$origin_rec;
    }
    public function setImagRec(string $imag_rec){
        $this->imag_rec=$imag_rec;
    }



}