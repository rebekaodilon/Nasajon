<?php

namespace App\Estatistica;

use App\Resultado\ResultadoMunicipio;

class EstatisticasCalculator
{
    public function calcular(array $resultados): array
    {
        $totalMunicipios = count($resultados);
        $totalOk = 0;
        $totalNaoEncontrado = 0;
        $totalErroApi = 0;

        $popTotalOk = 0;

        $somaPorRegiao = [];
        $countPorRegiao = [];

        foreach ($resultados as $resultado) {

            switch ($resultado->getStatus()) {
                case 'OK':
                    $totalOk++;

                    $pop = $resultado->getPopulacaoInput();
                    $popTotalOk += $pop;

                    $regiao = $resultado->getRegiao();

                    if (!isset($somaPorRegiao[$regiao])) {
                        $somaPorRegiao[$regiao] = 0;
                        $countPorRegiao[$regiao] = 0;
                    }

                    $somaPorRegiao[$regiao] += $pop;
                    $countPorRegiao[$regiao]++;
                    break;

                case 'NAO_ENCONTRADO':
                    $totalNaoEncontrado++;
                    break;

                case 'ERRO_API':
                    $totalErroApi++;
                    break;
            }
        }

        $mediasPorRegiao = [];

        foreach ($somaPorRegiao as $regiao => $soma) {
            $mediasPorRegiao[$regiao] = $soma / $countPorRegiao[$regiao];
        }

        return [
            'total_municipios'     => $totalMunicipios,
            'total_ok'             => $totalOk,
            'total_nao_encontrado' => $totalNaoEncontrado,
            'total_erro_api'       => $totalErroApi,
            'pop_total_ok'         => $popTotalOk,
            'medias_por_regiao'    => $mediasPorRegiao,
        ];
    }
}

