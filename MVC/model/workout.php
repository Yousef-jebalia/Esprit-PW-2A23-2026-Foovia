<?php

class Workout {
    private ?int $id_work = null;
    private int $name_work;
    private string $pic_work;
    private int $nb_work;
    private int $cal_work;
    private int $duree_work;
    private int $id_user;

    public function __construct(int $name_work, string $pic_work, int $nb_work, int $cal_work, int $duree_work, int $id_user) {
        $this->name_work = $name_work;
        $this->pic_work = $pic_work;
        $this->nb_work = $nb_work;
        $this->cal_work = $cal_work;
        $this->duree_work = $duree_work;
        $this->id_user = $id_user;
    }

    // Getters
    public function getIdWork(): ?int { return $this->id_work; }
    public function getNameWork(): int { return $this->name_work; }
    public function getPicWork(): string { return $this->pic_work; }
    public function getNbWork(): int { return $this->nb_work; }
    public function getCalWork(): int { return $this->cal_work; }
    public function getDureeWork(): int { return $this->duree_work; }
    public function getIdUser(): int { return $this->id_user; }

    // Setters
    public function setIdWork(int $id): void { $this->id_work = $id; }
    public function setNameWork(int $name): void { $this->name_work = $name; }
    public function setPicWork(string $pic): void { $this->pic_work = $pic; }
    public function setNbWork(int $nb): void { $this->nb_work = $nb; }
    public function setCalWork(int $cal): void { $this->cal_work = $cal; }
    public function setDureeWork(int $duree): void { $this->duree_work = $duree; }
    public function setIdUser(int $id_user): void { $this->id_user = $id_user; }
}