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
            [$up, $upErr] = upload_image('image', 'actu');
            if ($upErr) { flash($upErr, 'err'); redirect('p=actualites'); }
            if ($up) { $item['image'] = $up; $item['imageWebp'] = ''; }
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
            [$up, $upErr] = upload_image('image', 'galerie');
            if ($upErr) { flash($upErr, 'err'); redirect('p=galerie'); }
            if (!$up) { flash('Veuillez choisir une image.', 'err'); redirect('p=galerie'); }
            $items[] = ['img' => $up, 'webp' => '', 'alt' => trim($_POST['alt'] ?? '')];
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
            [$up, $upErr] = upload_image('photo', 'prof');
            if ($upErr) { flash($upErr, 'err'); redirect('p=professeurs'); }
            if ($up) { $item['photo'] = $up; $item['webp'] = ''; }
            if ($item['name'] === '') { flash('Le nom est obligatoire.', 'err'); redirect('p=professeurs'); }
            if ($idx !== null && isset($items[$idx])) $items[$idx] = $item; else $items[] = $item;
            save_json('professeurs.json', array_values($items));
            flash('Professeur enregistré.');
        }
        redirect('p=professeurs');
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

/* ---------------- TABLEAU DE BORD ---------------- */
$nbActus = count(load_json('actualites.json', []));
$nbGal   = count(load_json('galerie.json', []));
$nbProf  = count(load_json('professeurs.json', []));
admin_header('Tableau de bord');
?>
<p class="hint">Bienvenue. Choisissez une rubrique à mettre à jour. Les changements sont visibles immédiatement sur le site.</p>
<div class="tiles">
  <a class="tile" href="index.php?p=actualites"><b><?php echo $nbActus; ?></b><span>Actualités</span></a>
  <a class="tile" href="index.php?p=galerie"><b><?php echo $nbGal; ?></b><span>Photos de galerie</span></a>
  <a class="tile" href="index.php?p=professeurs"><b><?php echo $nbProf; ?></b><span>Professeurs</span></a>
  <a class="tile" href="index.php?p=infos"><b>⚙</b><span>Infos pratiques</span></a>
</div>
<?php
admin_footer();
