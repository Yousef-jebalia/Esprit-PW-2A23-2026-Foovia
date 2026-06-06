<?php
class ReclamationMessage
{
    private int    $id_message;
    private int    $id_reclam;
    private int    $id_user;
    private string $body;
    private string $sent_at;

    public function __construct(
        int    $id_message,
        int    $id_reclam,
        int    $id_user,
        string $body,
        string $sent_at
    ) {
        $this->id_message = $id_message;
        $this->id_reclam  = $id_reclam;
        $this->id_user    = $id_user;
        $this->body       = $body;
        $this->sent_at    = $sent_at;
    }

    public function getIdMessage(): int  { return $this->id_message; }
    public function getIdReclam(): int   { return $this->id_reclam; }
    public function getIdUser(): int     { return $this->id_user; }
    public function getBody(): string    { return $this->body; }
    public function getSentAt(): string  { return $this->sent_at; }
}
