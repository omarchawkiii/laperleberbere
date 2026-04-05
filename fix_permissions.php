<?php
// SCRIPT TEMPORAIRE - SUPPRIMER APRÈS UTILISATION
$root   = __DIR__;
$errors = [];
$fixed  = [];

// Corriger récursivement
$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($root, RecursiveDirectoryIterator::SKIP_DOTS),
    RecursiveIteratorIterator::SELF_FIRST
);

// Corriger le dossier racine
if (chmod($root, 0755)) $fixed[] = '. → 755';
else $errors[] = '. (racine)';

foreach ($iterator as $item) {
    $path = $item->getPathname();
    // Ignorer ce fichier et debug.php
    if (in_array(basename($path), ['fix_permissions.php', 'debug.php'])) continue;

    if ($item->isDir()) {
        if (chmod($path, 0755)) $fixed[] = str_replace($root.'/', '', $path) . ' → 755';
        else $errors[] = str_replace($root.'/', '', $path);
    } else {
        if (chmod($path, 0644)) $fixed[] = str_replace($root.'/', '', $path) . ' → 644';
        else $errors[] = str_replace($root.'/', '', $path);
    }
}

echo "<style>body{font-family:monospace;padding:20px;line-height:1.8} .ok{color:green} .fail{color:red}</style>";
echo "<h2>Résultat</h2>";
echo "<span class='ok'>✅ Corrigés : " . count($fixed) . "</span><br>";
if ($errors) echo "<span class='fail'>❌ Échecs : " . implode(', ', $errors) . "</span><br>";

echo "<h2>Fichiers corrigés</h2><pre>";
echo implode("\n", $fixed);
echo "</pre>";

echo "<hr><strong style='color:red'>⚠️  Supprimez ce fichier du serveur maintenant !</strong>";
