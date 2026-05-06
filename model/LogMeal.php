<?php
class LogMeal
{
    private int $id_rec;
    private int $id_suiv;
    private string meal_time;
    private string meal_type;
    private string meal_image;
    


    // Constructor
    public function __construct(int $id_rec, int $id_suiv, string $meal_time, string $meal_type, string $meal_image)
    {
        $this->id_rec = $id_rec;
        $this->id_suiv = $id_suiv;
        $this->meal_time = $meal_time;
        $this->meal_type = $meal_type;
        $this->meal_image = $meal_image;
    }

    // Destructor
    public function __destruct()
    {
        // Cleanup if needed
    }

    // Getters
    public function getIdRec(): int
    {
        return $this->id_rec;
    }
    public function getIdSuiv(): int
    {
        return $this->id_suiv;
    }
    public function getMealTime(): string
    {
        return $this->meal_time;
    }
    public function getMealType(): string
    {
        return $this->meal_type;
    }
    public function getMealImage(): string
    {
        return $this->meal_image;
    }
    // Setters
    public function setIdRec(int $id_rec): void
    {
        $this->id_rec = $id_rec;
    }
    public function setIdSuiv(int $id_suiv): void
    {
        $this->id_suiv = $id_suiv;
    }
    public function setMealTime(string $meal_time): void
    {
        $this->meal_time = $meal_time;
    }
    public function setMealType(string $meal_type): void
    {
        $this->meal_type = $meal_type;
    }   
    public function setMealImage(string $meal_image): void
    {
        $this->meal_image = $meal_image;
    }
}
?>