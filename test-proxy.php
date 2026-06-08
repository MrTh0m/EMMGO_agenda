<?php
/**
 * test-proxy.php — Diagnostic du proxy ICS
 * Visite cette page dans ton navigateur pour voir ce qui bloque.
 * Supprime ce fichier une fois le problème résolu.
 */
header('Content-Type: text/html; charset=utf-8');
$url = 'https://emlyon.brightspace.com/d2l/le/calendar/feed/user/feed.ics?token=agsxpv5z940h01wb17653';

function ok($msg)  { echo "<li style='color:green'>✓ $msg</li>"; }
function err($msg) { echo "<li style='color:red'>✗ $msg</li>"; }
function warn($msg){ echo "<li style='color:orange'>⚠ $msg</li>"; }
?>
<!DOCTYPE html><html lang="fr"><head><meta charset="UTF-8">
<title>Diagnostic proxy EMMGO</title>
<style>body{font-family:monospace;max-width:800px;margin:2rem auto;padding:0 1rem}
li{margin:.4rem 0}pre{background:#f4f4f4;padding:1rem;border-radius:6px;overflow-x:auto;font-size:12px}</style>
</head><body>
<h2>Diagnostic proxy — EMMGO Dashboard</h2>
<ul>
<?php

// 1. Version PHP
$phpver = PHP_VERSION;
ok("PHP $phpver");
if (version_compare($phpver, '7.4', '<')) warn("PHP < 7.4 : certaines fonctions pourraient manquer");

// 2. cURL
if (function_exists('curl_init')) {
    ok('cURL disponible — ' . curl_version()['version']);
    $curl_ok = true;
} else {
    err('cURL non disponible');
    $curl_ok = false;
}

// 3. allow_url_fopen
if (ini_get('allow_url_fopen')) ok('allow_url_fopen activé');
else warn('allow_url_fopen désactivé (fallback indisponible)');

// 4. OpenSSL
if (extension_loaded('openssl')) ok('OpenSSL disponible');
else err('OpenSSL absent — impossible de faire des requêtes HTTPS');

// 5. Résolution DNS de emlyon.brightspace.com
$ip = @gethostbyname('emlyon.brightspace.com');
if ($ip !== 'emlyon.brightspace.com') ok("Résolution DNS OK → $ip");
else err('Résolution DNS échouée pour emlyon.brightspace.com (le serveur ne peut pas joindre Internet ?)');

// 6. Test de connexion TCP port 443
$sock = @fsockopen('emlyon.brightspace.com', 443, $errno, $errstr, 5);
if ($sock) { ok('Connexion TCP:443 OK'); fclose($sock); $tcp_ok = true; }
else { err("Connexion TCP:443 échouée : $errstr ($errno) — pare-feu sortant ?"); $tcp_ok = false; }

// 7. Test cURL complet
if ($curl_ok) {
    echo "</ul><h3>Test cURL complet</h3><ul>";
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL            => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS      => 3,
        CURLOPT_TIMEOUT        => 10,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_USERAGENT      => 'EMMGO-Dashboard/1.0',
        CURLOPT_VERBOSE        => false,
        CURLOPT_HEADERFUNCTION => function($ch, $header) use (&$respHeaders) {
            $respHeaders[] = trim($header);
            return strlen($header);
        },
    ]);
    $respHeaders = [];
    $body   = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error  = curl_error($ch);
    $info   = curl_getinfo($ch);
    curl_close($ch);

    if ($error) {
        err("cURL error : $error");
        // Retry without SSL verify
        $ch2 = curl_init();
        curl_setopt_array($ch2, [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_USERAGENT      => 'EMMGO-Dashboard/1.0',
        ]);
        $body2  = curl_exec($ch2);
        $err2   = curl_error($ch2);
        $stat2  = curl_getinfo($ch2, CURLINFO_HTTP_CODE);
        curl_close($ch2);
        if (!$err2 && $stat2 === 200) {
            warn("Fonctionne avec SSL_VERIFYPEER=false → certificat CA manquant sur le serveur");
            $body = $body2;
        } else {
            err("Échec même sans vérification SSL : $err2");
        }
    } elseif ($status === 200 && $body && str_contains($body, 'BEGIN:VCALENDAR')) {
        ok("HTTP $status — ICS reçu (" . strlen($body) . " octets) — " . substr_count($body, 'BEGIN:VEVENT') . " événements");
    } elseif ($status === 200) {
        warn("HTTP 200 mais le contenu ne ressemble pas à un ICS (premiers 200 car : " . htmlspecialchars(substr($body, 0, 200)) . ")");
    } else {
        err("HTTP $status");
        if ($body) echo "<pre>" . htmlspecialchars(substr($body, 0, 500)) . "</pre>";
    }
    echo "<li>Temps total : " . round($info['total_time'] * 1000) . " ms</li>";
}

echo "</ul>";

// 8. Résumé et recommandation
echo "<h3>Résumé</h3><ul>";
if (!$tcp_ok) {
    err("Le serveur web ne peut pas joindre emlyon.brightspace.com — probable restriction réseau/pare-feu sortant. Solution : ajouter une règle sortante vers 443, ou utiliser un autre hébergeur.");
} else {
    ok("La connexion réseau fonctionne — proxy.php devrait marcher.");
}
echo "</ul>";

echo "<p style='color:#888;font-size:12px;margin-top:2rem'>Supprime ce fichier après diagnostic.</p>";
?>
</body></html>
