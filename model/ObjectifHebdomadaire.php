<?php
class ObjectifHebdomadaire
{
    private int $id_suiv;
    private int $id_obj;
    private string $date_suiv;
    private float $val_cal_suiv;
    private float $poids_suiv;
    private float $val_fat_suiv;
    private float $val_prot_suiv;
    private float $val_carb_suiv;
    private string $note_suiv;
    private string $status_obj_quot_suiv; 
    private int $nb_verre_eau_suiv;
    private string $nb_h_sommeil_suiv;
    private int $nb_pas_suiv;
    private int $id_user;
    


    // Constructor
    public function __construct(int $id_suiv, int $id_obj, string $date_suiv, float $val_cal_suiv, float $poids_suiv, float $val_fat_suiv, float $val_prot_suiv, float $val_carb_suiv, string $note_suiv, string $status_obj_quot_suiv, int $nb_verre_eau_suiv, string $nb_h_sommeil_suiv, int $nb_pas_suiv, int $id_user)
    {
        $this->id_suiv = $id_suiv;
        $this->id_obj = $id_obj;
        $this->date_suiv = $date_suiv;
        $this->val_cal_suiv = $val_cal_suiv;
        $this->poids_suiv = $poids_suiv;
        $this->val_fat_suiv = $val_fat_suiv;
        $this->val_prot_suiv = $val_prot_suiv;
        $this->val_carb_suiv = $val_carb_suiv;
        $this->note_suiv = $note_suiv;
        $this->status_obj_quot_suiv = $status_obj_quot_suiv;
        $this->nb_verre_eau_suiv = $nb_verre_eau_suiv;
        $this->nb_h_sommeil_suiv = $nb_h_sommeil_suiv;
        $this->nb_pas_suiv = $nb_pas_suiv;
        $this->id_user = $id_user;


    }

    // Destructor
    public function __destruct()
    {
        // Cleanup if needed
    }

    // Getters
    public function getIdSuiv(): int
    {
        return $this->id_suiv;
    }

    public function getIdObj(): int
    {
        return $this->id_obj;
    }

    public function getDateSuiv(): string
    {
        return $this->date_suiv;
    }

    public function getValCalSuiv(): float
    {
        return $this->val_cal_suiv;
    }

    public function getPoidsSuiv(): float
    {
        return $this->poids_suiv;
    }

    public function getValFatSuiv(): float
    {
        return $this->val_fat_suiv;
    }

    public function getValProtSuiv(): float
    {
        return $this->val_prot_suiv;
    }

    public function getValCarbSuiv(): float
    {
        return $this->val_carb_suiv;
    }

    public function getNoteSuiv(): string
    {
        return $this->note_suiv;
    }

    public function getStatusObjQuotSuiv(): string
    {
        return $this->status_obj_quot_suiv;
    }

    public function getNbVerreEauSuiv(): int
    {
        return $this->nb_verre_eau_suiv;
    }   

    public function getNbHSommeilSuiv(): string
    {
        return $this->nb_h_sommeil_suiv;
    }

    public function getNbPasSuiv(): int
    {
        return $this->nb_pas_suiv;
    }

    public function getIdUser(): int
    {
        return $this->id_user;
    }


    
    // Setters
    public function setIdSuiv(int $id_suiv): void
    {
        $this->id_suiv = $id_suiv;
    }

    public function setIdObj(int $id_obj): void
    {
        $this->id_obj = $id_obj;
    }

    public function setDateSuiv(string $date_suiv): void
    {
        $this->date_suiv = $date_suiv;
    }

    public function setValCalSuiv(float $val_cal_suiv): void
    {
        $this->val_cal_suiv = $val_cal_suiv;
    }

    public function setPoidsSuiv(float $poids_suiv): void
    {
        $this->poids_suiv = $poids_suiv;
    }

    public function setValFatSuiv(float $val_fat_suiv): void
    {
        $this->val_fat_suiv = $val_fat_suiv;
    }

    public function setValProtSuiv(float $val_prot_suiv): void
    {
        $this->val_prot_suiv = $val_prot_suiv;
    }

    public function setValCarbSuiv(float $val_carb_suiv): void
    {
        $this->val_carb_suiv = $val_carb_suiv;
    }

    public function setNoteSuiv(string $note_suiv): void
    {
        $this->note_suiv = $note_suiv;
    }

    public function setStatusObjQuotSuiv(string $status_obj_quot_suiv): void
    {
        $this->status_obj_quot_suiv = $status_obj_quot_suiv;
    }

    public function setNbVerreEauSuiv(int $nb_verre_eau_suiv): void
    {
        $this->nb_verre_eau_suiv = $nb_verre_eau_suiv;
    }

    public function setNbHSommeilSuiv(string $nb_h_sommeil_suiv): void
    {
        $this->nb_h_sommeil_suiv = $nb_h_sommeil_suiv;
    }

    public function setNbPasSuiv(int $nb_pas_suiv): void
    {
        $this->nb_pas_suiv = $nb_pas_suiv;
    }

    public function setIdUser(int $id_user): void
    {
        $this->id_user = $id_user;
    }
    
}
?>