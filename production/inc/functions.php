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

/** Gabarit d'une page légale simple (mentions, confidentialité). */
function legal_page($title, $html) {
    $an = config('analytics');
    echo '<!DOCTYPE html><html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">';
    echo '<title>' . e($title) . ' · ' . e(config('site_name', 'Musubi Dojo')) . '</title>';
    echo '<link rel="icon" href="images/favicon.ico" sizes="any">';
    echo '<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>';
    echo '<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@600;700&family=Inter:wght@300;400;600&display=swap" rel="stylesheet">';
    echo '<style>*{box-sizing:border-box}body{margin:0;font-family:Inter,system-ui,sans-serif;color:#17130d;background:#f7f1e6;line-height:1.7}'
       . '.wrap{max-width:820px;margin:0 auto;padding:40px 24px 80px}a{color:#c33025}'
       . '.top{display:flex;align-items:center;gap:12px;margin-bottom:26px}.top img{width:46px;height:46px;border-radius:50%}'
       . '.top b{font-family:"Cormorant Garamond",serif;font-size:1.35rem}'
       . 'h1{font-family:"Cormorant Garamond",serif;font-size:2.4rem;margin:0 0 18px}'
       . 'h2{font-family:"Cormorant Garamond",serif;font-size:1.5rem;margin:28px 0 8px}'
       . 'p{color:#4b4335}ul{color:#4b4335}'
       . '.back{display:inline-block;margin-top:34px;padding:12px 24px;border-radius:999px;background:#17130d;color:#f7f1e6;text-decoration:none;font-weight:600}'
       . '.back:hover{background:#c33025}</style>';
    if ($an) echo "\n" . $an . "\n";
    echo '</head><body><div class="wrap">';
    echo '<div class="top"><img src="images/logo.png" alt="Musubi Dojo"><b>Musubi Dojo Jurbise</b></div>';
    echo '<h1>' . e($title) . '</h1>';
    echo $html;
    echo '<a class="back" href="index.php">Retour au site</a>';
    echo '</div></body></html>';
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

/** Réglages éditables du site (bannière, Facebook...). */
function settings($key = null, $default = null) {
    static $s = null;
    if ($s === null) $s = load_json('settings.json', []);
    if ($key === null) return $s;
    return $s[$key] ?? $default;
}

/** Échappe puis convertit le **gras** en <strong>. Texte enrichi sûr. */
function rich($text) {
    $t = e($text);
    $t = preg_replace('/\*\*(.+?)\*\*/su', '<strong>$1</strong>', $t);
    return nl2br($t, false);
}

/* ------------------------------------------------- Anti-spam formulaires */
function form_token() {
    $ts  = time();
    $sig = hash_hmac('sha256', (string) $ts, (string) config('app_secret', 'x'));
    return $ts . '.' . $sig;
}
function verify_form_token($v, $min = 2, $max = 7200) {
    if (!is_string($v) || strpos($v, '.') === false) return false;
    [$ts, $sig] = explode('.', $v, 2);
    if (!ctype_digit($ts)) return false;
    $calc = hash_hmac('sha256', $ts, (string) config('app_secret', 'x'));
    if (!hash_equals($calc, $sig)) return false;
    $age = time() - (int) $ts;
    return $age >= $min && $age <= $max;
}

/* ------------------------------------------------- Cloudflare Turnstile */
function turnstile_enabled() {
    return config('turnstile_site') && config('turnstile_secret');
}
function turnstile_widget() {
    if (!turnstile_enabled()) return '';
    return '<div class="cf-turnstile" data-sitekey="' . e(config('turnstile_site')) . '" style="margin:4px 0"></div>';
}
function turnstile_verify($token) {
    if (!turnstile_enabled()) return true;
    if (!$token) return false;
    $data = ['secret' => config('turnstile_secret'), 'response' => $token];
    $url  = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';
    $resp = null;
    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [CURLOPT_POST => true, CURLOPT_POSTFIELDS => http_build_query($data), CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 6]);
        $resp = curl_exec($ch);
        curl_close($ch);
    } elseif (ini_get('allow_url_fopen')) {
        $ctx = stream_context_create(['http' => ['method' => 'POST', 'header' => 'Content-Type: application/x-www-form-urlencoded', 'content' => http_build_query($data), 'timeout' => 6]]);
        $resp = @file_get_contents($url, false, $ctx);
    }
    if (!$resp) return false;
    $j = json_decode($resp, true);
    return !empty($j['success']);
}

/* ------------------------------------------------- Dates en français */
function fmt_date_fr($ts) {
    $months = [1 => 'janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre'];
    return (int) date('j', $ts) . ' ' . $months[(int) date('n', $ts)] . ' ' . date('Y', $ts);
}

/* ------------------------------------------------- Agenda AFA (flux RSS, avec cache) */
function afa_events($limit = 8) {
    $url       = 'https://afamanager.aikido.be/fr/evenement/rss';
    $cacheDir  = DATA_DIR . '/cache';
    $cacheFile = $cacheDir . '/afa-rss.xml';
    $ttl       = 6 * 3600; // 6 heures
    $xml       = null;

    // 1) Cache encore frais ?
    if (is_file($cacheFile) && (time() - filemtime($cacheFile)) < $ttl) {
        $xml = @file_get_contents($cacheFile);
    }
    // 2) Sinon, on tente de rafraîchir le flux
    if (!$xml) {
        $fresh = afa_fetch($url);
        if ($fresh && strpos($fresh, '<item') !== false) {
            if (!is_dir($cacheDir)) @mkdir($cacheDir, 0775, true);
            @file_put_contents($cacheFile, $fresh, LOCK_EX);
            $xml = $fresh;
        } elseif (is_file($cacheFile)) {
            $xml = @file_get_contents($cacheFile); // repli sur cache périmé
        }
    }
    if (!$xml) return [];

    $prev = libxml_use_internal_errors(true);
    $rss  = simplexml_load_string($xml);
    libxml_use_internal_errors($prev);
    if (!$rss || !isset($rss->channel->item)) return [];

    $today  = strtotime('today');
    $events = [];
    foreach ($rss->channel->item as $it) {
        $ts = strtotime((string) $it->pubDate);
        if (!$ts || $ts < $today) continue;
        $title = trim((string) $it->title);
        if ($title === '') continue;
        $events[] = ['title' => $title, 'link' => trim((string) $it->link), 'ts' => $ts];
    }
    usort($events, fn($a, $b) => $a['ts'] <=> $b['ts']);
    return array_slice($events, 0, $limit);
}

function afa_fetch($url) {
    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 5,
            CURLOPT_CONNECTTIMEOUT => 4,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_USERAGENT      => 'MusubiDojo/1.0 (+https://jurbise.aikido.be)',
        ]);
        $resp = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return ($resp !== false && $code >= 200 && $code < 400) ? $resp : null;
    }
    if (ini_get('allow_url_fopen')) {
        $ctx  = stream_context_create(['http' => ['timeout' => 5, 'user_agent' => 'MusubiDojo/1.0']]);
        $resp = @file_get_contents($url, false, $ctx);
        return $resp !== false ? $resp : null;
    }
    return null;
}

/* ------------------------------------------------- Prochain cours */
function next_course($infos) {
    $days = ['lundi' => 1, 'mardi' => 2, 'mercredi' => 3, 'jeudi' => 4, 'vendredi' => 5, 'samedi' => 6, 'dimanche' => 7];
    $daysFr = array_flip($days);
    $now = new DateTime('now');
    $best = null;
    foreach (($infos['horaires'] ?? []) as $h) {
        $label = strtolower($h['label'] ?? '');
        $dow = null;
        foreach ($days as $name => $n) if (strpos($label, $name) !== false) { $dow = $n; break; }
        if (!$dow) continue;
        if (!preg_match('/(\d{1,2})\s*h\s*(\d{2})?/', $h['value'] ?? '', $m)) continue;
        $hh = (int) $m[1];
        $mm = (isset($m[2]) && $m[2] !== '') ? (int) $m[2] : 0;
        $d = new DateTime('now');
        $add = ($dow - (int) $d->format('N') + 7) % 7;
        if ($add > 0) $d->modify("+$add day");
        $d->setTime($hh, $mm, 0);
        if ($d <= $now) $d->modify('+7 day');
        if ($best === null || $d < $best) $best = $d;
    }
    if (!$best) return null;
    return $daysFr[(int) $best->format('N')] . ' à ' . $best->format('G\hi');
}

/* ------------------------------------------------- Optimisation d'image */
/**
 * Redimensionne l'image (max $maxDim) et crée une version WebP à côté.
 * Renvoie le chemin relatif WebP (images/xxx.webp) ou null.
 */
function optimize_image($absPath, $maxDim = 1600) {
    if (!function_exists('imagecreatefromstring')) return null;
    $raw = @file_get_contents($absPath);
    if ($raw === false) return null;
    $im = @imagecreatefromstring($raw);
    if (!$im) return null;
    $w = imagesx($im);
    $h = imagesy($im);
    $ext = strtolower(pathinfo($absPath, PATHINFO_EXTENSION));
    if (max($w, $h) > $maxDim) {
        $scale = $maxDim / max($w, $h);
        $nw = (int) round($w * $scale);
        $nh = (int) round($h * $scale);
        $dst = imagecreatetruecolor($nw, $nh);
        imagealphablending($dst, false);
        imagesavealpha($dst, true);
        imagecopyresampled($dst, $im, 0, 0, 0, 0, $nw, $nh, $w, $h);
        imagedestroy($im);
        $im = $dst;
        if ($ext === 'jpg' || $ext === 'jpeg')      imagejpeg($im, $absPath, 85);
        elseif ($ext === 'png')                     imagepng($im, $absPath, 6);
        elseif ($ext === 'webp' && function_exists('imagewebp')) imagewebp($im, $absPath, 82);
        elseif ($ext === 'gif')                     imagegif($im, $absPath);
    }
    $webpRel = null;
    if (function_exists('imagewebp')) {
        $webpAbs = preg_replace('/\.[a-z0-9]+$/i', '.webp', $absPath);
        if (@imagewebp($im, $webpAbs, 82)) $webpRel = 'images/' . basename($webpAbs);
    }
    imagedestroy($im);
    return $webpRel;
}
