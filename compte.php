<?php
	include ("kernel/main.php");
	include ("_affiche_vignette.php");

	# Pas loggué...
	if (!$_COMPTE) $Navig->fix_page_demande_connect_compte(true);


	//
	// Variables...
	//
	# Historique...
	$titre_historique = "Mon compte";
	//////////////////////////////////////////////////


	//
	// Déconnexion...
	//
	if (isset($_GET['logout']))	{
		$Client->deconnect();	// Déconnecte le client ($_COMPTE=false)...
		$Caddie->deconnect();		
		$Navig->autoreload();	// Pour recalculer $id_client du Caddie...
	}
	//////////////////////////////////////////////////


	//
	// Récupérer nombre de commandes...
	//
	sql_mysql_query ("	SELECT	id
						FROM	commandes
						WHERE		id_client	= '$_COMPTE'
								AND statut		!= 200
								AND NOT (mode_paiement=1 AND statut=1)
					", 'commandes');
	//////////////////////////////////////////////////
?>
<div class="catalogue clearfix">
<div class="head">Mon compte</div>
<div class="padding">
<p><a href="compte_modif.php">&gt;&gt; Informations personnelles</a></p>
<?php if ($nb_commandes) { ?>
<p><a href="compte_commandes.php">&gt;&gt; Consulter mes commandes (<?php echo $nb_commandes; ?>)</a></p>
<?php } else { ?>
<p><span style="color:#BBBBBB;">&gt;&gt; Consulter mes commandes (0)</span></p>
<?php } ?>
<p><a href="ma_liste.php">&gt;&gt; Ma Liste</a></p>
<p><a href="compte.php?logout=1">&gt;&gt; D&eacute;connexion</a></p>
</div>
</div>
<?php modele_page("_modele_page.php", "Commander"); ?>