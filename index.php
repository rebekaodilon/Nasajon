<?php

declare(strict_types=1);

$enviar = in_array('--send', $argv, true);

/**
 * ======================================================
 * Carrega variáveis de ambiente (.env)
 * ======================================================
 */
$envPath = __DIR__ . '/.env';

if (file_exists($envPath)) {
    foreach (file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);

        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }

        [$key, $value] = explode('=', $line, 2);
        putenv(trim($key) . '=' . trim($value));
    }
}

/**
 * ======================================================
 * Configuração de logs
 * ======================================================
 */
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/app.log');

require __DIR__ . '/vendor/autoload.php';

use App\Csv\CsvReader;
use App\Ibge\IbgeClient;
use App\Api\CorrecaoClient;
use App\Util\StringNormalizer;
use App\Util\MunicipioMatcher;
use App\Resultado\ResultadoCsvWriter;
use App\Resultado\ResultadoMunicipio;
use App\Estatistica\EstatisticasCalculator;

try {
    /**
     * ======================================================
     * 1. Leitura do CSV de entrada
     * ======================================================
     */
    $csvReader = new CsvReader(__DIR__ . '/input.csv');
    $inputs = $csvReader->read();

    /**
     * ======================================================
     * 2. Consulta à API do IBGE
     * ======================================================
     */
    $ibgeClient = new IbgeClient();

    $ibgeDisponivel = true;
    $ibgeData = [];

    try {
        $ibgeData = $ibgeClient->fetchMunicipios();
    } catch (Throwable $e) {
        $ibgeDisponivel = false;
        error_log('[IBGE] API indisponível: ' . $e->getMessage());
        echo "Aviso: API do IBGE indisponível\n";
    }

    /**
     * ======================================================
     * 3. Indexação dos municípios do IBGE
     * ======================================================
     */
    $mapaIbge = [];

    foreach ($ibgeData as $item) {
        if (!isset($item['nome'])) {
            continue;
        }

        $key = StringNormalizer::normalize($item['nome']);

        $uf = $item['microrregiao']['mesorregiao']['UF'] ?? null;
        if ($uf === null) {
            continue;
        }

        $mapaIbge[$key][] = [
            'nome'   => $item['nome'],
            'uf'     => $uf['sigla'],
            'regiao' => $uf['regiao']['nome'] ?? null,
            'id'     => $item['id'],
        ];
    }

    /**
     * ======================================================
     * 4. Matching + geração dos resultados
     * ======================================================
     */
    $resultados = [];

    foreach ($inputs as $input) {
        if (!$ibgeDisponivel) {
            $resultados[] = new ResultadoMunicipio(
                $input->getMunicipio(),
                $input->getPopulacao(),
                'ERRO_API'
            );
            continue;
        }

        $matches = MunicipioMatcher::match(
        $input->getMunicipio(),
        $mapaIbge
    );

    if ($matches === null) {
        $resultados[] = new ResultadoMunicipio(
            $input->getMunicipio(),
            $input->getPopulacao(),
            'NAO_ENCONTRADO'
        );
        continue;
    }

    if (count($matches) > 1) {
        $resultados[] = new ResultadoMunicipio(
            $input->getMunicipio(),
            $input->getPopulacao(),
            'AMBIGUO'
        );
        continue;
    }

    $ibge = $matches[0];

        $resultados[] = new ResultadoMunicipio(
            $input->getMunicipio(),
            $input->getPopulacao(),
            'OK',
            $ibge['nome'],
            $ibge['uf'],
            $ibge['regiao'],
            $ibge['id']
        );
    }

    /**
     * ======================================================
     * DEBUG (informativo)
     * ======================================================
     */
    echo PHP_EOL . "DEBUG RESULTADOS:" . PHP_EOL;

    foreach ($resultados as $r) {
        echo $r->getMunicipioInput() . ' | '
        . $r->getStatus() . ' | '
        . ($r->getRegiao() ?? 'SEM_REGIAO') . ' | '
        . ($r->getIdIbge() ?? 0)
        . PHP_EOL;
    }

    /**
     * ======================================================
     * 5. Geração do CSV de saída
     * ======================================================
     */
    $writer = new ResultadoCsvWriter(__DIR__ . '/resultado.csv');
    $writer->write($resultados);

    echo "Arquivo resultado.csv gerado com sucesso" . PHP_EOL;

    /**
     * ======================================================
     * 6. Cálculo das estatísticas
     * ======================================================
     */
    $calculator = new EstatisticasCalculator();
    $stats = $calculator->calcular($resultados);

    echo PHP_EOL . "JSON que será enviado:" . PHP_EOL;
    echo json_encode(['stats' => $stats], JSON_PRETTY_PRINT) . PHP_EOL;

    /**
     * ======================================================
     * 7. Envio para a API de correção
     * ======================================================
     */
    if ($enviar) {
        $accessToken = getenv('ACCESS_TOKEN');

        if (!$accessToken) {
            throw new RuntimeException(
                'ACCESS_TOKEN não definido na variável de ambiente (.env)'
            );
        }

        $client = new CorrecaoClient(
            'https://mynxlubykylncinttggu.functions.supabase.co/ibge-submit'
        );

        $response = $client->enviar($stats, $accessToken);

        echo PHP_EOL . "Resultado da correção:" . PHP_EOL;
        echo "Score: {$response['score']}" . PHP_EOL;
        echo "Feedback: {$response['feedback']}" . PHP_EOL;
    } else {
        echo PHP_EOL . "Envio NÃO executado (use --send para enviar)" . PHP_EOL;
    }

} catch (Throwable $e) {
    error_log('Erro inesperado: ' . $e->getMessage());
    echo 'Erro: ' . $e->getMessage() . PHP_EOL;
    exit(1);
}
