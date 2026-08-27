<?php
/**
 * Configuration du site Musubi Dojo (production O2switch).
 *
 * ÉTAPES :
 *   1. Copiez ce fichier sous le nom  config.php  (dans le même dossier /inc).
 *   2. Adaptez les valeurs ci-dessous.
 *   3. Générez le hash du mot de passe admin avec la commande :
 *          php -r "echo password_hash('VotreMotDePasse', PASSWORD_DEFAULT);"
 *      puis collez le résultat dans 'admin_pass_hash'.
 *      (Vous pourrez ensuite changer ce mot de passe directement dans le panneau d'admin.)
 *
 * Ne publiez jamais config.php : il contient le mot de passe (haché) du panneau d'admin.
 */
return [
    // --- Panneau d'administration (/admin) ---
    'admin_user'      => 'admin',
    'admin_pass_hash' => 'REMPLACEZ_PAR_LE_HASH', // voir étape 3 ci-dessus

    // --- Formulaire de contact ---
    'mail_to'   => 'benoit.toulotte@aikido.be',        // destinataire des messages
    'mail_from' => 'site@votre-domaine.be',            // DOIT être une adresse de VOTRE domaine (O2switch)
    'site_name' => 'Musubi Dojo Jurbise',
    'autoreply' => true,                               // accusé de réception automatique au visiteur

    // --- Sécurité des formulaires ---
    'app_secret' => 'changez-cette-chaine-par-une-valeur-aleatoire',
    // Cloudflare Turnstile (anti-robot invisible, gratuit). Laissez vide pour désactiver.
    // Clés sur https://dash.cloudflare.com/ > Turnstile.
    'turnstile_site'   => '',
    'turnstile_secret' => '',

    // --- Statistiques de visite (facultatif) ---
    // Collez ici le code de suivi (Matomo, Plausible...) injecté avant </head>. Vide = aucun.
    'analytics' => '',

    // --- Coordonnées pour la page Mentions légales ---
    'legal_editor'  => 'Ecole d\'Aikido Musubi Dojo',
    'legal_address' => 'Route d\'Ath 25-35, 7050 Jurbise, Belgique',
    'legal_email'   => 'benoit.toulotte@aikido.be',
    'legal_host'    => 'o2switch, 222-224 Boulevard Gustave Flaubert, 63000 Clermont-Ferrand, France',

    // --- Développement local uniquement ---
    // true en local (pas de serveur mail) pour tester le parcours sans envoi reel. false en production.
    'dev_no_mail' => false,
];
