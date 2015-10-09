<?php
	include ("kernel/main.php");
	include ("_affiche_vignette.php");

	# Pas loggué...
	if (!$_COMPTE) $Navig->fix_page_demande_connect_compte(true);

	# Caddie vide...
	if (!$Caddie->infos['nb']) $Navig->redirect("caddie.php");

	//
	// Varibles...
	//
	$PAIEMENT = false;

	post_var('mode_paiement',	0, 'I');
	post_var('accept_cgv',		0, 'I');

	# Livraison...
	session_var('livr_nom',					$Client->infos['nom'],			'*');
	session_var('livr_prenom',				$Client->infos['prenom'],		'*');
	session_var('livr_adresse',				$Client->infos['adresse'],		'*');
	session_var('livr_adresse2',			$Client->infos['adresse2'],		'*');
	session_var('livr_adresse3',			$Client->infos['adresse3'],		'*');
	session_var('livr_code_postal',			$Client->infos['code_postal'],	'*');
	session_var('livr_ville',				$Client->infos['ville'],		'*');
	session_var('livr_tel',					$Client->infos['tel'],			'*');
	session_var('livr_id_pays',				$Client->infos['id_pays'],		'I');
	session_var('livr_id_mode_transport',	1,								'I');

	# Nom du pays (pour enregistrement en dur...
	$livr_pays = sql_nom('fdp_pays', $livr_id_pays);

	# Historique...
	$titre_historique = "Commander";
	//////////////////////////////////////////////////
	
	# Gestion des FDP en fonction du pays...
	$Caddie->mode_transport($livr_id_mode_transport, $livr_id_pays);


	//
	// Tester case CGV...
	//
	if ($mode_paiement) {
		if (!$accept_cgv) $ERR = "CMD_PAS_CGV";
		else $PAIEMENT = true;
	}
	//////////////////////////////////////////////////


	//
	// Demande de paiement...
	//
	if ($PAIEMENT && array_key_exists($mode_paiement, $_MODE_PAIEMENT)) {
		extract($_POST);

		# Créer le code commande...
		$CODE_CMD = false;
		while (!$CODE_CMD) {
			$CODE_CMD = date("Ymd") . chr(mt_rand(65,90)) . chr(mt_rand(65,90)) . chr(mt_rand(65,90)) . chr(mt_rand(65,90));
			$query_code_commande = mysql_query ("SELECT id FROM commandes WHERE code_commande='$CODE_CMD'");
			if (mysql_num_rows($query_code_commande)) $CODE_CMD = false; // le code commande existe déjà dans la bdd...
		}

		#
		# INSERT commandes...
		#
		# Récupérer le nom du pays...
		$FDP = "";
		if ($Caddie->id_pays==_ID_FRANCE && $Caddie->id_mode_transport==1) {
			$FDP = "remise_fdp_HT	= '" . $Caddie->infos['fdp_HT']		. "',
					remise_fdp_TTC	= '" . $Caddie->infos['fdp_TTC']	. "',
					remise_fdp_TVA	= '" . $Caddie->infos['fdp_TVA']	. "',
					";
		} // Fin France...


		# Infos complémentaires...
		$SQL = "	INSERT	commandes
					SET		code_commande				= '$CODE_CMD',
							id_site						= '$_SITE_ID',
							id_client					= '$_COMPTE',
							client_societe				= '" . mysql_real_escape_string($Client->infos['societe'])			. "',
							client_civilite				= '" . mysql_real_escape_string($Client->infos['civilite'])			. "',
							client_nom					= '" . mysql_real_escape_string($Client->infos['nom'])				. "',
							client_prenom				= '" . mysql_real_escape_string($Client->infos['prenom'])			. "',
							client_adresse				= '" . mysql_real_escape_string($Client->infos['adresse'])			. "',
							client_adresse2				= '" . mysql_real_escape_string($Client->infos['adresse2'])			. "',
							client_adresse3				= '" . mysql_real_escape_string($Client->infos['adresse3'])			. "',
							client_code_postal			= '" . mysql_real_escape_string($Client->infos['code_postal'])		. "',
							client_ville				= '" . mysql_real_escape_string($Client->infos['ville'])			. "',
							client_pays					= '" . mysql_real_escape_string($Client->infos['pays_nom'])			. "',
							client_tel					= '" . mysql_real_escape_string($Client->infos['tel'])				. "',
							client_port					= '" . mysql_real_escape_string($Client->infos['portable'])			. "',
							client_email				= '" . mysql_real_escape_string($Client->infos['email'])			. "',
							livr_nom					= '" . mysql_real_escape_string(mystripslashes($livr_nom))			. "',
							livr_prenom					= '" . mysql_real_escape_string(mystripslashes($livr_prenom))		. "',
							livr_adresse				= '" . mysql_real_escape_string(mystripslashes($livr_adresse))		. "',
							livr_adresse2				= '" . mysql_real_escape_string(mystripslashes($livr_adresse2))		. "',
							livr_adresse3				= '" . mysql_real_escape_string(mystripslashes($livr_adresse3))		. "',
							livr_code_postal			= '" . mysql_real_escape_string(mystripslashes($livr_code_postal))	. "',
							livr_ville					= '" . mysql_real_escape_string(mystripslashes($livr_ville))		. "',
							livr_id_pays				= '$livr_id_pays',
							livr_pays					= '" . mysql_real_escape_string(mystripslashes($livr_pays))			. "',
							livr_tel					= '" . mysql_real_escape_string(mystripslashes($livr_tel))			. "',
							total_HT					= '" . $Caddie->infos['total_HT']					. "',
							total_TTC					= '" . $Caddie->infos['total_TTC']					. "',
							total_TVA					= '" . $Caddie->infos['total_TVA']					. "',
							fdp_HT						= '" . $Caddie->infos['fdp_HT']						. "',
							fdp_TTC						= '" . $Caddie->infos['fdp_TTC']					. "',
							fdp_TVA						= '" . $Caddie->infos['fdp_TVA']					. "',
							$FDP
							id_mode_transport			= '" . $Caddie->id_mode_transport					. "',
							code_promo					= '" . $Caddie->code_promo							. "',
							remise_code_promo			= '" . $Caddie->remise_code_promo					. "',
							remise_code_promo_HT		= '" . $Caddie->infos['remise_code_promo_HT']		. "',
							remise_code_promo_TTC		= '" . $Caddie->infos['remise_code_promo_TTC']		. "',
							remise_code_promo_TVA		= '" . $Caddie->infos['remise_code_promo_TVA']		. "',
							remise_carte_fidelite		= '" . ($Caddie->remise_carte_fidelite ? $_REMISE_CARTE_FIDELITE : 0) . "',
							remise_carte_fidelite_TTC	= '" . $Caddie->infos['remise_carte_fidelite_TTC']	. "',
							remise_carte_fidelite_HT	= '" . $Caddie->infos['remise_carte_fidelite_HT']	. "',
							montant_HT					= '" . $Caddie->infos['montant_HT']					. "',
							montant_TTC					= '" . $Caddie->infos['montant_TTC']				. "',
							montant_TVA					= '" . $Caddie->infos['montant_TVA']				. "',
							poids						= '" . $Caddie->infos['poids_total']				. "',
							nb_produits					= '" . $Caddie->infos['nb']							. "',
							mode_paiement				= '$mode_paiement',
							statut						= '1',
							date_commande				= '$_DATE_TIME',
							ip							= '" . $_SERVER["REMOTE_ADDR"] . "'
				";
// ##### DEV #####	remise_carte_fidelite_TVA	= '" . $Caddie->infos['remise_carte_fidelite_TVA']	. "',

		mysql_query ($SQL);

		$id_commande = mysql_insert_id();

		$_SESSION['id_commande_fin'] = $id_commande;

		# Détails...
		$LST = "";
		while ($row = $Caddie->lire()) {

			# Promos spéciales...
			switch ($row["promo_spe"]) {
				case "1_1"	:
					if ($row["promo_spe_quantite"]) {
						$row["quantite"]	+= $row["promo_spe_quantite"];
						$row["article"]		.= "\n--- Promotion : " . $tab_PROMO_SPE[$row["promo_spe"]];
					}
					break;
				case "2_1"	:
					if ($row["promo_spe_quantite"]) {
						$row["quantite"]	+= $row["promo_spe_quantite"];
						$row["article"]		.= "\n--- Promotion : " . $tab_PROMO_SPE[$row["promo_spe"]];
					}
					break;
				case "3_1"	:
					if ($row["promo_spe_quantite"]) {
						$row["quantite"]	+= $row["promo_spe_quantite"];
						$row["article"]		.= "\n--- Promotion : " . $tab_PROMO_SPE[$row["promo_spe"]];
					}
					break;
				case "2_50"	:
					if ($row["promo_spe_quantite"]) {
						$row["article"]	.= "\n--- Promotion : " . $tab_PROMO_SPE[$row["promo_spe"]];
						$row["montant_ligne_HT"]	+= $row["promo_spe_montant_HT"];	// valeur déjà négative donc '+'...
						$row["montant_ligne_TTC"]	+= $row["promo_spe_montant_TTC"];	// valeur déjà négative donc '+'...
						$row["montant_ligne_TVA"]	+= $row["promo_spe_montant_TVA"];	// valeur déjà négative donc '+'...
					}
					break;
			}

			# Requête...
			$SQL = "	INSERT	commandes_detail
						SET		id_commande				= '$id_commande',
								id_produit				= '" . $row["id_produit"]					. "',
								reference				= '" . $row["reference"]					. "',
								article					= '" . mysql_escape_string($row["article_complet"])	. "',
								quantite				= '" . $row["quantite"]						. "',
								PA_HT					= '" . $row["PA_HT"]						. "',
								PA_TTC					= '" . $row["PA_TTC"]						. "',
								prix_HT					= '" . $row["prix_HT"]						. "',
								prix_TTC				= '" . $row["prix_TTC"]						. "',
								prix_TVA				= '" . $row["prix_TVA"]						. "',
								taux_TVA				= '" . $row["taux_TVA"]						. "',
								montant_ligne_HT		= '" . $row["montant_ligne_HT"]				. "',
								montant_ligne_TTC		= '" . $row["montant_ligne_TTC"]			. "',
								montant_ligne_TVA		= '" . $row["montant_ligne_TVA"]			. "',
								poids_ligne				= '" . $row["poids_ligne"]					. "',
								promo_spe				= '" . $row["promo_spe"]					. "',
								promo_spe_quantite		= '" . $row["promo_spe_quantite"]			. "',
								promo_spe_prix_HT		= '" . $row["promo_spe_prix_HT"]			. "',
								promo_spe_prix_TTC		= '" . $row["promo_spe_prix_TTC"]			. "',
								promo_spe_TVA			= '" . $row["promo_spe_TVA"]				. "',
								promo_spe_montant_HT	= '" . $row["promo_spe_montant_HT"]			. "',
								promo_spe_montant_TTC	= '" . $row["promo_spe_montant_TTC"]		. "',
								promo_spe_montant_TVA	= '" . $row["promo_spe_montant_TVA"]		. "',
								promo_spe_poids_total	= '" . $row["promo_spe_poids_total"]		. "'
					";
			mysql_query ($SQL);
			$LST .= $row["quantite"] . " x " . $row["article"] . " [réf:" . $row["reference"] . "]\n";

/*
			# Commande spéciale...
			if ($row["promo_spe"]=="2_50") {
				if ($row["promo_spe"]==)	$article = "Promo : " . $tab_PROMO_SPE[$row["promo_spe"]];
				else							$article = $row["article"] . " - Promo : " . $tab_PROMO_SPE[$row["promo_spe"]];

				$SQL = "	INSERT	commandes_detail
							SET		id_commande				= '$id_commande',
									id_produit				= '" . $row["id_produit"]				. "',
									reference				= '" . $row["reference"]				. "',
									article					= '" . mysql_escape_string($article)	. "',
									quantite				= '" . $row["promo_spe_quantite"]		. "',
									prix_HT					= '" . $row["promo_spe_prix_HT"]		. "',
									prix_TTC				= '" . $row["promo_spe_prix_TTC"]		. "',
									prix_TVA				= '" . $row["promo_spe_TVA"]			. "',
									taux_TVA				= '" . $row["taux_TVA"]					. "',
									montant_ligne_HT		= '" . $row["promo_spe_montant_HT"]		. "',
									montant_ligne_TTC		= '" . $row["promo_spe_montant_TTC"]	. "',
									montant_ligne_TVA		= '" . $row["promo_spe_montant_TVA"]	. "',
									poids_ligne				= '" . $row["promo_spe_poids_total"]	. "'
				";
				mysql_query ($SQL);
			}
*/
		}	// fin while détail commande...

		# Cadeau...
		if ($Caddie->id_cadeau) {
			$SQL = "	INSERT	commandes_detail
						SET		id_commande				= '$id_commande',
								id_produit				= '" . $Caddie->cadeau->infos["id"]			. "',
								reference				= '" . $Caddie->cadeau->infos["reference"]	. "',
								article					= '" . mysql_escape_string("Cadeau : " . $Caddie->cadeau->infos["nom_complet"])	. "',
								quantite				= '1',
								PA_HT					= '" . $Caddie->cadeau->infos["PA_HT"]		. "',
								PA_TTC					= '" . $Caddie->cadeau->infos["PA_TTC"]		. "',
								prix_HT					= '0',
								prix_TTC				= '0',
								prix_TVA				= '0',
								taux_TVA				= '" . $Caddie->cadeau->infos["taux_TVA"]	. "',
								montant_ligne_HT		= '0',
								montant_ligne_TTC		= '0',
								montant_ligne_TVA		= '0',
								poids_ligne				= '" . $Caddie->cadeau->infos["poids"]		. "',
								cadeau					= '1'
					";
			mysql_query($SQL);
			$LST .= "1 x Cadeau : " . $Caddie->cadeau->infos["nom_complet"] . " [réf:" . $Caddie->cadeau->infos["reference"] . "]\n";
		}

		# Carte fidélité...
		if ($Caddie->achat_carte_fidelite) {
			$SQL = "	INSERT	commandes_detail
						SET		id_commande				= '$id_commande',
								id_produit				= '999999',
								reference				= 'cartefidelite',
								article					= 'Clé de fidélité',
								quantite				= '1',
								PA_HT					= '0',
								PA_TTC					= '0',
								prix_HT					= '" . $_PRIX_CARTE_FIDELITE/1.196	. "',
								prix_TTC				= '" . $_PRIX_CARTE_FIDELITE		. "',
								prix_TVA				= '" . ( $_PRIX_CARTE_FIDELITE - ($_PRIX_CARTE_FIDELITE/1.196) ) . "',
								taux_TVA				= '19.6',
								montant_ligne_HT		= '" . $_PRIX_CARTE_FIDELITE/1.196	. "',
								montant_ligne_TTC		= '" . $_PRIX_CARTE_FIDELITE		. "',
								montant_ligne_TVA		= '" . ( $_PRIX_CARTE_FIDELITE - ($_PRIX_CARTE_FIDELITE/1.196) ) . "',
								poids_ligne				= '0',
								cadeau					= '0'
					";
			mysql_query ($SQL);
			$LST .= "1 x Carte de fidélité\n";
		}



		# Montant CB...
		$MONTANT_CB = number_format($Caddie->infos['montant_TTC'], 2, '.', '');


		# Mail...
		# Commande...
		$query_cmd	= mysql_query ("SELECT * FROM commandes WHERE commandes.code_commande='$CODE_CMD'");
		$row_cmd	= mysql_fetch_assoc($query_cmd);
		foreach($row_cmd as $k=>$v) $GLOBALS['CMD_'.strtoupper($k)] = $v;

		# Client...
		$Client->extraire_var();
		$CLIENT_ADRESSE_LIVR = $Client->infos['adresse'] . " " . $Client->infos['adresse2'] . " " . $Client->infos['adresse3'] . " " . $Client->infos['code_postal'] . " " . $Client->infos['ville'] . " " . $Client->infos['pays_nom'];

		$Email->envoyer("commande_nouvelle", $CLIENT_EMAIL, $_MAIL_COMMANDE, $CLIENT_LANGUE);

		# Redirect...
		$_SESSION['mode_paiement_fin'] = $mode_paiement;
		if ($mode_paiement==$_CB) {
			$_SESSION['pay'] = array();
			$_SESSION['pay']['MONTANT_CB']	= $Caddie->infos['montant_TTC'] * 100;
			$_SESSION['pay']['CODE_CMD']	= $CODE_CMD;
		}
		$Navig->redirect("commander_fin.php");
	}
	//////////////////////////////////////////////////
?>
<div class="catalogue clearfix">
  <div class="head">Commander</div>
  <div class="padding">
<form action="commander.php#livraison" method="post" name="form_commander" id="form_commander">
  <?php if ($ERR) { ?><p class="erreur"><?php echotrad($ERR); ?></p><?php } ?>
  <p>&nbsp;</p>
  <table width="100%" border="0" cellpadding="2" cellspacing="0" class="tbl">
    <tr>
      <th>R&eacute;f</th>
      <th>Article</th>
      <th>Quantit&eacute;</th>
      <th>Prix</th>
      <th>Montant</th>
    </tr>
<?php while ($row = $Caddie->lire()) { ?>
    <tr class="ligne<?php echo toggle_0_1(); ?>">
      <td valign="top"><a href="<?php echo $row['url_produit']; ?>"><?php echo $row['reference']; ?></a></td>
      <td valign="top">
	  	<a href="<?php echo $row['url_produit']; ?>"><?php echo $row['article']; ?></a>
<?php if ($row['rupture_stock']) { ?><br /><span class="erreur">RUPTURE MOMENTANEE</span><?php } ?>	  </td>
      <td align="center" valign="top"><?php echo $row['quantite']; ?></td>
      <td align="right" valign="top"><?php echo prix($row['prix_TTC']); ?></td>
      <td align="right" valign="top" nowrap="nowrap"><?php echo prix($row['montant_ligne_TTC']); ?></td>
    </tr>
<?php if ($row['promo_spe']) { ?>
    <tr class="promos">
      <td valign="top">&nbsp;</td>
      <td valign="top">Promotion : <a href="promo_spe.php?code_promo_spe=<?php echo $row['promo_spe']; ?>" style='font-size: smaller; '>2 produits achetés = le 3ème offert</a></td>
      <td align="center" valign="top"><?php echo $row['promo_spe_quantite']; ?></td>
      <td align="right" valign="top"><?php echo prix($row['promo_spe_prix_TTC']); ?></td>
      <td align="right" valign="top" nowrap="nowrap"><?php echo prix($row['promo_spe_montant_TTC']); ?></td>
    </tr>
<?php } // Fin promo_spe... ?>
<?php } // fin while... ?>
<?php if ($Caddie->id_cadeau) { ?>
  <tr class="cadeau">
	<td valign="top"><a href="<?php echo $Caddie->cadeau->infos['url']; ?>"><?php echo $Caddie->cadeau->infos['reference']; ?></a></td>
	<td valign="top"><img src="images/boutons/bt_cadeau.gif" align="absmiddle" /> : <a href="<?php echo $Caddie->cadeau->infos['url']; ?>"><b><?php echo $Caddie->cadeau->infos['nom_complet']; ?></b></a>
<?php if ($Caddie->cadeau->infos['rupture_stock']) { ?><br />
<span class="erreur">RUPTURE MOMENTANEE</span><?php } ?>	</td>
	<td align="center" valign="top">1</td>
	<td align="right" valign="top"><?php echo prix(0); ?></td>
	<td align="right" valign="top" nowrap="nowrap"><?php echo prix(0); ?></td>
  </tr>
<?php } // fin cadeau... ?>
<?php if ($Caddie->achat_carte_fidelite) { ?>
  <tr class="cadeau">
    <td valign="top">&nbsp;</td>
    <td valign="top">Achat cl&eacute; fid&eacute;lit&eacute; (remise : -<?php echo $_REMISE_CARTE_FIDELITE; ?>%)</td>
    <td align="center" valign="top">1</td>
    <td align="right" valign="top"><?php echo prix($_PRIX_CARTE_FIDELITE); ?></td>
    <td align="right" valign="top" nowrap="nowrap"><?php echo prix($_PRIX_CARTE_FIDELITE); ?></td>
  </tr>
<?php } // fin carte fidélité... ?>
    <tr>
      <th colspan="4">&nbsp;</th>
      <th align="right" valign="top" nowrap="nowrap">&nbsp;</th>
    </tr>
    <tr>
      <td colspan="4" align="right"><b>Sous-Total</b></td>
      <td align="right" valign="top" nowrap="nowrap"><b><?php echo prix($Caddie->infos['total_TTC']); ?></b></td>
    </tr>
<?php if ($Caddie->code_promo) { ?>
  <tr>
    <td colspan="4" align="right" class="ok">Code Promo : <?php echo $Caddie->code_promo; ?> - Remise -<?php echo $Caddie->remise_code_promo; ?>%</td>
    <td align="right" valign="top" nowrap="nowrap" class="ok"><?php echo "-".prix($Caddie->infos['remise_code_promo_TTC']); ?></td>
  </tr>
<?php } // Fin code promo... ?>
<?php if ($Caddie->remise_carte_fidelite) { ?>
    <tr>
      <td colspan="4" align="right" class="ok">Carte de fid&eacute;lit&eacute; : Remise -<?php echo $_REMISE_CARTE_FIDELITE; ?>%</td>
      <td align="right" valign="top" nowrap="nowrap" class="ok"><?php echo "-".prix($Caddie->infos['remise_carte_fidelite_TTC']); ?></td>
    </tr>
<?php } // Fin code promo... ?>
    <tr>
      <td colspan="4" align="right">Poids total</td>
      <td align="right" valign="top" nowrap="nowrap"><?php echo $Caddie->infos['poids_total']; ?>g</td>
    </tr>
    <?php if (!$Caddie->id_pays) { ?>
    <tr>
      <td colspan="4" align="right">Frais d'exp&eacute;dition </td>
      <td align="right" valign="top" nowrap="nowrap">Pays d'exp&eacute;dition non mentionn&eacute;</td>
    </tr>
    <?php } else { ?>
    <tr>
      <td colspan="4" align="right">Frais d'exp&eacute;dition</td>
      <td align="right" valign="top" nowrap="nowrap"><?php echo prix($Caddie->infos['fdp_TTC']); ?></td>
    </tr>
    <?php if ($Caddie->id_pays==_ID_FRANCE && $Caddie->id_mode_transport==1) { ?>
    <tr>
      <td colspan="4" align="right" class="ok">Frais d'exp&eacute;dition offerts pour la france </td>
      <td align="right" valign="top" nowrap="nowrap" class="ok"><?php echo "-".prix($Caddie->infos['fdp_TTC']); ?></td>
    </tr>
    <?php } // Fin France... ?>
    <?php } // Fin affichage FDP... ?>
    <tr>
      <td colspan="4" align="right"><b>Montant Total </b></td>
      <td align="right" valign="top" nowrap="nowrap"><b><?php echo prix($Caddie->infos['montant_TTC']); ?></b></td>
    </tr>
    <tr>
      <td colspan="4" align="right">Total HT</td>
      <td align="right" valign="top" nowrap="nowrap"><?php echo prix($Caddie->infos['montant_HT']); ?></td>
    </tr>
    <tr>
      <td colspan="4" align="right">TVA</td>
      <td align="right" valign="top" nowrap="nowrap"><?php echo prix($Caddie->infos['montant_TVA']); ?></td>
    </tr>
    <tr>
      <td colspan="4" align="right">Nb produits </td>
      <td align="right" valign="top" nowrap="nowrap"><?php echo $Caddie->infos['nb']; ?></td>
    </tr>
  </table>
  <p><a href="caddie.php"><b>Retour au caddie</b></a>&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;<a href="<?php echo $Navig->page_continuer; ?>"><b>Continuer les achats</b></a></p>
	<a name="livraison" id="livraison"></a>
  <hr />
  <h2 class="header2">Livraison  </h2>
  <table  border="0" cellspacing="0" cellpadding="2" class="tbl">
    <tr>
      <th valign="middle"><b>Nom</b></th>
      <td align="left" valign="middle"><input name="livr_nom" type="text" id="livr_nom" value="<?php echoif("livr_nom"); ?>" maxlength="50" /></td>
    </tr>
    <tr>
      <th valign="middle"><b>Pr&eacute;nom</b></th>
      <td align="left" valign="middle"><input name="livr_prenom" type="text" id="livr_prenom" value="<?php echoif("livr_prenom"); ?>" maxlength="50" /></td>
    </tr>
    <tr>
      <th valign="top"><b>Appartement</b></th>
      <td align="left" valign="middle"><input name="livr_adresse" type="text" id="livr_adresse" value="<?php echoif("livr_adresse"); ?>" size="40" />
        <br />
      <span class="petit">ex: 1er &eacute;tage, Esc B, Appart 268 </span></td>
    </tr>
    <tr>
      <th valign="top"><b>B&acirc;timent</b></th>
      <td align="left" valign="middle"><input name="livr_adresse2" type="text" id="livr_adresse2" value="<?php echoif("livr_adresse2"); ?>" size="40" />
      <br />
      <span class="petit">ex: Immeuble/B&acirc;timent/R&eacute;sidence des Accacias </span></td>
    </tr>
    <tr>
      <th valign="top">Rue / Lieu dit</th>
      <td align="left" valign="middle"><input name="livr_adresse3" type="text" id="livr_adresse3" value="<?php echoif("livr_adresse3"); ?>" size="40" />
      <br />
      <span class="petit">ex: 6 rue des Palmiers, Lieudit &quot;les palmiers&quot;</span></td>
    </tr>
    <tr>
      <th valign="middle"><b>Code postal </b></th>
      <td align="left" valign="middle"><input name="livr_code_postal" type="text" id="livr_code_postal" value="<?php echoif("livr_code_postal"); ?>" maxlength="10" /></td>
    </tr>
    <tr>
      <th valign="middle"><b>Ville</b></th>
      <td align="left" valign="middle"><input name="livr_ville" type="text" id="livr_ville" value="<?php echoif("livr_ville"); ?>" maxlength="50" /></td>
    </tr>
    <tr>
      <th valign="middle">Pays</th>
      <td align="left" valign="middle"><?php liste_deroulante('livr_id_pays', $query_liste_pays, '--- CHOISIR ---'); ?></td>
    </tr>
    <tr>
      <th valign="middle"><b>T&eacute;l&eacute;phone</b></th>
      <td align="left" valign="middle"><input name="livr_tel" type="text" id="livr_tel" value="<?php echoif("livr_tel"); ?>" maxlength="50" /></td>
    </tr>
    <tr>
      <th valign="middle">Mode de transport</th>
      <td style="padding:1px;"><?php liste_deroulante('livr_id_mode_transport', $query_liste_modes_transport, ''); ?></td>
    </tr>
    <tr>
      <th valign="middle"><input name="mode_paiement" type="hidden" id="mode_paiement" value="0" /></th>
      <td style="padding:1px;"><input name="bt_modifier" type="submit" id="bt_modifier" value="Modifier &gt;" class="bouton" /></td>
    </tr>
  </table>
<hr />
<p>Cochez cette case apr&egrave;s avoir lu et approuv&eacute; les conditions <a href="cgv.php">g&eacute;n&eacute;rales de vente</a> 
<input name="accept_cgv" type="checkbox" id="accept_cgv" value="1" class="checkbox" <?php echocheckedbool("accept_cgv"); ?> /></p>
<?php if ($ERR) { ?><p class="erreur"><?php echotrad($ERR); ?></p><?php } ?>
<hr />
<h2 class="header2">Paiement par CB</h2>
<p><a href="http://entreprises.bnpparibas.fr/Nos-solutions/Gestion-des-flux/Faciliter-vos-encaissements/Cartes/Mercanet" target="_blank"><img src="images/paiement/Mercanet_logo-j.gif" width="150" height="53" border="0" align="absmiddle" alt="" /></a> Paiement s&eacute;curis&eacute; <img src="images/paiement/CLEF.gif" width="78" height="18" border="0" alt="" /> par <a href="http://www.bnpparibas.net/banque/portail/particulier/HomePage?type=site" target="_blank"><img src="images/paiement/BNPP_Logo.gif" width="164" height="62" border="0" align="absmiddle" /></a></p>
<p><a href="javascript:paiement('1');"><img src="images/paiement/CB.gif" width="55" height="35" border="0" alt="" /></a><a href="javascript:paiement('1');"><img src="images/paiement/MASTERCARD.gif" width="55" height="35" border="0" alt="" /></a><a href="javascript:paiement('1');"><img src="images/paiement/VISA.gif" width="55" height="35" border="0" alt="" /></a></p>
<br/>
<p><input name="bt_paiement_cb" type="button" id="bt_paiement_cb" value="Paiement &gt;&gt;&gt;" onclick="paiement('1');" class="paiement-button" />
</p>
<br/>
<hr />
<h2 class="header2">Paiement par Ch&egrave;que :</h2>
<br/>
<p><input name="bt_paiement2" type="button" id="bt_paiement2" value="Enregistrer ma commande par ch&egrave;que &gt;&gt;&gt;" onclick="paiement('2');" class="paiement-button" /></p>
<br/>
</form>
</div>
</div>
<?php modele_page("_modele_page.php", "Commander"); ?>