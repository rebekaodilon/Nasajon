<?php

namespace App\Csv;

use App\Model\MunicipioInput;

class CsvReader
{
    private string $filePath;

    public function __construct(string $filePath)
    {
        if (!file_exists($filePath)) {
            throw new \RuntimeException("Arquivo CSV não encontrado: {$filePath}");
        }

        $this->filePath = $filePath;
    }

    /**
     * @return MunicipioInput[]
     */
    public function read(): array
    {
        $handle = fopen($this->filePath, 'r');

        if ($handle === false) {
            throw new \RuntimeException('Não foi possível abrir o arquivo CSV');
        }

        $municipios = [];
        $linha = 0;

        while (($row = fgetcsv($handle, 0, ',')) !== false) {
            // ignora header
            if ($linha === 0) {
                $linha++;
                continue;
            }

            $municipios[] = new MunicipioInput(
                trim($row[0]),
                (int) trim($row[1])
            );

            $linha++;
        }

        fclose($handle);

        return $municipios;
    }
}