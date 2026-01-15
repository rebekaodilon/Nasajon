<?php

declare(strict_types=1);

namespace App\Resultado;

final class ResultadoMunicipio
{
    public function __construct(
        private string $municipioInput,
        private int $populacaoInput,
        private string $status,
        private ?string $municipioIbge = null,
        private ?string $uf = null,
        private ?string $regiao = null,
        private ?int $idIbge = null
    ) {
    }

    public function getMunicipioInput(): string
    {
        return $this->municipioInput;
    }

    public function getPopulacaoInput(): int
    {
        return $this->populacaoInput;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getMunicipioIbge(): ?string
    {
        return $this->municipioIbge;
    }

    public function getUf(): ?string
    {
        return $this->uf;
    }

    public function getRegiao(): ?string
    {
        return $this->regiao;
    }

    public function getIdIbge(): ?int
    {
        return $this->idIbge;
    }

    public function toCsvRow(): array
    {
        return [
            $this->municipioInput,
            $this->populacaoInput,
            $this->municipioIbge,
            $this->uf,
            $this->regiao,
            $this->idIbge,
            $this->status,
        ];
    }
}
