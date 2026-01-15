<?php

namespace App\Ibge;

class IbgeClient
{
    private const URL = 'https://servicodados.ibge.gov.br/api/v1/localidades/municipios';

    public function fetchMunicipios(): array
    {
        $ch = curl_init(self::URL);

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
        ]);

        $response = curl_exec($ch);

        if ($response === false) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new \RuntimeException('Erro ao acessar API do IBGE: ' . $error);
        }

        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($statusCode !== 200) {
            throw new \RuntimeException('API do IBGE retornou status ' . $statusCode);
        }

        $data = json_decode($response, true);

        if (!is_array($data)) {
            throw new \RuntimeException('Resposta inválida da API do IBGE');
        }

        return $data;
    }
}
