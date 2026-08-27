<?php
require __DIR__ . '/inc/functions.php';
ob_start();
?>
<p>Cette politique explique quelles données personnelles sont collectées sur ce site, dans quel but, et quels sont vos droits, conformément au Règlement général sur la protection des données (RGPD).</p>

<h2>Responsable du traitement</h2>
<p>
  <?php echo e(config('legal_editor', "École d'Aïkido Musubi Dojo")); ?>,
  <?php echo e(config('legal_address', "Route d'Ath 25-35, 7050 Jurbise, Belgique")); ?>.<br>
  Contact : <a href="mailto:<?php echo e(config('legal_email', 'benoit.toulotte@aikido.be')); ?>"><?php echo e(config('legal_email', 'benoit.toulotte@aikido.be')); ?></a>
</p>

<h2>Données collectées</h2>
<p>Lorsque vous utilisez nos formulaires (contact ou demande de cours d'essai), nous collectons les informations que vous nous transmettez : nom, adresse e-mail, et selon le cas, téléphone, âge indicatif et message. Aucune autre donnée n'est collectée à votre insu.</p>

<h2>Finalité</h2>
<p>Ces informations servent uniquement à répondre à votre demande et à organiser votre venue au dojo. Elles ne sont ni vendues, ni cédées, ni utilisées à des fins publicitaires.</p>

<h2>Conservation</h2>
<p>Les messages reçus sont conservés le temps nécessaire au traitement de votre demande, puis supprimés. Vous pouvez demander leur suppression à tout moment.</p>

<h2>Cookies</h2>
<p>Le site public n'utilise pas de cookies de suivi publicitaire. Un cookie technique de session est uniquement utilisé dans l'espace d'administration réservé au club.</p>

<h2>Vos droits</h2>
<p>Vous disposez d'un droit d'accès, de rectification et de suppression de vos données. Pour l'exercer, écrivez-nous à l'adresse ci-dessus. Vous pouvez également introduire une réclamation auprès de l'Autorité de protection des données (Belgique).</p>
<?php
legal_page('Politique de confidentialité', ob_get_clean());
