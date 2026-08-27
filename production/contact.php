<?php
/**
 * Traitement des formulaires (contact + cours d'essai) du Musubi Dojo.
 * - Anti-spam : honeypot + jeton signé (time-trap) + Cloudflare Turnstile (si configuré).
 * - Enregistre le message dans data/messages.json (boîte de réception de l'admin).
 * - Envoie un e-mail au club et, en option, un accusé de réception au visiteur.
 */
require __DIR__ . '/inc/functions.php';

function contact_back($ok, $anchor = 'contact') {
    $base = base_url();
    header('Location: ' . ($ok ? $base . '/merci.html' : $base . '/index.php?err=1#' . $anchor));
    exit;
}
function mail_utf8($to, $subject, $body, $headers) {
    return @mail($to, '=?UTF-8?B?' . base64_encode($subject) . '?=', $body, $headers);
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') contact_back(false);

$type   = (($_POST['type'] ?? 'contact') === 'essai') ? 'essai' : 'contact';
$anchor = $type === 'essai' ? 'essai' : 'contact';

// 1) Honeypot : si rempli, c'est un robot. On simule un succès.
if (!empty($_POST['website'])) contact_back(true, $anchor);
// 2) Jeton signé + délai minimal (piège temporel)
if (!verify_form_token($_POST['ftok'] ?? '')) contact_back(false, $anchor);
// 3) Cloudflare Turnstile (si activé)
if (!turnstile_verify($_POST['cf-turnstile-response'] ?? '')) contact_back(false, $anchor);

$nom     = trim($_POST['nom'] ?? '');
$email   = trim($_POST['email'] ?? '');
$message = trim($_POST['message'] ?? '');
$phone   = trim($_POST['phone'] ?? '');
$age     = trim($_POST['age'] ?? '');
$wanted  = trim($_POST['wanted'] ?? '');

if ($nom === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) contact_back(false, $anchor);
if ($type === 'contact' && $message === '') contact_back(false, $anchor);
if (strlen($nom) > 120 || strlen($email) > 200 || strlen($message) > 6000
    || strlen($phone) > 40 || strlen($age) > 40 || strlen($wanted) > 120) contact_back(false, $anchor);
$nom   = preg_replace('/[\r\n]+/', ' ', $nom);
$email = preg_replace('/[\r\n]+/', '', $email);

/* --- Enregistrement dans la boîte de réception --- */
$msgs = load_json('messages.json', []);
array_unshift($msgs, [
    'id'      => bin2hex(random_bytes(6)),
    'ts'      => date('c'),
    'type'    => $type,
    'name'    => $nom,
    'email'   => $email,
    'phone'   => $phone,
    'age'     => $age,
    'wanted'  => $wanted,
    'message' => $message,
    'read'    => false,
]);
if (count($msgs) > 500) $msgs = array_slice($msgs, 0, 500);
save_json('messages.json', $msgs);

/* --- Composition de l'e-mail au club --- */
$to       = config('mail_to', 'benoit.toulotte@aikido.be');
$from     = config('mail_from', 'site@localhost');
$siteName = config('site_name', 'Musubi Dojo Jurbise');

$subject = ($type === 'essai' ? "Demande de cours d'essai" : 'Nouveau message') . ' - ' . $siteName;
$lines   = ['Type : ' . ($type === 'essai' ? "Cours d'essai" : 'Contact'), 'Nom : ' . $nom, 'E-mail : ' . $email];
if ($phone  !== '') $lines[] = 'Téléphone : ' . $phone;
if ($age    !== '') $lines[] = 'Âge : ' . $age;
if ($wanted !== '') $lines[] = 'Disponibilité souhaitée : ' . $wanted;
if ($message !== '') $lines[] = "\nMessage :\n" . $message;
$body = implode("\n", $lines) . "\n";

$headers = 'From: ' . $siteName . ' <' . $from . ">\r\n"
         . 'Reply-To: ' . $email . "\r\n"
         . "Content-Type: text/plain; charset=UTF-8\r\nMIME-Version: 1.0";

if (config('dev_no_mail')) {
    @file_put_contents(__DIR__ . '/inc/_contact-dev.log',
        date('c') . " | {$type} | {$nom} | {$email} | " . str_replace("\n", ' ', $body) . "\n", FILE_APPEND);
    contact_back(true, $anchor);
}

mail_utf8($to, $subject, $body, $headers);

/* --- Accusé de réception au visiteur --- */
if (config('autoreply')) {
    $arBody = "Bonjour {$nom},\n\n"
            . "Merci pour votre message. Nous l'avons bien reçu et vous répondrons dès que possible.\n\n"
            . "À bientôt sur le tatami,\nL'équipe du {$siteName}\n";
    $arHeaders = 'From: ' . $siteName . ' <' . $from . ">\r\nContent-Type: text/plain; charset=UTF-8\r\nMIME-Version: 1.0";
    mail_utf8($email, "Nous avons bien reçu votre message - {$siteName}", $arBody, $arHeaders);
}

contact_back(true, $anchor);
