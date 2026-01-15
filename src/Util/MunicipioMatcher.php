<?php

declare(strict_types=1);

namespace App\Util;

final class MunicipioMatcher
{
    public static function match(string $input, array $mapaIbge): ?array
    {
        $key = StringNormalizer::normalize($input);

        // 1. Match exato
        if (isset($mapaIbge[$key])) {
            return $mapaIbge[$key];
        }

        // 2. Match aproximado (Levenshtein)
        $melhorMatch = null;
        $menorDistancia = PHP_INT_MAX;

        foreach ($mapaIbge as $ibgeKey => $dados) {
            $distancia = levenshtein($key, $ibgeKey);

            if ($distancia < $menorDistancia) {
                $menorDistancia = $distancia;
                $melhorMatch = $dados;
            }
        }

        // Threshold conservador (erros simples)
        if ($menorDistancia <= 2) {
            return [$melhorMatch[0]];
        }

        return null;
    }
}
