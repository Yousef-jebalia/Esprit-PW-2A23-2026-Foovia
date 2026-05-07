<?php
class Reclamations
{
    private string $id_reclamation;
    private int $id_user;
    private string $description;
    private string $etat;
    private string $type;
    private string $date_overture;
    private string $date_fermiture;

    // Constructor
    public function __construct(string $id_reclamation, int $id_user, string $description, string $etat, string $type, string $date_overture, string $date_fermiture)
    {
        $this->id_reclamation = $id_reclamation;
        $this->id_user = $id_user;
        $this->description = $description;
        $this->etat = $etat;
        $this->type = $type;
        $this->date_overture = $date_overture;
        $this->date_fermiture = $date_fermiture;
    }

    // Destructor
    public function __destruct()
    {
        // Cleanup if needed
    }

    // Getters
    public function getIdReclamation(): string
    {
        return $this->id_reclamation;
    }

    public function getIdUser(): int
    {
        return $this->id_user;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getEtat(): string
    {
        return $this->etat;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getDateOverture(): string
    {
        return $this->date_overture;
    }

    public function getDateFermiture(): string
    {
        return $this->date_fermiture;
    }

    // Setters
    public function setIdReclamation(string $id_reclamation): void
    {
        $this->id_reclamation = $id_reclamation;
    }

    public function setIdUser(int $id_user): void
    {
        $this->id_user = $id_user;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function setEtat(string $etat): void
    {
        $this->etat = $etat;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function setDateOverture(string $date_overture): void
    {
        $this->date_overture = $date_overture;
    }

    public function setDateFermiture(string $date_fermiture): void
    {
        $this->date_fermiture = $date_fermiture;
    }
}
?>