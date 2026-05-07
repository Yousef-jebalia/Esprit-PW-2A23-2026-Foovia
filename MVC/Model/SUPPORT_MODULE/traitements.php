<?php
class Traitements
{
    private int $id_traitement;
    private int $id_admin;
    private string $id_reclamation;
    private string $commentaire;
    private string $status; // urgent high priority low priority
    private string $date_traitemants;

    // Constructor
    public function __construct(int $id_traitement, int $id_admin, string $id_reclamation, string $commentaire, string $status, string $date_traitemants)
    {
        $this->id_traitement = $id_traitement;
        $this->id_admin = $id_admin;
        $this->id_reclamation = $id_reclamation;
        $this->commentaire = $commentaire;
        $this->status = $status;
        $this->date_traitemants = $date_traitemants;
    }

    // Destructor
    public function __destruct()
    {
        // Cleanup if needed
    }

    // Getters
    public function getIdTraitement(): int
    {
        return $this->id_traitement;
    }

    public function getIdAdmin(): int
    {
        return $this->id_admin;
    }

    public function getIdReclamation(): string
    {
        return $this->id_reclamation;
    }

    public function getCommentaire(): string
    {
        return $this->commentaire;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getDateTraitemants(): string
    {
        return $this->date_traitemants;
    }

    // Setters
    public function setIdTraitement(int $id_traitement): void
    {
        $this->id_traitement = $id_traitement;
    }

    public function setIdAdmin(int $id_admin): void
    {
        $this->id_admin = $id_admin;
    }

    public function setIdReclamation(string $id_reclamation): void
    {
        $this->id_reclamation = $id_reclamation;
    }

    public function setCommentaire(string $commentaire): void
    {
        $this->commentaire = $commentaire;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    public function setDateTraitemants(string $date_traitemants): void
    {
        $this->date_traitemants = $date_traitemants;
    }
}
?>