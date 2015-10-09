<?php
	include ("kernel/main.php");
	include ("_affiche_vignette.php");

	# Pas loggué...
	if (!$_COMPTE) $Navig->fix_page_demande_connect_compte(true);

	# Caddie vide...
	if (!$Caddie->infos['nb']) $Navig->redirect("caddie.php");

	//
	// Variables...
	//
	session_var("mode_paiement_fin", 0, 'I');
	if (!$mode_paiement_fin) $Navig->redirect("caddie.php");

	session_var("id_commande_fin", 0, 'I');

	# Vider Caddie paiement par chèque...
	if ($mode_paiement_fin==2) $Caddie->vider();

	# Historique...
	$titre_historique = "Commander";

	# CB...
	if ($mode_paiement_fin==$_CB) extract($_SESSION['pay']);
	//////////////////////////////////////////////////
?>
<div class="catalogue clearfix">
  <div class="head">Confirmation de commande</div>
  <div class="padding">
<?php if ($mode_paiement_fin==$_CB) {	// CB... ?>
<h2 class="header2">&gt;&gt; Commande Par Carte Bancaire</h2>
<?php include ("kernel/pay/call_request.php"); ?>
<?php } ?>
<?php if ($mode_paiement_fin==$_CHEQUE) {	// CHEQUE... ?>
<h2 class="header2">&gt;&gt; Commande Par Ch&egrave;que</h2>
<p>Votre commande par ch&egrave;que a bien &eacute;t&eacute; enregistr&eacute;e.</p>
<p>Elle ne sera d&eacute;finitivement enregistr&eacute;e qu'&agrave; r&eacute;ception de votre ch&egrave;que d'un montant du total de votre commande, libell&eacute; &agrave; l'ordre de : <b>UNIVERS DISCOUNT</b></p>
<p>&agrave; l'adresse : <br>
  <?php echo nl2br($_PAIEMENT_CHEQUE_ADRESSE); ?></p>
<p><a href="javascript:facture('<?php echo $id_commande_fin; ?>');"><b>Cliquez-ici</b></a> pour imprimer votre facture.</p>
<p>Retrouvez l'ensemble de vos factures dans <a href="compte.php"><b>votre compte</b></a>.</p>
<?php } ?>
</div>
</div>
<?php
	modele_page("_modele_page.php", "Commander");

	# RAZ...
	$mode_paiement_fin	= 0;
	$id_commande_fin	= 0;
?>