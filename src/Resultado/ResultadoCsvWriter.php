<?php

namespace App\Resultado;

class ResultadoCsvWriter
{
    private string $filePath;

    public function __construct(string $filePath)
    {
        $this->filePath = $filePath;
    }

    /**
     * @param ResultadoMunicipio[] $resultados
     */
    public function write(array $resultados): void
    {
        $handle = fopen($this->filePath, 'w');

        if ($handle === false) {
            throw new \RuntimeException('Não foi possível criar resultado.csv');
        }

        // Header obrigatório
        fputcsv($handle, [
            'municipio_input',
            'populacao_ibge',
            'municipio_ibge',
            'uf',
            'regiao',
            'id_ibge',
            'status',
        ]);

        foreach ($resultados as $resultado) {
            fputcsv($handle, $resultado->toCsvRow());
        }

        fclose($handle);
    }
}
