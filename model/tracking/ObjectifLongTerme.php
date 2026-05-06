<?php
class ObjectifLongTerme
{
    private int $id_obj;
    private int $id_user;
    private string $type_obj;
    private float $val_cible_obj;
    private float $val_init_obj;
    private string $date_deb_obj;
    private string $date_fin_obj;
    private string $status_obj;
    private int $frequency_rappel_obj;
    private int $consistancy_sport_obj;
    private int $consistency_alim_obj;
    private float $obj_cal_obj;
    private float $obj_fat_obj;
    private float $obj_prot_obj;
    private float $obj_carb_obj;
    

    // Constructor
    public function __construct(int $id_obj, int $id_user,string $type_obj, float $val_cible_obj, float $val_init_obj, string $date_deb_obj, string $date_fin_obj, string $status_obj, int $frequency_rappel_obj, int $consistancy_sport_obj, int $consistency_alim_obj, float $obj_cal_obj, float $obj_fat_obj, float $obj_prot_obj, float $obj_carb_obj)
    {
        $this->id_obj = $id_obj;
        $this->id_user = $id_user;
        $this->type_obj = $type_obj;
        $this->val_cible_obj = $val_cible_obj;
        $this->val_init_obj = $val_init_obj;
        $this->date_deb_obj = $date_deb_obj;
        $this->date_fin_obj = $date_fin_obj;
        $this->status_obj = $status_obj;
        $this->frequency_rappel_obj = $frequency_rappel_obj;
        $this->consistancy_sport_obj = $consistancy_sport_obj;
        $this->consistency_alim_obj = $consistency_alim_obj;
        $this->obj_cal_obj = $obj_cal_obj;
        $this->obj_fat_obj = $obj_fat_obj;
        $this->obj_prot_obj = $obj_prot_obj;
        $this->obj_carb_obj = $obj_carb_obj;

        
        
    }

    // Destructor
    public function __destruct()
    {
        // Cleanup if needed
    }

    // Getters
    public function getIdObj(): int{return $this->id_obj;}

    public function getIdUser(): int{return $this->id_user;}

    public function getTypeObj(): string{return $this->type_obj;}

    public function getValCibleObj(): float{return $this->val_cible_obj;}

    public function getValInitObj(): float{return $this->val_init_obj;}

    public function getDateDebObj(): string{return $this->date_deb_obj;}

    public function getDateFinObj(): string{return $this->date_fin_obj;}

    public function getStatusObj(): string{return $this->status_obj;}

    public function getFrequencyRappelObj(): int{return $this->frequency_rappel_obj;}

    public function getConsistancySportObj(): int{return $this->consistancy_sport_obj;}

    public function getConsistencyAlimObj(): int{return $this->consistency_alim_obj;}

    public function getObjCalObj(): float{return $this->obj_cal_obj;}

    public function getObjFatObj(): float{return $this->obj_fat_obj;}

    public function getObjProtObj(): float{return $this->obj_prot_obj;}

    public function getObjCarbObj(): float{return $this->obj_carb_obj;}

    // Setters
    public function setIdObj(string $id_obj): void{$this->id_obj = $id_obj;}

    public function setIdUser(int $id_user): void{$this->id_user = $id_user;}

    public function setTypeObj(string $type_obj): void{$this->type_obj = $type_obj;}

    public function setValCibleObj(float $val_cible_obj): void{$this->val_cible_obj = $val_cible_obj;}

    public function setValInitObj(float $val_init_obj): void{$this->val_init_obj = $val_init_obj;}

    public function setDateDebObj(string $date_deb_obj): void{$this->date_deb_obj = $date_deb_obj;}

    public function setDateFinObj(string $date_fin_obj): void{$this->date_fin_obj = $date_fin_obj;}

    public function setStatusObj(string $status_obj): void{$this->status_obj = $status_obj;}

    public function setFrequencyRappelObj(int $frequency_rappel_obj): void{$this->frequency_rappel_obj = $frequency_rappel_obj;}

    public function setConsistancySportObj(int $consistancy_sport_obj): void{$this->consistancy_sport_obj = $consistancy_sport_obj;}

    public function setConsistencyAlimObj(int $consistency_alim_obj): void{$this->consistency_alim_obj = $consistency_alim_obj;}

    public function setObjCalObj(float $obj_cal_obj): void{$this->obj_cal_obj = $obj_cal_obj;}

    public function setObjFatObj(float $obj_fat_obj): void{$this->obj_fat_obj = $obj_fat_obj;}

    public function setObjProtObj(float $obj_prot_obj): void{$this->obj_prot_obj = $obj_prot_obj;}

    public function setObjCarbObj(float $obj_carb_obj): void{$this->obj_carb_obj = $obj_carb_obj;}

}
?>
