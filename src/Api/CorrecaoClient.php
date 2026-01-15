<?php

namespace App\Api;

class CorrecaoClient
{
    private string $url;

    public function __construct(string $url)
    {
        $this->url = $url;
    }

    public function enviar(array $stats, string $accessToken): array
    {
        $payload = json_encode(['stats' => $stats], JSON_THROW_ON_ERROR);

        $ch = curl_init($this->url);

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $accessToken,
            ],
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
        ]);

        $response = curl_exec($ch);

        if ($response === false) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new \RuntimeException('Erro ao enviar para API de correção: ' . $error);
        }

        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($statusCode !== 200) {
            throw new \RuntimeException(
                'API de correção retornou status ' . $statusCode . ': ' . $response
            );
        }

        $data = json_decode($response, true);

        if (!is_array($data)) {
            throw new \RuntimeException('Resposta inválida da API de correção');
        }

        return $data;
    }
}
