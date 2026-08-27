<?php
/**
 * Traitement du formulaire de contact du Musubi Dojo.
 * Envoi par mail() natif (compatible O2switch). Redirige vers merci.html en cas de succès.
 */
require __DIR__ . '/inc/functions.php';

function contact_back($ok) {
    $base = base_url();
    header('Location: ' . ($ok ? $base . '/merci.html' : $base . '/index.php?err=1#contact'));
    exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    contact_back(false);
}

// Anti-spam : champ piège (honeypot). Rempli => robot : on fait comme si tout allait bien.
if (!empty($_POST['website'])) {
    contact_back(true);
}

$nom     = trim($_POST['nom'] ?? '');
$email   = trim($_POST['email'] ?? '');
$message = trim($_POST['message'] ?? '');

// Validation
if ($nom === '' || $message === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    contact_back(false);
}
if (strlen($nom) > 120 || strlen($email) > 200 || strlen($message) > 6000) {
    contact_back(false);
}
// Neutralise toute tentative d'injection d'en-tête
$nom   = preg_replace('/[\r\n]+/', ' ', $nom);
$email = preg_replace('/[\r\n]+/', '', $email);

$to       = config('mail_to', 'benoit.toulotte@aikido.be');
$from     = config('mail_from', 'site@localhost');
$siteName = config('site_name', 'Musubi Dojo Jurbise');

$subject = 'Nouveau message depuis le site ' . $siteName;
$body    = "Nouveau message reçu depuis le formulaire de contact du site.\n\n"
         . "Nom    : {$nom}\n"
         . "E-mail : {$email}\n\n"
         . "Message :\n{$message}\n";

$headers  = 'From: ' . $siteName . ' <' . $from . ">\r\n";
$headers .= 'Reply-To: ' . $email . "\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
$headers .= "MIME-Version: 1.0\r\n";
$headers .= 'X-Mailer: PHP/' . phpversion();

$encodedSubject = '=?UTF-8?B?' . base64_encode($subject) . '?=';

// Mode développement local : pas de serveur mail, on journalise seulement.
if (config('dev_no_mail')) {
    @file_put_contents(
        __DIR__ . '/inc/_contact-dev.log',
        date('c') . " | {$nom} | {$email} | " . str_replace("\n", ' ', $message) . "\n",
        FILE_APPEND
    );
    contact_back(true);
}

$ok = @mail($to, $encodedSubject, $body, $headers);
contact_back($ok);
