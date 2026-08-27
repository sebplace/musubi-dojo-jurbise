<?php
/**
 * Bibliothèque du panneau d'administration Musubi Dojo.
 * Session, authentification, protection CSRF, upload d'images, mise en page.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax']);
    session_start();
}
require_once __DIR__ . '/../inc/functions.php';

/* ------------------------------------------------------------------ Auth */
function is_logged_in() {
    return !empty($_SESSION['admin_ok']);
}

function attempt_login($user, $pass) {
    $u = (string) config('admin_user', 'admin');
    $h = (string) config('admin_pass_hash', '');
    if ($h !== '' && hash_equals($u, (string) $user) && password_verify((string) $pass, $h)) {
        session_regenerate_id(true);
        $_SESSION['admin_ok'] = true;
        return true;
    }
    return false;
}

function require_login() {
    if (!is_logged_in()) {
        header('Location: index.php');
        exit;
    }
}

function do_logout() {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}

/* ------------------------------------------------------------------ CSRF */
function csrf_token() {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16));
    return $_SESSION['csrf'];
}
function csrf_field() {
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}
function csrf_check() {
    if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) {
        http_response_code(400);
        exit('Jeton de sécurité invalide. Revenez en arrière et rechargez la page.');
    }
}

/* ---------------------------------------------------------------- Flash */
function flash($msg, $type = 'ok') {
    $_SESSION['flash'][] = ['m' => $msg, 't' => $type];
}
function take_flashes() {
    $f = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $f;
}

/* --------------------------------------------------------------- Upload */
/** Traite un champ fichier image. Renvoie [chemin_relatif|null, erreur|null]. */
function upload_image($field, $prefix) {
    if (empty($_FILES[$field]) || ($_FILES[$field]['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return [null, null]; // aucun fichier envoyé (facultatif)
    }
    $f = $_FILES[$field];
    if ($f['error'] !== UPLOAD_ERR_OK)          return [null, "Erreur lors de l'envoi du fichier."];
    if ($f['size'] > 8 * 1024 * 1024)           return [null, "Fichier trop lourd (maximum 8 Mo)."];
    $info = @getimagesize($f['tmp_name']);
    if (!$info)                                 return [null, "Le fichier n'est pas une image valide."];
    $map = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'];
    $mime = $info['mime'] ?? '';
    if (!isset($map[$mime]))                    return [null, "Format non supporté (JPEG, PNG, WebP ou GIF)."];
    $ext  = $map[$mime];
    $slug = preg_replace('/[^a-z0-9]+/', '-', strtolower($prefix)) ?: 'photo';
    $name = trim($slug, '-') . '-' . date('Ymd') . '-' . bin2hex(random_bytes(4)) . '.' . $ext;
    $dest = IMG_DIR . '/' . $name;
    if (!move_uploaded_file($f['tmp_name'], $dest)) {
        return [null, "Impossible d'enregistrer l'image sur le serveur."];
    }
    return ['images/' . $name, null];
}

/* --------------------------------------------------------------- Layout */
function admin_header($title, $active = '') {
    $nav = [
        ''            => 'Tableau de bord',
        'actualites'  => 'Actualités',
        'galerie'     => 'Galerie',
        'professeurs' => 'Professeurs',
        'infos'       => 'Infos pratiques',
    ];
    echo '<!DOCTYPE html><html lang="fr"><head><meta charset="utf-8">';
    echo '<meta name="viewport" content="width=device-width,initial-scale=1"><meta name="robots" content="noindex">';
    echo '<title>' . e($title) . ' · Admin Musubi Dojo</title><link rel="stylesheet" href="style.css"></head><body>';
    echo '<header class="a-head"><div class="a-brand"><span class="dot"></span> Musubi Dojo <small>Administration</small></div>';
    echo '<nav class="a-nav">';
    foreach ($nav as $p => $label) {
        $cls = ($active === $p) ? ' class="on"' : '';
        echo '<a href="index.php' . ($p ? '?p=' . $p : '') . '"' . $cls . '>' . e($label) . '</a>';
    }
    echo '<a href="../index.php" target="_blank" class="ext">Voir le site ↗</a>';
    echo '<a href="index.php?action=logout" class="out">Déconnexion</a>';
    echo '</nav></header><main class="a-main">';
    foreach (take_flashes() as $fl) {
        echo '<div class="flash ' . ($fl['t'] === 'err' ? 'err' : 'ok') . '">' . e($fl['m']) . '</div>';
    }
    echo '<h1>' . e($title) . '</h1>';
}

function admin_footer() {
    echo '</main></body></html>';
}
