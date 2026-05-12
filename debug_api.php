<?php
// Configurações para o teste (Coloquei as chaves que você passou)
$apiKey = 'ip6nPKnTduhzOCfWQwvE9GrJ6dZefoYmHc0T8vQQwVR76Y9B270OYw0TKg557oFB';
$apiSecret = 'MdH21FCvAwabxHjZvmM3L89HC88ddZO8nivf0yxaOnnZ1UcGtjGcsSxToFKo3DF7';

header('Content-Type: text/html; charset=utf-8');
echo "<h2>Teste de Conexão Binance API</h2>";

function testBinance($url, $apiKey, $apiSecret, $params = []) {
    // 1. Pegar tempo
    $chTime = curl_init('https://api.binance.com/api/v3/time');
    curl_setopt($chTime, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($chTime, CURLOPT_SSL_VERIFYPEER, false);
    $timeRes = curl_exec($chTime);
    $serverTime = json_decode($timeRes, true)['serverTime'] ?? null;
    curl_close($chTime);

    if (!$serverTime) {
        return "ERRO: Não conseguiu pegar o tempo da Binance. Servidor pode estar bloqueado.";
    }

    // 2. Assinar
    $params['timestamp'] = $serverTime;
    ksort($params);
    $query = http_build_query($params);
    $signature = hash_hmac('sha256', $query, $apiSecret);
    $fullUrl = $url . "?" . $query . "&signature=" . $signature;

    // 3. Chamar
    $ch = curl_init($fullUrl);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => ["X-MBX-APIKEY: $apiKey"],
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_TIMEOUT => 15
    ]);

    $response = curl_exec($ch);
    $err = curl_error($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return [
        'http_code' => $httpCode,
        'response' => $response,
        'curl_error' => $err
    ];
}

// Teste 1: Histórico de Pay
echo "<h3>1. Testando Histórico de Pay (C2C):</h3>";
$resPay = testBinance('https://api.binance.com/sapi/v1/pay/transactions', $apiKey, $apiSecret);
echo "<pre>"; print_r($resPay); echo "</pre>";

// Teste 2: Histórico de Depósitos (Carteira)
echo "<h3>2. Testando Histórico de Depósitos (Wallet):</h3>";
$resWallet = testBinance('https://api.binance.com/sapi/v1/capital/deposit/hisrec', $apiKey, $apiSecret);
echo "<pre>"; print_r($resWallet); echo "</pre>";

// Teste 3: Status da Conta (Para ver se as chaves valem)
echo "<h3>3. Testando Permissões da Conta:</h3>";
$resAccount = testBinance('https://api.binance.com/api/v3/account', $apiKey, $apiSecret);
echo "<pre>"; print_r($resAccount); echo "</pre>";
?>
