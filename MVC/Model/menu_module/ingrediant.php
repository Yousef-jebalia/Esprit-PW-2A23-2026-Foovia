<?php
class Ingrediant{
    
    private int $id_ing;
    private string $name_ing;
    private float $prot_ing;
    private string $fat_ing;
    private string $carb_ing;
    private string $cal_ing;
    private string $img_ing;
    //constructor
    public function __construct(int $id_ing,string $name_ing,float $prot_ing,string $fat_ing,string $carb_ing,string $cal_ing,string $img_ing){
        $this->id_ing=$id_ing;
        $this->name_ing=$name_ing;
        $this->prot_ing=$prot_ing;
        $this->fat_ing=$fat_ing;
        $this->carb_ing=$carb_ing;
        $this->cal_ing=$cal_ing;
        $this->img_ing=$img_ing;

    }

    //getters
    public function getIdIng(){
        return $this->id_ing;
    } 
    public function getNameIng(){
        return $this->name_ing;
    }
    public function getProtIng(){
        return $this->prot_ing;
    }
    public function getFatIng(){
        return $this->fat_ing;
    }
    public function getCarbIng(){
        return $this->carb_ing;
    }
    public function getCalIng(){
        return $this->cal_ing;
    }
    public function getImgIng(){
        return $this->img_ing;
    }

    //setters
    public function setIdIng(int $id_ing){
        $this->id_ing=$id_ing;
    }
    public function setNameIng(string $name_ing){
        $this->name_ing=$name_ing;
    }
    public function setProtIng(float $prot_ing){
        $this->prot_ing=$prot_ing;
    }
    public function setFatIng(string $fat_ing){
        $this->fat_ing=$fat_ing;
    }
    public function setCarbIng(string $carb_ing){
        $this->carb_ing=$carb_ing;
    }
    public function setCalIng(string $cal_ing){
        $this->cal_ing=$cal_ing;
    }
    public function setImgIng(string $img_ing){
        $this->img_ing=$img_ing;
    }

}
?>