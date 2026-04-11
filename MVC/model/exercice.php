<?php

require_once __DIR__ . '/config.php';

class Exercise
{
    // Properties
    private ?int    $id_ex;
    private string $name_ex;
    private string $type_ex;
    private string $muscle_ex;
    private int    $cal_ex;
    private int    $fatigue_ex;
    private float  $PR_ex;
    private string $description_ex;
    private ?string $gif_ex;

    private PDO $db;

    public function __construct($name, $type, $muscle, $cal, $fatigue, $description, $gif=NULL, $id=NULL, $PR=0.0)
    {
        $this->db = config::getConnexion();
        $this->name_ex = $name;
        $this->type_ex = $type;
        $this->muscle_ex = $muscle;
        $this->cal_ex = $cal;
        $this->fatigue_ex = $fatigue;
        $this->PR_ex = $PR;
        $this->description_ex = $description;
        $this->gif_ex = $gif;
    }

    // -------------------------------------------------------------------------
    // Getters
    // -------------------------------------------------------------------------

    public function getIdEx(): ?int            { return $this->id_ex; }
    public function getNameEx(): string        { return $this->name_ex; }
    public function getTypeEx(): string        { return $this->type_ex; }
    public function getMuscleEx(): string      { return $this->muscle_ex; }
    public function getCalEx(): int            { return $this->cal_ex; }
    public function getFatigueEx(): int        { return $this->fatigue_ex; }
    public function getPREx(): float           { return $this->PR_ex; }
    public function getDescriptionEx(): string { return $this->description_ex; }
    public function getGifEx(): ?string        { return $this->gif_ex; }

    // -------------------------------------------------------------------------
    // Setters
    // -------------------------------------------------------------------------

    public function setNameEx(string $name_ex): void      { $this->name_ex = $name_ex; }
    public function setTypeEx(string $type_ex): void      { $this->type_ex = $type_ex; }
    public function setMuscleEx(string $muscle_ex): void  { $this->muscle_ex = $muscle_ex; }
    public function setCalEx(int $cal_ex): void           { $this->cal_ex = $cal_ex; }
    public function setFatigueEx(int $fatigue_ex): void   { $this->fatigue_ex = $fatigue_ex; }
    public function setPREx(float $PR_ex): void           { $this->PR_ex = $PR_ex; }
    public function setDescriptionEx(string $desc): void  { $this->description_ex = $desc; }
    public function setGifEx(?string $gif_ex): void       { $this->gif_ex = $gif_ex; }

}