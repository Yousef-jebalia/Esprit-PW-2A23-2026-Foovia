<?php

class Categorie
{
	private ?int $id_cat = null;
	private string $name_cat;

	public function __construct(string $name_cat)
	{
		$this->name_cat = $name_cat;
	}

	public function getIdCat(): ?int
	{
		return $this->id_cat;
	}

	public function getNameCat(): string
	{
		return $this->name_cat;
	}

	public function setIdCat(int $id_cat): void
	{
		$this->id_cat = $id_cat;
	}

	public function setNameCat(string $name_cat): void
	{
		$this->name_cat = $name_cat;
	}
}

