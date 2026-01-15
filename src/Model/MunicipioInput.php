<?php

namespace App\Model;

class MunicipioInput
{
    private string $municipio;
    private int $populacao;

    public function __construct(string $municipio, int $populacao)
    {
        $this->municipio = $municipio;
        $this->populacao = $populacao;
    }

    public function getMunicipio(): string
    {
        return $this->municipio;
    }

    public function getPopulacao(): int
    {
        return $this->populacao;
    }
}
