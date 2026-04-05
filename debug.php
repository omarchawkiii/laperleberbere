<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
echo "<style>body{font-family:monospace;padding:20px;line-height:1.9} .ok{color:green} .fail{color:red} .warn{color:orange} h2{border-bottom:2px solid #ccc;padding-bottom:4px;margin-top:24px} code{background:#f4f4f4;padding:1px 5px}</style>";

// ── 1. Permissions fichiers ──────────────────────────────────────
echo "<h2>1. Permissions fichiers (doivent être 644 / 755)</h2>";
$items = [
    '.'                         => '755',
    'index.php'                 => '644',
    'config'                    => '755',
    'config/db.php'             => '644',
    'config/mail.php'           => '644',
    'client'                    => '755',
    'client/index.php'          => '644',
    'client/api_booking.php'    => '644',
    'admin'                     => '755',
    'admin/index.php'           => '644',
    'admin/manage_slots.php'    => '644',
    'admin/login.php'           => '644',
    'admin/logout.php'          => '644',
    'assets'                    => '755',
    'assets/css/style.css'      => '644',
];
foreach ($items as $path => $expected) {
    $full = __DIR__ . '/' . $path;
    if (!file_exists($full)) { echo "<span class='warn'>⚠️  $path introuvable</span><br>"; continue; }
    $perms   = substr(sprintf('%o', fileperms($full)), -3);
    $is_dir  = is_dir($full);
    $ok      = $is_dir ? in_array($perms, ['755','750','711']) : in_array($perms, ['644','640','604']);
    echo ($ok ? "<span class='ok'>✅" : "<span class='fail'>❌") . " $path — <code>$perms</code>" . (!$ok ? " ← doit être $expected" : "") . "</span><br>";
}

// ── 2. Test chargement réel de chaque page ───────────────────────
echo "<h2>2. Chargement réel des pages</h2>";

// Teste client/index.php (sans session nécessaire)
echo "<strong>client/index.php :</strong><br>";
ob_start();
try {
    // On simule l'include depuis le bon répertoire
    chdir(__DIR__ . '/client');
    register_shutdown_function(function(){});
    $code = php_strip_whitespace(__DIR__ . '/client/index.php');
    echo strlen($code) > 100
        ? "<span class='ok'>  ✅ Parsé OK (" . strlen($code) . " octets après strip)</span>"
        : "<span class='fail'>  ❌ Fichier trop petit / vide</span>";
} catch (Throwable $e) {
    echo "<span class='fail'>  ❌ " . htmlspecialchars($e->getMessage()) . "</span>";
}
ob_end_clean();
chdir(__DIR__);
echo "<br>";

// ── 3. .htaccess ────────────────────────────────────────────────
echo "<h2>3. Fichier .htaccess</h2>";
if (file_exists(__DIR__ . '/.htaccess')) {
    echo "<span class='ok'>✅ .htaccess présent</span><br>";
    echo "<pre style='background:#f4f4f4;padding:10px;font-size:12px'>" . htmlspecialchars(file_get_contents(__DIR__ . '/.htaccess')) . "</pre>";
} else {
    echo "<span class='warn'>⚠️  Aucun .htaccess — peut causer des problèmes sur certains serveurs</span><br>";
}

// ── 4. Test URL des pages (cURL) ─────────────────────────────────
echo "<h2>4. Test HTTP des pages réelles</h2>";
$base = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']);
$pages = [
    '/index.php'        => 'Page accueil',
    '/client/index.php' => 'Page client',
    '/admin/login.php'  => 'Page login admin',
];
if (function_exists('curl_init')) {
    foreach ($pages as $uri => $label) {
        $url = rtrim($base, '/') . $uri;
        $ch  = curl_init($url);
        curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 5, CURLOPT_FOLLOWLOCATION => false, CURLOPT_NOBODY => true]);
        curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        $ok = in_array($code, [200, 302]);
        echo ($ok ? "<span class='ok'>✅" : "<span class='fail'>❌") . " $label — HTTP <strong>$code</strong></span> <code>$url</code><br>";
    }
} else {
    echo "<span class='warn'>⚠️  cURL non disponible — test HTTP impossible</span><br>";
    echo "→ Testez manuellement ces URLs :<br>";
    foreach ($pages as $uri => $label) {
        echo "  <a href='" . rtrim($base,'/') . $uri . "' target='_blank'>" . rtrim($base,'/') . $uri . "</a> ($label)<br>";
    }
}

// ── 5. Log Apache (cPanel) ───────────────────────────────────────
echo "<h2>5. Log Apache/cPanel</h2>";
$apacheLogs = [
    '/home/' . get_current_user() . '/logs/' . $_SERVER['HTTP_HOST'] . '-ssl_log',
    '/home/' . get_current_user() . '/logs/' . $_SERVER['HTTP_HOST'] . '-error_log',
    '/home/' . get_current_user() . '/logs/error_log',
];
$found = false;
foreach ($apacheLogs as $log) {
    if (file_exists($log) && is_readable($log)) {
        $lines = array_slice(file($log), -20);
        echo "Fichier : <code>$log</code><br>";
        echo "<pre style='background:#f4f4f4;padding:10px;font-size:11px;overflow:auto;max-height:200px;border:1px solid #ddd'>" . htmlspecialchars(implode('', $lines)) . "</pre>";
        $found = true; break;
    }
}
if (!$found) echo "<span class='warn'>Log Apache non accessible ici — consultez <strong>cPanel → Logs → Errors</strong></span><br>";
