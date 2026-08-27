<?php
/**
 * Fonctions partagées du site Musubi Dojo (production O2switch).
 */

define('DATA_DIR', __DIR__ . '/../data');
define('IMG_DIR', __DIR__ . '/../images');

/** Échappe une chaîne pour l'affichage HTML. */
function e($s) {
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

/** Charge un fichier JSON de données. Renvoie $default si absent/invalide. */
function load_json($name, $default = []) {
    $path = DATA_DIR . '/' . basename($name);
    if (!is_file($path)) return $default;
    $raw = file_get_contents($path);
    $data = json_decode($raw, true);
    return is_array($data) ? $data : $default;
}

/** Enregistre des données en JSON (écriture atomique). Renvoie true/false. */
function save_json($name, $data) {
    $path = DATA_DIR . '/' . basename($name);
    $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if ($json === false) return false;
    $tmp = $path . '.tmp';
    if (file_put_contents($tmp, $json, LOCK_EX) === false) return false;
    return rename($tmp, $path);
}

/** URL absolue de base du site (protocole + hôte + dossier), sans slash final. */
function base_url() {
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['SERVER_PORT'] ?? '') == 443)
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
    $scheme = $https ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $dir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');
    return $scheme . '://' . $host . $dir;
}

/** Balise <picture> avec source WebP optionnelle. */
function picture($src, $webp, $alt, $attrs = '') {
    $out = '<picture>';
    if ($webp) $out .= '<source srcset="' . e($webp) . '" type="image/webp">';
    $out .= '<img src="' . e($src) . '" alt="' . e($alt) . '" ' . $attrs . '></picture>';
    return $out;
}

/** Charge la configuration (inc/config.php, sinon inc/config.sample.php). */
function config($key = null, $default = null) {
    static $cfg = null;
    if ($cfg === null) {
        $f = __DIR__ . '/config.php';
        if (!is_file($f)) $f = __DIR__ . '/config.sample.php';
        $cfg = is_file($f) ? require $f : [];
        if (!is_array($cfg)) $cfg = [];
    }
    if ($key === null) return $cfg;
    return $cfg[$key] ?? $default;
}
