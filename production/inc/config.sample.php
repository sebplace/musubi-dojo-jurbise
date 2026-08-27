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

    // --- Développement local uniquement ---
    // Mettez true en local (pas de serveur mail) pour tester le parcours sans envoi réel.
    // Laissez false / supprimez la ligne en production.
    'dev_no_mail' => false,
];
