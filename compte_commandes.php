<?php
	include ("kernel/main.php");
	include ("_affiche_vignette.php");

	# Pas loggué...
	if (!$_COMPTE) $Navig->fix_page_demande_connect_compte(true);


	//
	// Variables...
	//
	# Historique...
	$titre_historique = "Mon compte - mes commandes";
	//////////////////////////////////////////////////


	//
	// Requêtes...
	//
	# Commandes en cours...
	sql_mysql_query ("	SELECT	*,
								DATE_FORMAT(commandes.date_commande,'%d/%m/%Y') AS la_date
						FROM	commandes
						WHERE		id_client = '$_COMPTE'
								AND	statut < 100
								AND NOT (mode_paiement=1 AND statut=1)
						ORDER BY id
					", 'cmd_encours');

	# Commandes expédiées...
	sql_mysql_query ("	SELECT	*,
								DATE_FORMAT(commandes.date_commande,'%d/%m/%Y') AS la_date
						FROM	commandes
						WHERE		id_client = '$_COMPTE'
								AND	statut = 100
						ORDER BY id
					", 'cmd_ok');
	//////////////////////////////////////////////////
?>
<div class="catalogue clearfix">
<div class="head">Mon compte - mes commandes</div>
<div class="padding">
<?php if ($nb_cmd_encours) { ?>
              <h2 class="header2">Mes commandes en cours</h2>
              <table width="100%"  border="0" cellspacing="2" cellpadding="2" class="tbl">
                <tr>
                  <th>Num&eacute;ro de commande</th>
                  <th>Paiement</th>
                  <th>Date</th>
                  <th>Total</th>
                  <th>Statut</th>
                  <th>&nbsp;</th>
                </tr>
                <?php while ($row = mysql_fetch_object($query_cmd_encours)) { ?>
                <tr align="center">
                  <td><?php echo $row->code_commande; ?></td>
                  <td><?php echo $_MODE_PAIEMENT[$row->mode_paiement]; ?></td>
                  <td><?php echo $row->la_date; ?></td>
                  <td><?php echo prix($row->total_TTC); ?></td>
                  <td <?php if ($row->statut==3) { ?>style="font-weight:bold;color:#009900;"<?php } ?>><?php echo $_STATUT_CMD[$row->statut]; ?></td>
                  <td><a href="javascript:facture('<?php echo $row->id; ?>');"><b>Voir</b></a></td>
                </tr>
                <?php } ?>
              </table>
              <p>&nbsp;</p>
<?php } ?>
<?php if ($nb_cmd_ok) { ?>
              <h2 class="header2">Mes commandes exp&eacute;di&eacute;es</h2>
              <table width="100%"  border="0" cellspacing="2" cellpadding="2" class="tbl">
                <tr>
                  <th>Num&eacute;ro de commande</th>
                  <th>Paiement</th>
                  <th>Date</th>
                  <th>Total</th>
                  <th>Statut</th>
                  <th>&nbsp;</th>
                </tr>
                <?php while ($row = mysql_fetch_object($query_cmd_ok)) { ?>
                <tr align="center">
                  <td><?php echo $row->code_commande; ?></td>
                  <td><?php echo $_MODE_PAIEMENT[$row->mode_paiement]; ?></td>
                  <td><?php echo $row->la_date; ?></td>
                  <td><?php echo prix($row->total_TTC); ?></td>
                  <td><?php echo $_STATUT_CMD[$row->statut]; ?></td>
                  <td><a href="javascript:facture('<?php echo $row->id; ?>');"><b>Voir</b></a></td>
                </tr>
                <?php } ?>
              </table>
<?php } ?>
</div>
</div>
<?php modele_page("_modele_page.php", "Commander"); ?>