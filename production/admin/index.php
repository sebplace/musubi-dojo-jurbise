<?php
/**
 * Panneau d'administration Musubi Dojo : contrôleur principal.
 */
require __DIR__ . '/lib.php';

/* ----------------------------------------------------------- Déconnexion */
if (($_GET['action'] ?? '') === 'logout') {
    do_logout();
    header('Location: index.php');
    exit;
}

/* --------------------------------------------------------------- Connexion */
if (!is_logged_in()) {
    $err = '';
    if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
        if (attempt_login($_POST['user'] ?? '', $_POST['pass'] ?? '')) {
            header('Location: index.php');
            exit;
        }
        usleep(400000);
        $err = 'Identifiant ou mot de passe incorrect.';
    }
    ?><!DOCTYPE html><html lang="fr"><head><meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1"><meta name="robots" content="noindex">
    <title>Connexion · Admin Musubi Dojo</title><link rel="stylesheet" href="style.css"></head>
    <body class="login-body"><form class="login" method="POST">
      <div class="a-brand center"><span class="dot"></span> Musubi Dojo <small>Administration</small></div>
      <?php if ($err): ?><div class="flash err"><?php echo e($err); ?></div><?php endif; ?>
      <label>Identifiant<input name="user" autofocus required></label>
      <label>Mot de passe<input name="pass" type="password" required></label>
      <button type="submit">Se connecter</button>
    </form></body></html><?php
    exit;
}

$p = $_GET['p'] ?? '';

function redirect($q = '') {
    header('Location: index.php' . ($q ? ('?' . $q) : ''));
    exit;
}
function parse_kv($text) {
    $out = [];
    foreach (preg_split('/\r\n|\r|\n/', (string) $text) as $line) {
        $line = trim($line);
        if ($line === '') continue;
        $parts = explode('|', $line, 2);
        $out[] = ['label' => trim($parts[0]), 'value' => trim($parts[1] ?? '')];
    }
    return $out;
}
function kv_to_text($arr) {
    return implode("\n", array_map(fn($x) => ($x['label'] ?? '') . ' | ' . ($x['value'] ?? ''), $arr ?: []));
}
function move_item(&$arr, $idx, $dir) {
    $j = $dir === 'up' ? $idx - 1 : $idx + 1;
    if (isset($arr[$idx], $arr[$j])) { $t = $arr[$idx]; $arr[$idx] = $arr[$j]; $arr[$j] = $t; }
}

/* ============================================================ TRAITEMENT POST */
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    csrf_check();
    $do = $_POST['do'] ?? '';

    /* ---------------- ACTUALITÉS ---------------- */
    if ($p === 'actualites') {
        $items = load_json('actualites.json', []);
        if ($do === 'delete') {
            $i = (int) ($_POST['idx'] ?? -1);
            if (isset($items[$i])) array_splice($items, $i, 1);
            save_json('actualites.json', array_values($items));
            flash('Actualité supprimée.');
        } elseif ($do === 'move') {
            move_item($items, (int) $_POST['idx'], $_POST['dir'] ?? '');
            save_json('actualites.json', array_values($items));
        } elseif ($do === 'save') {
            $idx = $_POST['idx'] === '' ? null : (int) $_POST['idx'];
            $item = [
                'date'      => trim($_POST['date'] ?? ''),
                'title'     => trim($_POST['title'] ?? ''),
                'text'      => trim($_POST['text'] ?? ''),
                'image'     => '',
                'imageWebp' => '',
                'imageAlt'  => trim($_POST['imageAlt'] ?? ''),
                'link'      => trim($_POST['link'] ?? ''),
                'linkLabel' => trim($_POST['linkLabel'] ?? ''),
            ];
            // conserver l'image existante en édition
            if ($idx !== null && isset($items[$idx])) {
                $item['image']     = $items[$idx]['image'] ?? '';
                $item['imageWebp'] = $items[$idx]['imageWebp'] ?? '';
            }
            [$up, $upWebp, $upErr] = upload_image('image', 'actu');
            if ($upErr) { flash($upErr, 'err'); redirect('p=actualites'); }
            if ($up) { $item['image'] = $up; $item['imageWebp'] = $upWebp ?? ''; }
            if (!empty($_POST['remove_image'])) { $item['image'] = ''; $item['imageWebp'] = ''; }
            if ($item['title'] === '') { flash('Le titre est obligatoire.', 'err'); redirect('p=actualites'); }
            if ($idx !== null && isset($items[$idx])) $items[$idx] = $item; else $items[] = $item;
            save_json('actualites.json', array_values($items));
            flash('Actualité enregistrée.');
        }
        redirect('p=actualites');
    }

    /* ---------------- GALERIE ---------------- */
    if ($p === 'galerie') {
        $items = load_json('galerie.json', []);
        if ($do === 'delete') {
            $i = (int) ($_POST['idx'] ?? -1);
            if (isset($items[$i])) array_splice($items, $i, 1);
            save_json('galerie.json', array_values($items));
            flash('Photo retirée de la galerie.');
        } elseif ($do === 'move') {
            move_item($items, (int) $_POST['idx'], $_POST['dir'] ?? '');
            save_json('galerie.json', array_values($items));
        } elseif ($do === 'alt') {
            $i = (int) ($_POST['idx'] ?? -1);
            if (isset($items[$i])) { $items[$i]['alt'] = trim($_POST['alt'] ?? ''); save_json('galerie.json', $items); flash('Légende mise à jour.'); }
        } elseif ($do === 'add') {
            [$up, $upWebp, $upErr] = upload_image('image', 'galerie');
            if ($upErr) { flash($upErr, 'err'); redirect('p=galerie'); }
            if (!$up) { flash('Veuillez choisir une image.', 'err'); redirect('p=galerie'); }
            $items[] = ['img' => $up, 'webp' => $upWebp ?? '', 'alt' => trim($_POST['alt'] ?? '')];
            save_json('galerie.json', array_values($items));
            flash('Photo ajoutée à la galerie.');
        }
        redirect('p=galerie');
    }

    /* ---------------- PROFESSEURS ---------------- */
    if ($p === 'professeurs') {
        $items = load_json('professeurs.json', []);
        if ($do === 'delete') {
            $i = (int) ($_POST['idx'] ?? -1);
            if (isset($items[$i])) array_splice($items, $i, 1);
            save_json('professeurs.json', array_values($items));
            flash('Professeur supprimé.');
        } elseif ($do === 'move') {
            move_item($items, (int) $_POST['idx'], $_POST['dir'] ?? '');
            save_json('professeurs.json', array_values($items));
        } elseif ($do === 'save') {
            $idx  = $_POST['idx'] === '' ? null : (int) $_POST['idx'];
            $item = [
                'name'  => trim($_POST['name'] ?? ''),
                'role'  => trim($_POST['role'] ?? ''),
                'grade' => trim($_POST['grade'] ?? ''),
                'photo' => '',
                'webp'  => '',
                'pos'   => trim($_POST['pos'] ?? '') ?: '50% 30%',
            ];
            if ($idx !== null && isset($items[$idx])) {
                $item['photo'] = $items[$idx]['photo'] ?? '';
                $item['webp']  = $items[$idx]['webp'] ?? '';
            }
            [$up, $upWebp, $upErr] = upload_image('photo', 'prof');
            if ($upErr) { flash($upErr, 'err'); redirect('p=professeurs'); }
            if ($up) { $item['photo'] = $up; $item['webp'] = $upWebp ?? ''; }
            if ($item['name'] === '') { flash('Le nom est obligatoire.', 'err'); redirect('p=professeurs'); }
            if ($idx !== null && isset($items[$idx])) $items[$idx] = $item; else $items[] = $item;
            save_json('professeurs.json', array_values($items));
            flash('Professeur enregistré.');
        }
        redirect('p=professeurs');
    }

    /* ---------------- RÉPERTOIRE (TECHNIQUES) ---------------- */
    if ($p === 'techniques') {
        $items = load_json('techniques.json', []);
        if ($do === 'delete') {
            $i = (int) ($_POST['idx'] ?? -1);
            if (isset($items[$i])) array_splice($items, $i, 1);
            save_json('techniques.json', array_values($items));
            flash('Technique supprimée.');
        } elseif ($do === 'move') {
            move_item($items, (int) $_POST['idx'], $_POST['dir'] ?? '');
            save_json('techniques.json', array_values($items));
        } elseif ($do === 'save') {
            $idx  = $_POST['idx'] === '' ? null : (int) $_POST['idx'];
            $item = ['t' => trim($_POST['t'] ?? ''), 'd' => trim($_POST['d'] ?? '')];
            $url  = trim($_POST['url'] ?? '');
            if ($url !== '') $item['url'] = $url;
            $img = ''; $webp = '';
            if ($idx !== null && isset($items[$idx])) { $img = $items[$idx]['img'] ?? ''; $webp = $items[$idx]['webp'] ?? ''; }
            [$up, $upWebp, $upErr] = upload_image('img', 'technique');
            if ($upErr) { flash($upErr, 'err'); redirect('p=techniques'); }
            if ($up) { $img = $up; $webp = $upWebp ?? ''; }
            if (!empty($_POST['remove_image'])) { $img = ''; $webp = ''; }
            if ($img !== '') { $item['img'] = $img; $item['webp'] = $webp; }
            if ($item['t'] === '') { flash('Le nom de la technique est obligatoire.', 'err'); redirect('p=techniques'); }
            if ($idx !== null && isset($items[$idx])) $items[$idx] = $item; else $items[] = $item;
            save_json('techniques.json', array_values($items));
            flash('Technique enregistrée.');
        }
        redirect('p=techniques');
    }

    /* ---------------- INFOS PRATIQUES ---------------- */
    if ($p === 'infos') {
        $infos = [
            'horaires'          => parse_kv($_POST['horaires'] ?? ''),
            'horaires_note'     => trim($_POST['horaires_note'] ?? ''),
            'tarifs'            => parse_kv($_POST['tarifs'] ?? ''),
            'tarifs_note'       => trim($_POST['tarifs_note'] ?? ''),
            'inscription'       => parse_kv($_POST['inscription'] ?? ''),
            'inscription_note'  => trim($_POST['inscription_note'] ?? ''),
        ];
        save_json('infos.json', $infos);
        flash('Infos pratiques mises à jour.');
        redirect('p=infos');
    }

    /* ---------------- MESSAGES ---------------- */
    if ($p === 'messages') {
        $msgs = load_json('messages.json', []);
        $id = $_POST['id'] ?? '';
        if ($do === 'read' || $do === 'unread') {
            foreach ($msgs as &$m) if (($m['id'] ?? '') === $id) $m['read'] = ($do === 'read');
            unset($m);
        } elseif ($do === 'delete') {
            $msgs = array_values(array_filter($msgs, fn($m) => ($m['id'] ?? '') !== $id));
            flash('Message supprimé.');
        } elseif ($do === 'clear') {
            $msgs = [];
            flash('Boîte de réception vidée.');
        }
        save_json('messages.json', $msgs);
        redirect('p=messages');
    }

    /* ---------------- STAGES ---------------- */
    if ($p === 'stages') {
        $items = load_json('stages.json', []);
        if ($do === 'delete') {
            $i = (int) ($_POST['idx'] ?? -1);
            if (isset($items[$i])) array_splice($items, $i, 1);
            save_json('stages.json', array_values($items));
            flash('Stage supprimé.');
        } elseif ($do === 'save') {
            $idx  = $_POST['idx'] === '' ? null : (int) $_POST['idx'];
            $item = [
                'date' => trim($_POST['date'] ?? ''), 'title' => trim($_POST['title'] ?? ''),
                'teacher' => trim($_POST['teacher'] ?? ''), 'grade' => trim($_POST['grade'] ?? ''),
                'place' => trim($_POST['place'] ?? ''), 'image' => '', 'webp' => '', 'link' => trim($_POST['link'] ?? ''),
            ];
            if ($idx !== null && isset($items[$idx])) { $item['image'] = $items[$idx]['image'] ?? ''; $item['webp'] = $items[$idx]['webp'] ?? ''; }
            [$up, $upWebp, $upErr] = upload_image('image', 'stage');
            if ($upErr) { flash($upErr, 'err'); redirect('p=stages'); }
            if ($up) { $item['image'] = $up; $item['webp'] = $upWebp ?? ''; }
            if ($item['title'] === '') { flash('Le titre est obligatoire.', 'err'); redirect('p=stages'); }
            if ($idx !== null && isset($items[$idx])) $items[$idx] = $item; else $items[] = $item;
            usort($items, fn($a, $b) => strcmp($a['date'] ?? '', $b['date'] ?? ''));
            save_json('stages.json', array_values($items));
            flash('Stage enregistré.');
        }
        redirect('p=stages');
    }

    /* ---------------- TEXTES ---------------- */
    if ($p === 'textes') {
        if ($do === 'aikido') {
            $vals = [];
            $titles = $_POST['v_title'] ?? []; $texts = $_POST['v_text'] ?? [];
            for ($k = 0; $k < count($titles); $k++) { $t = trim($titles[$k]); if ($t === '') continue; $vals[] = ['title' => $t, 'text' => trim($texts[$k] ?? '')]; }
            save_json('aikido.json', ['lead' => trim($_POST['lead'] ?? ''), 'values' => $vals]);
            flash('Présentation de l\'aïkido mise à jour.');
        } elseif ($do === 'histoire') {
            $items = []; $years = $_POST['year'] ?? []; $texts = $_POST['htext'] ?? [];
            for ($k = 0; $k < count($years); $k++) { $y = trim($years[$k]); $tx = trim($texts[$k] ?? ''); if ($y === '' && $tx === '') continue; $items[] = ['year' => $y, 'text' => $tx]; }
            save_json('histoire.json', $items);
            flash('Frise historique mise à jour.');
        } elseif ($do === 'faq') {
            $items = []; $qs = $_POST['q'] ?? []; $as = $_POST['a'] ?? [];
            for ($k = 0; $k < count($qs); $k++) { $q = trim($qs[$k]); if ($q === '') continue; $items[] = ['q' => $q, 'a' => trim($as[$k] ?? '')]; }
            save_json('faq.json', $items);
            flash('FAQ mise à jour.');
        } elseif ($do === 'memoriam') {
            $mem = load_json('memoriam.json', []);
            $founders = array_values($mem['founders'] ?? []);
            for ($k = 0; $k < 2; $k++) {
                $row = $founders[$k] ?? [];
                $row['name']     = trim($_POST["mf{$k}_name"] ?? '');
                $row['subtitle'] = trim($_POST["mf{$k}_sub"] ?? '');
                [$up, $upWebp, $upErr] = upload_image("mf{$k}_photo", 'memoriam');
                if ($up) { $row['photo'] = $up; $row['webp'] = $upWebp ?? ''; }
                $founders[$k] = $row;
            }
            $mem['founders'] = array_values(array_filter($founders, fn($f) => trim($f['name'] ?? '') !== ''));
            $mem['founder_text'] = trim($_POST['f_text'] ?? '');
            unset($mem['founder']);
            save_json('memoriam.json', $mem);
            flash('In Memoriam mis à jour.');
        } elseif ($do === 'armes') {
            $armes = load_json('armes.json', []);
            $armes['grades_intro'] = trim($_POST['grades_intro'] ?? '');
            $armes['armes_intro']  = trim($_POST['armes_intro'] ?? '');
            $belts = []; $bl = $_POST['b_label'] ?? []; $bs = $_POST['b_sub'] ?? []; $bb = $_POST['b_bar'] ?? [];
            for ($k = 0; $k < count($bl); $k++) { $l = trim($bl[$k]); if ($l === '') continue; $belts[] = ['label' => $l, 'sub' => trim($bs[$k] ?? ''), 'bar' => trim($bb[$k] ?? '#e7dcc6')]; }
            $armes['belts'] = $belts;
            $w = []; $wk = $_POST['w_kanji'] ?? []; $wn = $_POST['w_nom'] ?? []; $wd = $_POST['w_desc'] ?? [];
            for ($k = 0; $k < count($wn); $k++) { $n = trim($wn[$k]); if ($n === '') continue; $w[] = ['kanji' => trim($wk[$k] ?? ''), 'nom' => $n, 'desc' => trim($wd[$k] ?? '')]; }
            $armes['armes'] = $w;
            save_json('armes.json', $armes);
            flash('Grades & armes mis à jour.');
        } elseif ($do === 'libelles') {
            save_json('libelles.json', [
                'hero_eyebrow'   => trim($_POST['hero_eyebrow'] ?? ''),
                'hero_title'     => trim($_POST['hero_title'] ?? ''),
                'hero_subtitle'  => trim($_POST['hero_subtitle'] ?? ''),
                'footer_tagline' => trim($_POST['footer_tagline'] ?? ''),
                'director'       => trim($_POST['director'] ?? ''),
            ]);
            flash('Libellés mis à jour.');
        }
        redirect('p=textes');
    }

    /* ---------------- GLOSSAIRE ---------------- */
    if ($p === 'glossaire') {
        $secs = load_json('glossaire.json', []);
        $si = (int) ($_POST['sec'] ?? -1);
        if ($do === 'save_section' && isset($secs[$si])) {
            $items = [];
            foreach (preg_split('/\r\n|\r|\n/', (string) ($_POST['lines'] ?? '')) as $line) {
                $line = trim($line);
                if ($line === '') continue;
                $parts = array_map('trim', explode('|', $line));
                $items[] = ['t' => $parts[0] ?? '', 'p' => $parts[1] ?? '', 'd' => $parts[2] ?? ''];
            }
            $secs[$si]['items'] = $items;
            save_json('glossaire.json', $secs);
            flash('Section « ' . ($secs[$si]['title'] ?? '') . ' » mise à jour.');
        }
        redirect('p=glossaire');
    }

    /* ---------------- BIBLIOGRAPHIE ---------------- */
    if ($p === 'bibliographie') {
        $items = load_json('bibliographie.json', []);
        if ($do === 'delete') {
            $i = (int) ($_POST['idx'] ?? -1);
            if (isset($items[$i])) array_splice($items, $i, 1);
            save_json('bibliographie.json', array_values($items));
            flash('Ouvrage supprimé.');
        } elseif ($do === 'save') {
            $idx  = $_POST['idx'] === '' ? null : (int) $_POST['idx'];
            $item = ['cat' => trim($_POST['cat'] ?? 'fr'), 't' => trim($_POST['t'] ?? ''), 'a' => trim($_POST['a'] ?? ''), 'e' => trim($_POST['e'] ?? ''), 'img' => ''];
            if ($idx !== null && isset($items[$idx])) $item['img'] = $items[$idx]['img'] ?? '';
            [$up, $upWebp, $upErr] = upload_image('cover', 'biblio');
            if ($upErr) { flash($upErr, 'err'); redirect('p=bibliographie'); }
            if ($up) $item['img'] = $up; // chemin complet (images/...), géré par l'affichage
            if ($item['t'] === '') { flash('Le titre est obligatoire.', 'err'); redirect('p=bibliographie'); }
            if ($idx !== null && isset($items[$idx])) $items[$idx] = $item; else $items[] = $item;
            save_json('bibliographie.json', array_values($items));
            flash('Ouvrage enregistré.');
        }
        redirect('p=bibliographie');
    }

    /* ---------------- COORDONNÉES ---------------- */
    if ($p === 'coordonnees') {
        if ($do === 'contact') {
            $tel = preg_replace('/[^0-9+]/', '', $_POST['phone1'] ?? '');
            save_json('contact.json', [
                'phone1' => trim($_POST['phone1'] ?? ''),
                'phone1_tel' => $tel,
                'phone2' => trim($_POST['phone2'] ?? ''),
                'email' => trim($_POST['email'] ?? ''),
                'address' => trim($_POST['address'] ?? ''),
                'address_full' => trim($_POST['address'] ?? ''),
                'maps' => trim($_POST['maps'] ?? ''),
                'whatsapp' => trim($_POST['whatsapp'] ?? ''),
                'facebook' => trim($_POST['facebook'] ?? ''),
            ]);
            flash('Coordonnées mises à jour.');
        } elseif ($do === 'liens') {
            $items = [];
            foreach (preg_split('/\r\n|\r|\n/', (string) ($_POST['lines'] ?? '')) as $line) {
                $line = trim($line);
                if ($line === '') continue;
                $parts = array_map('trim', explode('|', $line, 2));
                if (empty($parts[0])) continue;
                $items[] = ['label' => $parts[0], 'url' => $parts[1] ?? '#'];
            }
            save_json('liens.json', $items);
            flash('Liens utiles mis à jour.');
        }
        redirect('p=coordonnees');
    }

    /* ---------------- RÉGLAGES ---------------- */
    if ($p === 'reglages') {
        if ($do === 'password') {
            $err = change_password($_POST['current'] ?? '', $_POST['new'] ?? '', $_POST['confirm'] ?? '');
            flash($err ?? 'Mot de passe modifié.', $err ? 'err' : 'ok');
        } elseif ($do === 'settings') {
            save_json('settings.json', [
                'alert_enabled' => !empty($_POST['alert_enabled']),
                'alert_text'    => trim($_POST['alert_text'] ?? ''),
                'facebook_url'  => trim($_POST['facebook_url'] ?? ''),
            ]);
            flash('Réglages enregistrés.');
        }
        redirect('p=reglages');
    }

    redirect($p ? 'p=' . $p : '');
}

/* ============================================================ AFFICHAGE */
$reorder = function ($p, $i, $count) {
    $out = '<span class="reorder">';
    if ($i > 0) $out .= '<form method="POST">' . csrf_field() . '<input type="hidden" name="do" value="move"><input type="hidden" name="idx" value="' . $i . '"><input type="hidden" name="dir" value="up"><button title="Monter">▲</button></form>';
    if ($i < $count - 1) $out .= '<form method="POST">' . csrf_field() . '<input type="hidden" name="do" value="move"><input type="hidden" name="idx" value="' . $i . '"><input type="hidden" name="dir" value="down"><button title="Descendre">▼</button></form>';
    return $out . '</span>';
};

if ($p === 'actualites') {
    $items = load_json('actualites.json', []);
    $edit  = isset($_GET['edit']) ? (int) $_GET['edit'] : null;
    $cur   = ($edit !== null && isset($items[$edit])) ? $items[$edit] : null;
    admin_header('Actualités', 'actualites');
    ?>
    <p class="hint">Gérez les encarts d'actualités affichés sur la page d'accueil.</p>
    <div class="cols">
      <section class="panel">
        <h2><?php echo $cur ? "Modifier l'actualité" : 'Ajouter une actualité'; ?></h2>
        <form method="POST" enctype="multipart/form-data">
          <?php echo csrf_field(); ?>
          <input type="hidden" name="do" value="save">
          <input type="hidden" name="idx" value="<?php echo $edit !== null ? (int) $edit : ''; ?>">
          <label>Étiquette (ex. « Reprise de saison »)<input name="date" value="<?php echo e($cur['date'] ?? ''); ?>"></label>
          <label>Titre <span class="req">*</span><input name="title" required value="<?php echo e($cur['title'] ?? ''); ?>"></label>
          <label>Texte<textarea name="text" rows="4"><?php echo e($cur['text'] ?? ''); ?></textarea></label>
          <label>Lien (facultatif)<input name="link" value="<?php echo e($cur['link'] ?? ''); ?>" placeholder="https://..."></label>
          <label>Libellé du lien<input name="linkLabel" value="<?php echo e($cur['linkLabel'] ?? ''); ?>"></label>
          <?php if (!empty($cur['image'])): ?><p class="thumb"><img src="../<?php echo e($cur['image']); ?>" alt=""><label class="inline"><input type="checkbox" name="remove_image" value="1"> Retirer l'image</label></p><?php endif; ?>
          <label>Image (facultative)<input type="file" name="image" accept="image/*"></label>
          <label>Texte alternatif de l'image<input name="imageAlt" value="<?php echo e($cur['imageAlt'] ?? ''); ?>"></label>
          <div class="actions"><button type="submit">Enregistrer</button><?php if ($cur): ?> <a class="btn-sec" href="index.php?p=actualites">Annuler</a><?php endif; ?></div>
        </form>
      </section>
      <section class="panel">
        <h2>Actualités en ligne (<?php echo count($items); ?>)</h2>
        <?php if (!$items): ?><p class="muted">Aucune actualité pour le moment.</p><?php endif; ?>
        <ul class="list">
          <?php foreach ($items as $i => $it): ?>
          <li>
            <div class="li-main"><b><?php echo e($it['title'] ?? ''); ?></b><small><?php echo e($it['date'] ?? ''); ?></small></div>
            <div class="li-act">
              <?php echo $reorder('actualites', $i, count($items)); ?>
              <a class="btn-sec" href="index.php?p=actualites&edit=<?php echo $i; ?>">Modifier</a>
              <form method="POST" onsubmit="return confirm('Supprimer cette actualité ?');"><?php echo csrf_field(); ?><input type="hidden" name="do" value="delete"><input type="hidden" name="idx" value="<?php echo $i; ?>"><button class="danger">Supprimer</button></form>
            </div>
          </li>
          <?php endforeach; ?>
        </ul>
      </section>
    </div>
    <?php
    admin_footer();
    exit;
}

if ($p === 'galerie') {
    $items = load_json('galerie.json', []);
    admin_header('Galerie', 'galerie');
    ?>
    <p class="hint">Ajoutez, réorganisez ou retirez les photos de la galerie.</p>
    <section class="panel">
      <h2>Ajouter une photo</h2>
      <form method="POST" enctype="multipart/form-data" class="row-form">
        <?php echo csrf_field(); ?><input type="hidden" name="do" value="add">
        <input type="file" name="image" accept="image/*" required>
        <input name="alt" placeholder="Légende / description">
        <button type="submit">Ajouter</button>
      </form>
    </section>
    <section class="panel">
      <h2>Photos (<?php echo count($items); ?>)</h2>
      <div class="grid">
        <?php foreach ($items as $i => $g): ?>
        <div class="gcard">
          <img src="../<?php echo e($g['img'] ?? ''); ?>" alt="">
          <form method="POST" class="alt-form"><?php echo csrf_field(); ?><input type="hidden" name="do" value="alt"><input type="hidden" name="idx" value="<?php echo $i; ?>"><input name="alt" value="<?php echo e($g['alt'] ?? ''); ?>" placeholder="Légende"><button class="btn-sec">OK</button></form>
          <div class="li-act"><?php echo $reorder('galerie', $i, count($items)); ?>
            <form method="POST" onsubmit="return confirm('Retirer cette photo ?');"><?php echo csrf_field(); ?><input type="hidden" name="do" value="delete"><input type="hidden" name="idx" value="<?php echo $i; ?>"><button class="danger">Retirer</button></form>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </section>
    <?php
    admin_footer();
    exit;
}

if ($p === 'professeurs') {
    $items = load_json('professeurs.json', []);
    $edit  = isset($_GET['edit']) ? (int) $_GET['edit'] : null;
    $cur   = ($edit !== null && isset($items[$edit])) ? $items[$edit] : null;
    admin_header('Professeurs', 'professeurs');
    ?>
    <div class="cols">
      <section class="panel">
        <h2><?php echo $cur ? 'Modifier le professeur' : 'Ajouter un professeur'; ?></h2>
        <form method="POST" enctype="multipart/form-data">
          <?php echo csrf_field(); ?><input type="hidden" name="do" value="save"><input type="hidden" name="idx" value="<?php echo $edit !== null ? (int) $edit : ''; ?>">
          <label>Nom <span class="req">*</span><input name="name" required value="<?php echo e($cur['name'] ?? ''); ?>"></label>
          <label>Rôle (ex. « Dojo Cho · Professeur »)<input name="role" value="<?php echo e($cur['role'] ?? ''); ?>"></label>
          <label>Grade / précisions<input name="grade" value="<?php echo e($cur['grade'] ?? ''); ?>"></label>
          <label>Cadrage vertical de la photo<input name="pos" value="<?php echo e($cur['pos'] ?? '50% 30%'); ?>" placeholder="50% 30%"><small class="muted">Réglez pour recentrer le visage (ex. 50% 20% = plus haut).</small></label>
          <?php if (!empty($cur['photo'])): ?><p class="thumb"><img src="../<?php echo e($cur['photo']); ?>" alt=""></p><?php endif; ?>
          <label>Photo <?php echo $cur ? '(laisser vide pour conserver)' : ''; ?><input type="file" name="photo" accept="image/*"></label>
          <div class="actions"><button type="submit">Enregistrer</button><?php if ($cur): ?> <a class="btn-sec" href="index.php?p=professeurs">Annuler</a><?php endif; ?></div>
        </form>
      </section>
      <section class="panel">
        <h2>Professeurs (<?php echo count($items); ?>)</h2>
        <ul class="list">
          <?php foreach ($items as $i => $it): ?>
          <li>
            <div class="li-main"><?php if (!empty($it['photo'])): ?><img class="mini" src="../<?php echo e($it['photo']); ?>" alt=""><?php endif; ?><span><b><?php echo e($it['name'] ?? ''); ?></b><small><?php echo e($it['role'] ?? ''); ?></small></span></div>
            <div class="li-act"><?php echo $reorder('professeurs', $i, count($items)); ?>
              <a class="btn-sec" href="index.php?p=professeurs&edit=<?php echo $i; ?>">Modifier</a>
              <form method="POST" onsubmit="return confirm('Supprimer ce professeur ?');"><?php echo csrf_field(); ?><input type="hidden" name="do" value="delete"><input type="hidden" name="idx" value="<?php echo $i; ?>"><button class="danger">Supprimer</button></form>
            </div>
          </li>
          <?php endforeach; ?>
        </ul>
      </section>
    </div>
    <?php
    admin_footer();
    exit;
}

if ($p === 'techniques') {
    $items = load_json('techniques.json', []);
    $edit  = isset($_GET['edit']) ? (int) $_GET['edit'] : null;
    $cur   = ($edit !== null && isset($items[$edit])) ? $items[$edit] : null;
    admin_header('Répertoire du dojo', 'techniques');
    ?>
    <p class="muted" style="max-width:720px">Ajoutez une image et/ou un lien vidéo à chaque technique. <b>Tant qu'aucune technique n'a d'image ni de vidéo, la section « Le répertoire du dojo » reste masquée sur le site public.</b></p>
    <div class="cols">
      <section class="panel">
        <h2><?php echo $cur ? 'Modifier la technique' : 'Ajouter une technique'; ?></h2>
        <form method="POST" enctype="multipart/form-data">
          <?php echo csrf_field(); ?><input type="hidden" name="do" value="save"><input type="hidden" name="idx" value="<?php echo $edit !== null ? (int) $edit : ''; ?>">
          <label>Technique <span class="req">*</span><input name="t" required value="<?php echo e($cur['t'] ?? ''); ?>" placeholder="Ex. Shihonage"></label>
          <label>Attaque / précision<input name="d" value="<?php echo e($cur['d'] ?? ''); ?>" placeholder="Ex. sur shomenuchi"></label>
          <label>Lien vidéo (optionnel)<input name="url" value="<?php echo e($cur['url'] ?? ''); ?>" placeholder="https://youtube.com/..."></label>
          <?php if (!empty($cur['img'])): ?><p class="thumb"><img src="../<?php echo e($cur['img']); ?>" alt=""></p>
          <label class="check"><input type="checkbox" name="remove_image" value="1"> Retirer l'image</label><?php endif; ?>
          <label>Image (optionnel)<input type="file" name="img" accept="image/*"></label>
          <div class="actions"><button type="submit">Enregistrer</button><?php if ($cur): ?> <a class="btn-sec" href="index.php?p=techniques">Annuler</a><?php endif; ?></div>
        </form>
      </section>
      <section class="panel">
        <h2>Techniques (<?php echo count($items); ?>)</h2>
        <ul class="list">
          <?php foreach ($items as $i => $it): ?>
          <li>
            <div class="li-main"><?php if (!empty($it['img'])): ?><img class="mini" src="../<?php echo e($it['img']); ?>" alt=""><?php endif; ?><span><b><?php echo e($it['t'] ?? ''); ?></b><small><?php echo e($it['d'] ?? ''); echo !empty($it['url']) ? ' · vidéo 🎬' : ''; ?></small></span></div>
            <div class="li-act"><?php echo $reorder('techniques', $i, count($items)); ?>
              <a class="btn-sec" href="index.php?p=techniques&edit=<?php echo $i; ?>">Modifier</a>
              <form method="POST" onsubmit="return confirm('Supprimer cette technique ?');"><?php echo csrf_field(); ?><input type="hidden" name="do" value="delete"><input type="hidden" name="idx" value="<?php echo $i; ?>"><button class="danger">Supprimer</button></form>
            </div>
          </li>
          <?php endforeach; ?>
        </ul>
      </section>
    </div>
    <?php
    admin_footer();
    exit;
}

if ($p === 'infos') {
    $infos = load_json('infos.json', []);
    admin_header('Infos pratiques', 'infos');
    ?>
    <p class="hint">Une ligne par entrée, au format <code>Libellé | Valeur</code>.</p>
    <form method="POST" class="panel">
      <?php echo csrf_field(); ?>
      <h2>Horaires</h2>
      <textarea name="horaires" rows="5"><?php echo e(kv_to_text($infos['horaires'] ?? [])); ?></textarea>
      <label>Note sous les horaires<input name="horaires_note" value="<?php echo e($infos['horaires_note'] ?? ''); ?>"></label>
      <h2>Tarifs</h2>
      <textarea name="tarifs" rows="4"><?php echo e(kv_to_text($infos['tarifs'] ?? [])); ?></textarea>
      <label>Note sous les tarifs<input name="tarifs_note" value="<?php echo e($infos['tarifs_note'] ?? ''); ?>"></label>
      <h2>Inscription</h2>
      <textarea name="inscription" rows="4"><?php echo e(kv_to_text($infos['inscription'] ?? [])); ?></textarea>
      <label>Note sous l'inscription<input name="inscription_note" value="<?php echo e($infos['inscription_note'] ?? ''); ?>"></label>
      <div class="actions"><button type="submit">Enregistrer</button></div>
    </form>
    <?php
    admin_footer();
    exit;
}

/* ---------------- MESSAGES ---------------- */
if ($p === 'messages') {
    $msgs = load_json('messages.json', []);
    admin_header('Messages', 'messages');
    ?>
    <p class="hint">Les demandes reçues via les formulaires du site (<?php echo count($msgs); ?>).</p>
    <?php if ($msgs): ?>
    <form method="POST" onsubmit="return confirm('Vider toute la boîte de réception ?');" style="margin-bottom:16px"><?php echo csrf_field(); ?><input type="hidden" name="do" value="clear"><button class="danger">Tout vider</button></form>
    <?php else: ?><p class="muted">Aucun message pour le moment.</p><?php endif; ?>
    <div class="msgs">
      <?php foreach ($msgs as $m): ?>
      <article class="msg <?php echo empty($m['read']) ? 'unread' : ''; ?>">
        <div class="msg-top">
          <span class="tag <?php echo ($m['type'] ?? '') === 'essai' ? 'essai' : ''; ?>"><?php echo ($m['type'] ?? '') === 'essai' ? "Cours d'essai" : 'Contact'; ?></span>
          <b><?php echo e($m['name'] ?? ''); ?></b>
          <a href="mailto:<?php echo e($m['email'] ?? ''); ?>"><?php echo e($m['email'] ?? ''); ?></a>
          <?php if (!empty($m['phone'])): ?><span><?php echo e($m['phone']); ?></span><?php endif; ?>
          <time><?php echo e(date('d/m/Y H:i', strtotime($m['ts'] ?? 'now'))); ?></time>
        </div>
        <?php if (!empty($m['age']) || !empty($m['wanted'])): ?><p class="msg-meta"><?php if (!empty($m['age'])) echo 'Âge : ' . e($m['age']) . ' · '; if (!empty($m['wanted'])) echo 'Souhaite venir : ' . e($m['wanted']); ?></p><?php endif; ?>
        <?php if (!empty($m['message'])): ?><p class="msg-body"><?php echo nl2br(e($m['message'])); ?></p><?php endif; ?>
        <div class="li-act">
          <form method="POST"><?php echo csrf_field(); ?><input type="hidden" name="id" value="<?php echo e($m['id'] ?? ''); ?>"><input type="hidden" name="do" value="<?php echo empty($m['read']) ? 'read' : 'unread'; ?>"><button class="btn-sec"><?php echo empty($m['read']) ? 'Marquer lu' : 'Marquer non lu'; ?></button></form>
          <form method="POST" onsubmit="return confirm('Supprimer ce message ?');"><?php echo csrf_field(); ?><input type="hidden" name="id" value="<?php echo e($m['id'] ?? ''); ?>"><input type="hidden" name="do" value="delete"><button class="danger">Supprimer</button></form>
        </div>
      </article>
      <?php endforeach; ?>
    </div>
    <?php admin_footer(); exit;
}

/* ---------------- STAGES ---------------- */
if ($p === 'stages') {
    $items = load_json('stages.json', []);
    $edit  = isset($_GET['edit']) ? (int) $_GET['edit'] : null;
    $cur   = ($edit !== null && isset($items[$edit])) ? $items[$edit] : null;
    admin_header('Stages', 'stages');
    ?>
    <p class="hint">Agenda des stages. Les stages passés sont masqués automatiquement du site.</p>
    <div class="cols">
      <section class="panel">
        <h2><?php echo $cur ? 'Modifier le stage' : 'Ajouter un stage'; ?></h2>
        <form method="POST" enctype="multipart/form-data">
          <?php echo csrf_field(); ?><input type="hidden" name="do" value="save"><input type="hidden" name="idx" value="<?php echo $edit !== null ? (int) $edit : ''; ?>">
          <label>Date <span class="req">*</span><input type="date" name="date" required value="<?php echo e($cur['date'] ?? ''); ?>"></label>
          <label>Titre <span class="req">*</span><input name="title" required value="<?php echo e($cur['title'] ?? ''); ?>"></label>
          <label>Enseignant<input name="teacher" value="<?php echo e($cur['teacher'] ?? ''); ?>"></label>
          <label>Grade<input name="grade" value="<?php echo e($cur['grade'] ?? ''); ?>"></label>
          <label>Lieu<input name="place" value="<?php echo e($cur['place'] ?? ''); ?>"></label>
          <label>Lien (facultatif)<input name="link" value="<?php echo e($cur['link'] ?? ''); ?>" placeholder="https://..."></label>
          <?php if (!empty($cur['image'])): ?><p class="thumb"><img src="../<?php echo e($cur['image']); ?>" alt=""></p><?php endif; ?>
          <label>Affiche / image<input type="file" name="image" accept="image/*"></label>
          <div class="actions"><button type="submit">Enregistrer</button><?php if ($cur): ?> <a class="btn-sec" href="index.php?p=stages">Annuler</a><?php endif; ?></div>
        </form>
      </section>
      <section class="panel">
        <h2>Stages (<?php echo count($items); ?>)</h2>
        <?php if (!$items): ?><p class="muted">Aucun stage enregistré.</p><?php endif; ?>
        <ul class="list">
          <?php foreach ($items as $i => $it): $past = ($it['date'] ?? '') && $it['date'] < date('Y-m-d'); ?>
          <li>
            <div class="li-main"><span><b><?php echo e($it['title'] ?? ''); ?></b><small><?php echo e($it['date'] ?? ''); echo $past ? ' · passé' : ''; ?></small></span></div>
            <div class="li-act">
              <a class="btn-sec" href="index.php?p=stages&edit=<?php echo $i; ?>">Modifier</a>
              <form method="POST" onsubmit="return confirm('Supprimer ce stage ?');"><?php echo csrf_field(); ?><input type="hidden" name="do" value="delete"><input type="hidden" name="idx" value="<?php echo $i; ?>"><button class="danger">Supprimer</button></form>
            </div>
          </li>
          <?php endforeach; ?>
        </ul>
      </section>
    </div>
    <?php admin_footer(); exit;
}

/* ---------------- TEXTES ---------------- */
if ($p === 'textes') {
    $aik = load_json('aikido.json', []);
    $his = load_json('histoire.json', []);
    $faq = load_json('faq.json', []);
    $mem = load_json('memoriam.json', []);
    $tech = load_json('techniques.json', []);
    $arm = load_json('armes.json', []);
    $lib = load_json('libelles.json', []);
    $techRows = $tech; for ($k = 0; $k < 2; $k++) $techRows[] = ['t' => '', 'd' => ''];
    $beltRows = $arm['belts'] ?? []; $beltRows[] = ['label' => '', 'sub' => '', 'bar' => '#e7dcc6'];
    $wRows = $arm['armes'] ?? []; $wRows[] = ['kanji' => '', 'nom' => '', 'desc' => ''];
    $vals = $aik['values'] ?? [];
    while (count($vals) < 4) $vals[] = ['title' => '', 'text' => ''];
    $hisRows = $his; for ($k = 0; $k < 2; $k++) $hisRows[] = ['year' => '', 'text' => ''];
    $faqRows = $faq; for ($k = 0; $k < 2; $k++) $faqRows[] = ['q' => '', 'a' => ''];
    admin_header('Textes', 'textes');
    ?>
    <p class="hint">Modifiez les textes du site. Utilisez <code>**gras**</code> pour mettre un mot en évidence. Laissez une ligne vide pour supprimer une entrée.</p>

    <section class="panel">
      <h2>Présentation de l'aïkido</h2>
      <form method="POST"><?php echo csrf_field(); ?><input type="hidden" name="do" value="aikido">
        <label>Texte d'introduction<textarea name="lead" rows="4"><?php echo e($aik['lead'] ?? ''); ?></textarea></label>
        <?php foreach ($vals as $v): ?>
        <div class="row-form"><input name="v_title[]" placeholder="Titre" value="<?php echo e($v['title'] ?? ''); ?>"><input name="v_text[]" placeholder="Description" value="<?php echo e($v['text'] ?? ''); ?>"></div>
        <?php endforeach; ?>
        <div class="actions"><button type="submit">Enregistrer</button></div>
      </form>
    </section>

    <section class="panel">
      <h2>Frise historique</h2>
      <form method="POST"><?php echo csrf_field(); ?><input type="hidden" name="do" value="histoire">
        <?php foreach ($hisRows as $h): ?>
        <div class="row-form"><input name="year[]" placeholder="Année" style="max-width:110px" value="<?php echo e($h['year'] ?? ''); ?>"><input name="htext[]" placeholder="Événement (**gras** possible)" value="<?php echo e($h['text'] ?? ''); ?>"></div>
        <?php endforeach; ?>
        <div class="actions"><button type="submit">Enregistrer</button></div>
      </form>
    </section>

    <section class="panel">
      <h2>Questions fréquentes</h2>
      <form method="POST"><?php echo csrf_field(); ?><input type="hidden" name="do" value="faq">
        <?php foreach ($faqRows as $f): ?>
        <label>Question<input name="q[]" value="<?php echo e($f['q'] ?? ''); ?>"></label>
        <label>Réponse<textarea name="a[]" rows="2"><?php echo e($f['a'] ?? ''); ?></textarea></label>
        <hr>
        <?php endforeach; ?>
        <div class="actions"><button type="submit">Enregistrer</button></div>
      </form>
    </section>

    <section class="panel">
      <h2>In Memoriam (fondateurs)</h2>
      <form method="POST" enctype="multipart/form-data"><?php echo csrf_field(); ?><input type="hidden" name="do" value="memoriam">
        <?php $mfs = array_values($mem['founders'] ?? []); for ($k = 0; $k < 2; $k++): $cf = $mfs[$k] ?? []; ?>
        <fieldset style="border:1px solid var(--line,#ddd);border-radius:8px;padding:12px;margin-bottom:14px">
          <legend>Fondateur <?php echo $k + 1; ?></legend>
          <label>Nom<input name="mf<?php echo $k; ?>_name" value="<?php echo e($cf['name'] ?? ''); ?>"></label>
          <label>Sous-titre<input name="mf<?php echo $k; ?>_sub" value="<?php echo e($cf['subtitle'] ?? ''); ?>"></label>
          <?php if (!empty($cf['photo'])): ?><p class="thumb"><img src="../<?php echo e($cf['photo']); ?>" alt=""></p><?php endif; ?>
          <label>Photo (laisser vide pour conserver)<input type="file" name="mf<?php echo $k; ?>_photo" accept="image/*"></label>
        </fieldset>
        <?php endfor; ?>
        <label>Texte d'hommage<textarea name="f_text" rows="3"><?php echo e($mem['founder_text'] ?? ''); ?></textarea></label>
        <div class="actions"><button type="submit">Enregistrer</button></div>
      </form>
    </section>

    <section class="panel">
      <h2>Grades &amp; armes</h2>
      <form method="POST"><?php echo csrf_field(); ?><input type="hidden" name="do" value="armes">
        <label>Texte d'introduction (grades)<textarea name="grades_intro" rows="2"><?php echo e($arm['grades_intro'] ?? ''); ?></textarea></label>
        <p class="muted" style="margin:6px 0">Progression (libellé · sous-titre · couleur) :</p>
        <?php foreach ($beltRows as $b): ?>
        <div class="row-form"><input name="b_label[]" placeholder="Libellé" value="<?php echo e($b['label'] ?? ''); ?>"><input name="b_sub[]" placeholder="Sous-titre" value="<?php echo e($b['sub'] ?? ''); ?>"><input name="b_bar[]" placeholder="Couleur (#e7dcc6)" value="<?php echo e($b['bar'] ?? ''); ?>" style="max-width:150px"></div>
        <?php endforeach; ?>
        <label style="margin-top:14px">Texte d'introduction (armes)<textarea name="armes_intro" rows="2"><?php echo e($arm['armes_intro'] ?? ''); ?></textarea></label>
        <p class="muted" style="margin:6px 0">Armes (kanji · nom · description) :</p>
        <?php foreach ($wRows as $w): ?>
        <label>Nom<input name="w_nom[]" value="<?php echo e($w['nom'] ?? ''); ?>"></label>
        <div class="row-form"><input name="w_kanji[]" placeholder="Kanji" value="<?php echo e($w['kanji'] ?? ''); ?>" style="max-width:110px"><input name="w_desc[]" placeholder="Description" value="<?php echo e($w['desc'] ?? ''); ?>"></div>
        <hr>
        <?php endforeach; ?>
        <div class="actions"><button type="submit">Enregistrer</button></div>
      </form>
    </section>

    <section class="panel">
      <h2>Libellés &amp; accroche</h2>
      <form method="POST"><?php echo csrf_field(); ?><input type="hidden" name="do" value="libelles">
        <label>Accroche (au-dessus du titre)<input name="hero_eyebrow" value="<?php echo e($lib['hero_eyebrow'] ?? ''); ?>"></label>
        <label>Titre principal (utilisez <code>&lt;br&gt;</code> pour un retour à la ligne)<input name="hero_title" value="<?php echo e($lib['hero_title'] ?? ''); ?>"></label>
        <label>Texte d'introduction<textarea name="hero_subtitle" rows="3"><?php echo e($lib['hero_subtitle'] ?? ''); ?></textarea></label>
        <label>Directeur technique (sous les professeurs)<textarea name="director" rows="2"><?php echo e($lib['director'] ?? ''); ?></textarea></label>
        <label>Texte du pied de page<textarea name="footer_tagline" rows="2"><?php echo e($lib['footer_tagline'] ?? ''); ?></textarea></label>
        <div class="actions"><button type="submit">Enregistrer</button></div>
      </form>
    </section>
    <?php admin_footer(); exit;
}

/* ---------------- GLOSSAIRE ---------------- */
if ($p === 'glossaire') {
    $secs = load_json('glossaire.json', []);
    admin_header('Glossaire', 'glossaire');
    ?>
    <p class="hint">Un terme par ligne, au format <code>Terme | prononciation | définition</code>. La prononciation peut rester vide (<code>Terme | | définition</code>).</p>
    <?php foreach ($secs as $si => $sec): ?>
    <section class="panel">
      <h2><?php echo e($sec['title'] ?? ''); ?> <small class="muted">(<?php echo count($sec['items'] ?? []); ?> termes)</small></h2>
      <form method="POST"><?php echo csrf_field(); ?><input type="hidden" name="do" value="save_section"><input type="hidden" name="sec" value="<?php echo $si; ?>">
        <textarea name="lines" rows="12"><?php
            foreach (($sec['items'] ?? []) as $it) echo e(($it['t'] ?? '') . ' | ' . ($it['p'] ?? '') . ' | ' . ($it['d'] ?? '')) . "\n";
        ?></textarea>
        <div class="actions"><button type="submit">Enregistrer cette section</button></div>
      </form>
    </section>
    <?php endforeach; ?>
    <?php admin_footer(); exit;
}

/* ---------------- BIBLIOGRAPHIE ---------------- */
if ($p === 'bibliographie') {
    $items = load_json('bibliographie.json', []);
    $edit  = isset($_GET['edit']) ? (int) $_GET['edit'] : null;
    $cur   = ($edit !== null && isset($items[$edit])) ? $items[$edit] : null;
    $catLabels = ['fr' => 'Français', 'en' => 'Anglais', 'autres' => 'Autres'];
    admin_header('Bibliographie', 'bibliographie');
    ?>
    <div class="cols">
      <section class="panel">
        <h2><?php echo $cur ? "Modifier l'ouvrage" : 'Ajouter un ouvrage'; ?></h2>
        <form method="POST" enctype="multipart/form-data">
          <?php echo csrf_field(); ?><input type="hidden" name="do" value="save"><input type="hidden" name="idx" value="<?php echo $edit !== null ? (int) $edit : ''; ?>">
          <label>Titre <span class="req">*</span><input name="t" required value="<?php echo e($cur['t'] ?? ''); ?>"></label>
          <label>Auteur<input name="a" value="<?php echo e($cur['a'] ?? ''); ?>"></label>
          <label>Éditeur<input name="e" value="<?php echo e($cur['e'] ?? ''); ?>"></label>
          <label>Catégorie
            <select name="cat">
              <?php foreach ($catLabels as $k => $v): ?><option value="<?php echo $k; ?>" <?php echo (($cur['cat'] ?? 'fr') === $k) ? 'selected' : ''; ?>><?php echo e($v); ?></option><?php endforeach; ?>
            </select>
          </label>
          <?php if (!empty($cur['img'])): $src = strpos($cur['img'], '/') !== false ? $cur['img'] : 'images/bibliographie/' . $cur['img']; ?><p class="thumb"><img src="../<?php echo e($src); ?>" alt=""></p><?php endif; ?>
          <label>Couverture (facultative)<input type="file" name="cover" accept="image/*"></label>
          <div class="actions"><button type="submit">Enregistrer</button><?php if ($cur): ?> <a class="btn-sec" href="index.php?p=bibliographie">Annuler</a><?php endif; ?></div>
        </form>
      </section>
      <section class="panel">
        <h2>Ouvrages (<?php echo count($items); ?>)</h2>
        <ul class="list">
          <?php foreach ($items as $i => $it): ?>
          <li>
            <div class="li-main"><span><b><?php echo e($it['t'] ?? ''); ?></b><small><?php echo e($it['a'] ?? ''); ?> · <?php echo e($catLabels[$it['cat'] ?? 'fr'] ?? ''); ?></small></span></div>
            <div class="li-act">
              <a class="btn-sec" href="index.php?p=bibliographie&edit=<?php echo $i; ?>">Modifier</a>
              <form method="POST" onsubmit="return confirm('Supprimer cet ouvrage ?');"><?php echo csrf_field(); ?><input type="hidden" name="do" value="delete"><input type="hidden" name="idx" value="<?php echo $i; ?>"><button class="danger">Supprimer</button></form>
            </div>
          </li>
          <?php endforeach; ?>
        </ul>
      </section>
    </div>
    <?php admin_footer(); exit;
}

/* ---------------- COORDONNÉES ---------------- */
if ($p === 'coordonnees') {
    $c = load_json('contact.json', []);
    $liens = load_json('liens.json', []);
    admin_header('Coordonnées', 'coordonnees');
    ?>
    <div class="cols">
      <section class="panel">
        <h2>Coordonnées de contact</h2>
        <form method="POST"><?php echo csrf_field(); ?><input type="hidden" name="do" value="contact">
          <label>Téléphone principal<input name="phone1" value="<?php echo e($c['phone1'] ?? ''); ?>"></label>
          <label>Téléphone secondaire<input name="phone2" value="<?php echo e($c['phone2'] ?? ''); ?>"></label>
          <label>E-mail<input name="email" type="email" value="<?php echo e($c['email'] ?? ''); ?>"></label>
          <label>Adresse<input name="address" value="<?php echo e($c['address'] ?? ''); ?>"></label>
          <label>Lien itinéraire (Google Maps)<input name="maps" value="<?php echo e($c['maps'] ?? ''); ?>"></label>
          <label>Lien WhatsApp<input name="whatsapp" value="<?php echo e($c['whatsapp'] ?? ''); ?>"></label>
          <label>Page Facebook<input name="facebook" value="<?php echo e($c['facebook'] ?? ''); ?>"></label>
          <div class="actions"><button type="submit">Enregistrer</button></div>
        </form>
      </section>
      <section class="panel">
        <h2>Liens utiles (pied de page)</h2>
        <p class="muted" style="margin-top:-8px">Un lien par ligne, au format <code>Libellé | adresse</code>.</p>
        <form method="POST"><?php echo csrf_field(); ?><input type="hidden" name="do" value="liens">
          <textarea name="lines" rows="8"><?php foreach ($liens as $l) echo e(($l['label'] ?? '') . ' | ' . ($l['url'] ?? '')) . "\n"; ?></textarea>
          <div class="actions"><button type="submit">Enregistrer</button></div>
        </form>
      </section>
    </div>
    <?php admin_footer(); exit;
}

/* ---------------- RÉGLAGES ---------------- */
if ($p === 'reglages') {
    // Export / sauvegarde (téléchargement d'une archive)
    if (isset($_GET['dl']) && class_exists('ZipArchive')) {
        $zipPath = tempnam(sys_get_temp_dir(), 'mdj');
        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::OVERWRITE) === true) {
            foreach (glob(DATA_DIR . '/*.json') as $f) $zip->addFile($f, 'data/' . basename($f));
            foreach (glob(IMG_DIR . '/*') as $f) if (is_file($f)) $zip->addFile($f, 'images/' . basename($f));
            $zip->close();
            header('Content-Type: application/zip');
            header('Content-Disposition: attachment; filename="sauvegarde-musubi-' . date('Ymd') . '.zip"');
            header('Content-Length: ' . filesize($zipPath));
            readfile($zipPath);
            @unlink($zipPath);
            exit;
        }
    }
    $set = load_json('settings.json', []);
    admin_header('Réglages', 'reglages');
    ?>
    <div class="cols">
      <section class="panel">
        <h2>Bannière d'alerte</h2>
        <p class="muted" style="margin-top:-8px">Affiche un bandeau en haut du site (ex. « Cours annulé ce soir »).</p>
        <form method="POST"><?php echo csrf_field(); ?><input type="hidden" name="do" value="settings">
          <label class="inline"><input type="checkbox" name="alert_enabled" value="1" <?php echo !empty($set['alert_enabled']) ? 'checked' : ''; ?>> Activer la bannière</label>
          <label>Message<input name="alert_text" value="<?php echo e($set['alert_text'] ?? ''); ?>"></label>
          <label>Adresse de la page Facebook<input name="facebook_url" value="<?php echo e($set['facebook_url'] ?? ''); ?>"></label>
          <div class="actions"><button type="submit">Enregistrer</button></div>
        </form>
      </section>
      <section class="panel">
        <h2>Mot de passe</h2>
        <form method="POST"><?php echo csrf_field(); ?><input type="hidden" name="do" value="password">
          <label>Mot de passe actuel<input type="password" name="current" required></label>
          <label>Nouveau mot de passe<input type="password" name="new" required></label>
          <label>Confirmer<input type="password" name="confirm" required></label>
          <div class="actions"><button type="submit">Changer</button></div>
        </form>
        <h2 style="margin-top:26px">Sauvegarde</h2>
        <p class="muted" style="margin-top:-8px">Téléchargez une archive de tout le contenu (textes et photos).</p>
        <a class="btn-sec" href="index.php?p=reglages&dl=1">Télécharger la sauvegarde</a>
      </section>
    </div>
    <?php admin_footer(); exit;
}

/* ---------------- TABLEAU DE BORD ---------------- */
$nbActus = count(load_json('actualites.json', []));
$nbGal   = count(load_json('galerie.json', []));
$nbProf  = count(load_json('professeurs.json', []));
$nbStage = count(load_json('stages.json', []));
$msgs    = load_json('messages.json', []);
$nbMsg   = count($msgs);
$nbUnread = 0; foreach ($msgs as $m) if (empty($m['read'])) $nbUnread++;
admin_header('Tableau de bord');
?>
<p class="hint">Bienvenue. Choisissez une rubrique à mettre à jour. Les changements sont visibles immédiatement sur le site.</p>
<div class="tiles">
  <a class="tile" href="index.php?p=messages"><b><?php echo $nbMsg; ?><?php echo $nbUnread ? '<i class="tbadge">' . $nbUnread . '</i>' : ''; ?></b><span>Messages</span></a>
  <a class="tile" href="index.php?p=actualites"><b><?php echo $nbActus; ?></b><span>Actualités</span></a>
  <a class="tile" href="index.php?p=stages"><b><?php echo $nbStage; ?></b><span>Stages</span></a>
  <a class="tile" href="index.php?p=galerie"><b><?php echo $nbGal; ?></b><span>Photos de galerie</span></a>
  <a class="tile" href="index.php?p=professeurs"><b><?php echo $nbProf; ?></b><span>Professeurs</span></a>
  <a class="tile" href="index.php?p=techniques"><b>技</b><span>Répertoire</span></a>
  <a class="tile" href="index.php?p=textes"><b>✎</b><span>Textes</span></a>
  <a class="tile" href="index.php?p=glossaire"><b>語</b><span>Glossaire</span></a>
  <a class="tile" href="index.php?p=bibliographie"><b>📚</b><span>Bibliographie</span></a>
  <a class="tile" href="index.php?p=coordonnees"><b>✆</b><span>Coordonnées</span></a>
  <a class="tile" href="index.php?p=infos"><b>⚙</b><span>Infos pratiques</span></a>
  <a class="tile" href="index.php?p=reglages"><b>⚙</b><span>Réglages</span></a>
</div>
<?php
admin_footer();
