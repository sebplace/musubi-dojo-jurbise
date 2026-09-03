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
/** Envoi d'un e-mail texte avec une pièce jointe PDF (multipart/mixed). */
function mail_with_pdf($to, $subject, $text, $siteName, $from, $pdfPath, $pdfName) {
    $data = @file_get_contents($pdfPath);
    if ($data === false) return false;
    $boundary = '=_mdj_' . bin2hex(random_bytes(10));
    $subj = '=?UTF-8?B?' . base64_encode($subject) . '?=';
    $headers = 'From: ' . $siteName . ' <' . $from . ">\r\n"
             . "MIME-Version: 1.0\r\n"
             . 'Content-Type: multipart/mixed; boundary="' . $boundary . '"';
    $body  = "--{$boundary}\r\n"
           . "Content-Type: text/plain; charset=UTF-8\r\n"
           . "Content-Transfer-Encoding: 8bit\r\n\r\n"
           . $text . "\r\n"
           . "--{$boundary}\r\n"
           . 'Content-Type: application/pdf; name="' . $pdfName . "\"\r\n"
           . "Content-Transfer-Encoding: base64\r\n"
           . 'Content-Disposition: attachment; filename="' . $pdfName . "\"\r\n\r\n"
           . chunk_split(base64_encode($data)) . "\r\n"
           . "--{$boundary}--";
    return @mail($to, $subj, $body, $headers);
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
    $licRel = settings('license_pdf');
    $licAbs = $licRel ? __DIR__ . '/' . $licRel : '';
    $hasLicense = $licRel && is_file($licAbs);

    $arBody = "Bonjour {$nom},\n\n"
            . "Merci pour votre message. Nous l'avons bien reçu et vous répondrons dès que possible.\n\n";
    if ($hasLicense) {
        $arBody .= "Vous trouverez en pièce jointe le formulaire de licence de la fédération (AFA). "
                 . "Il n'est à compléter que si vous décidez de nous rejoindre après votre cours d'essai : rien ne presse.\n\n";
    }
    $arBody .= "À bientôt sur le tatami,\nL'équipe du {$siteName}\n";

    $arSubject = "Nous avons bien reçu votre message - {$siteName}";
    if ($hasLicense) {
        mail_with_pdf($email, $arSubject, $arBody, $siteName, $from, $licAbs, 'Formulaire-licence-AFA-Musubi-Dojo.pdf');
    } else {
        $arHeaders = 'From: ' . $siteName . ' <' . $from . ">\r\nContent-Type: text/plain; charset=UTF-8\r\nMIME-Version: 1.0";
        mail_utf8($email, $arSubject, $arBody, $arHeaders);
    }
}

contact_back(true, $anchor);
