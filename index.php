<?php
/**
 * BINANCE BRIDGE (TÚNEL) - Servidor Railway
 * Este arquivo recebe as requisições do servidor principal e as repassa para a Binance.
 */

// CHAVE DE SEGURANÇA (Para evitar que outros usem seu túnel)
define('BRIDGE_TOKEN', 'OneServer_Secret_2026_Key'); 

$token = $_SERVER['HTTP_X_BRIDGE_TOKEN'] ?? '';
if ($token !== BRIDGE_TOKEN) {
    header('HTTP/1.1 403 Forbidden');
    die("Acesso Negado: Token Invalido.");
}

$url = $_GET['url'] ?? '';
if (!$url || strpos($url, 'https://api.binance.com') !== 0) {
    die("Erro: URL invalida ou nao permitida.");
}

$apiKey = $_SERVER['HTTP_X_MBX_APIKEY'] ?? '';

$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        "X-MBX-APIKEY: $apiKey",
        "Content-Type: application/json"
    ],
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_TIMEOUT => 30
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

header('Content-Type: application/json');
http_response_code($httpCode);
echo $response;
?>
