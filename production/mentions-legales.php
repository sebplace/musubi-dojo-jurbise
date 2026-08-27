<?php
require __DIR__ . '/inc/functions.php';
ob_start();
?>
<p>Conformément à la législation en vigueur, les informations suivantes sont portées à la connaissance des visiteurs du site.</p>

<h2>Éditeur du site</h2>
<p>
  <?php echo e(config('legal_editor', "École d'Aïkido Musubi Dojo")); ?><br>
  <?php echo e(config('legal_address', "Route d'Ath 25-35, 7050 Jurbise, Belgique")); ?><br>
  Courriel : <a href="mailto:<?php echo e(config('legal_email', 'benoit.toulotte@aikido.be')); ?>"><?php echo e(config('legal_email', 'benoit.toulotte@aikido.be')); ?></a>
</p>
<p>L'École d'Aïkido Musubi Dojo est affiliée à l'Association Francophone d'Aïkido (<a href="https://www.aikido.be" target="_blank" rel="noopener">www.aikido.be</a>).</p>

<h2>Hébergement</h2>
<p><?php echo e(config('legal_host', 'o2switch, 222-224 Boulevard Gustave Flaubert, 63000 Clermont-Ferrand, France')); ?></p>

<h2>Propriété intellectuelle</h2>
<p>L'ensemble des contenus de ce site (textes, images, logo, mise en page) est la propriété de l'École d'Aïkido Musubi Dojo, sauf mention contraire. Toute reproduction sans autorisation préalable est interdite.</p>

<h2>Responsabilité</h2>
<p>Les informations diffusées sur ce site sont fournies à titre indicatif. L'éditeur s'efforce de les tenir à jour mais ne saurait être tenu responsable d'éventuelles erreurs ou omissions.</p>

<h2>Données personnelles</h2>
<p>La gestion des données transmises via les formulaires est décrite dans notre <a href="confidentialite.php">politique de confidentialité</a>.</p>
<?php
legal_page('Mentions légales', ob_get_clean());
