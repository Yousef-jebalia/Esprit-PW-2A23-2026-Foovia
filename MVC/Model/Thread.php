<?php
class Thread
{
    private int    $id_thread;
    private ?int   $id_reclam;
    private string $title;
    private string $description;
    private string $published_at;
    private int    $published_by;

    public function __construct(
        int    $id_thread,
        ?int   $id_reclam,
        string $title,
        string $description,
        string $published_at,
        int    $published_by
    ) {
        $this->id_thread    = $id_thread;
        $this->id_reclam    = $id_reclam;
        $this->title        = $title;
        $this->description  = $description;
        $this->published_at = $published_at;
        $this->published_by = $published_by;
    }

    public function getIdThread(): int    { return $this->id_thread; }
    public function getIdReclam(): ?int   { return $this->id_reclam; }
    public function getTitle(): string    { return $this->title; }
    public function getDescription(): string { return $this->description; }
    public function getPublishedAt(): string { return $this->published_at; }
    public function getPublishedBy(): int    { return $this->published_by; }

    public function setTitle(string $title): void             { $this->title = $title; }
    public function setDescription(string $desc): void        { $this->description = $desc; }
    public function setPublishedAt(string $published_at): void { $this->published_at = $published_at; }
    public function setPublishedBy(int $published_by): void    { $this->published_by = $published_by; }
}
?>
