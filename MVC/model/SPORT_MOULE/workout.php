<?php

class Workout {
    private ?int $id_work = null;
    private string $name_work;
    private ?string $pic_work;
    private int $cal_work;
    private int $duree_work;
    private int $id_user;
    private int $id_cat;
    
    public function __construct(string $name_work, ?string $pic_work, int $cal_work, int $duree_work, int $id_user, int $id_cat) {
        $this->name_work = $name_work;
        $this->pic_work = $pic_work;
        $this->cal_work = $cal_work;
        $this->duree_work = $duree_work;
        $this->id_user = $id_user;
        $this->id_cat = $id_cat;
    }

    // Getters
    public function getIdWork(): ?int { return $this->id_work; }
    public function getNameWork(): string { return $this->name_work; }
    public function getPicWork(): ?string { return $this->pic_work; }
    public function getCalWork(): int { return $this->cal_work; }
    public function getDureeWork(): int { return $this->duree_work; }
    public function getIdUser(): int { return $this->id_user; }
    public function getIdCat(): int { return $this->id_cat; }

    // Setters
    public function setIdWork(int $id): void { $this->id_work = $id; }
    public function setNameWork(string $name): void { $this->name_work = $name; }
    public function setPicWork(?string $pic): void { $this->pic_work = $pic; }
    public function setCalWork(int $cal): void { $this->cal_work = $cal; }
    public function setDureeWork(int $duree): void { $this->duree_work = $duree; }
    public function setIdUser(int $id_user): void { $this->id_user = $id_user; }
    public function setIdCat(int $id_cat): void { $this->id_cat = $id_cat; }
}