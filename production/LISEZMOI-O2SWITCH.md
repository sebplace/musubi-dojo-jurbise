# Déploiement sur O2switch : mode d'emploi

Ce dossier `production/` contient la version **PHP** du site du Musubi Dojo, prête à être
hébergée sur O2switch (ou tout hébergement PHP 8). Elle comprend le site public, le
formulaire de contact et le panneau d'administration.

> La version GitHub Pages (statique) reste la maquette de démonstration. C'est **cette
> version-ci** que vous mettez en ligne chez O2switch.

---

## 1. Contenu du dossier

| Élément | Rôle |
|---|---|
| `index.php` | Page d'accueil (génère les sections depuis `data/`) |
| `contact.php` | Traitement du formulaire de contact (envoi e-mail) |
| `admin/` | Panneau d'administration protégé par mot de passe |
| `data/` | Contenu éditable : actualités, galerie, professeurs, infos pratiques (JSON) |
| `inc/` | Fonctions et **configuration** (protégé, non public) |
| `images/`, `fichiers/` | Médias, glossaire, photos (les envois du panneau arrivent ici) |
| `merci.html`, `404.html` | Page de confirmation et page d'erreur |
| `.htaccess` | Page 404, sécurité, cache |

---

## 2. Envoyer les fichiers

1. Connectez-vous à **cPanel** (o2switch) puis ouvrez le **Gestionnaire de fichiers**
   (ou utilisez un client FTP comme FileZilla).
2. Placez **tout le contenu** du dossier `production/` dans le dossier de votre site :
   - domaine principal : `public_html/`
   - ou sous-domaine / dossier dédié selon votre configuration.
3. Vérifiez que la page s'affiche en ouvrant votre domaine dans un navigateur.

> Si vous déposez le site dans un **sous-dossier** (ex. `public_html/dojo/`), modifiez la
> première ligne de `.htaccess` en `ErrorDocument 404 /dojo/404.html`.

---

## 3. Configurer (obligatoire)

1. Dans `inc/`, **copiez** `config.sample.php` en `config.php`.
2. Ouvrez `config.php` et renseignez :
   - `admin_pass_hash` : le mot de passe du panneau d'admin, **haché**. Pour le générer,
     ouvrez le **Terminal** de cPanel et lancez :
     ```
     php -r "echo password_hash('VotreMotDePasse', PASSWORD_DEFAULT);"
     ```
     Copiez la ligne obtenue (commençant par `$2y$...`) dans `admin_pass_hash`.
   - `mail_to` : l'adresse qui **reçoit** les messages (ex. `benoit.toulotte@aikido.be`).
   - `mail_from` : une adresse **de votre domaine** créée dans cPanel
     (ex. `site@votre-domaine.be`). Important pour que les e-mails ne partent pas en spam.
   - Laissez `dev_no_mail` à `false` (ou supprimez la ligne).
3. Ne partagez jamais `config.php` : il contient le mot de passe (haché) de l'admin.

---

## 4. Droits d'écriture

Le site enregistre le contenu dans `data/` et les photos dans `images/`. Sur O2switch,
ces dossiers sont généralement inscriptibles par défaut. En cas de message d'erreur à
l'enregistrement, réglez les permissions (via cPanel) :

- Dossiers `data/` et `images/` : **755**
- Fichiers `data/*.json` : **644**

---

## 5. Utiliser le panneau d'administration

- Adresse : `https://votre-domaine.be/admin/`
- Identifiant : `admin` (modifiable dans `config.php`), mot de passe : celui choisi à l'étape 3.
- Vous pouvez gérer :
  - **Actualités** : ajouter, modifier, réordonner, supprimer les encarts d'accueil.
  - **Galerie** : envoyer, légender, réordonner, retirer des photos.
  - **Professeurs** : nom, rôle, grade, photo, cadrage du visage.
  - **Infos pratiques** : horaires, tarifs, conditions d'inscription.
- Les changements sont visibles **immédiatement** sur le site.

---

## 6. Formulaire de contact

Le formulaire envoie un e-mail via la fonction `mail()` de PHP (native O2switch). Pour une
délivrabilité optimale, `mail_from` doit être une adresse réelle de votre domaine. Si
besoin d'un envoi authentifié (SMTP), on peut faire évoluer `contact.php` vers PHPMailer.

---

## 7. Sécurité (déjà en place)

- Les dossiers `inc/` et `data/` sont **inaccessibles** directement (fichiers `.htaccess`).
- Le panneau d'admin est protégé par mot de passe, avec jeton anti-CSRF et sessions.
- Les uploads sont limités aux images (JPEG, PNG, WebP, GIF), 8 Mo maximum.

Bonne mise en ligne. En cas de doute, gardez une copie de sauvegarde du dossier `data/`.
