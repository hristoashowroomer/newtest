<?php
	include("kernel/main.php");
	include("_affiche_vignette.php");

	//
	// Variables...
	//
	$ERR					= false;
	$DEMANDE_CONNEXION		= false; // true si demande de connexion...(utile pour afficher $ERR dans le bon form...
	$DEMANDE_PASSWD_OUBLIE	= false; // true si demande de passwd oublié...
	post_var('compte_civilite', "Mme", "*");

	# Css additionnel...
	$css = "compte";

	# Ancre...
	$ancre = "";

	# Historique...
	$titre_historique = "Mon compte - connexion";
	//////////////////////////////////////////////////


	//
	// Retour si déjà connecté...
	//
	if ($_COMPTE) $Navig->redirect("compte.php");
	//////////////////////////////////////////////////


	//
	// Tester email+password...
	//
	if (isset($_POST['deja_compte'])) {
		$DEMANDE_CONNEXION = true;

		$compte_email	= trim($_POST['compte_email']);
		$compte_passwd	= trim($_POST['compte_passwd']);
		
		$Client = new Client();

		if ($_COMPTE = $Client->connect($compte_email, $compte_passwd)) {
			$Caddie->client($_COMPTE);								// mettre à jour le caddie avec les anciennes et les nouvelles lignes...
			$Navig->redirect($Navig->page_demande_connect_compte);	// pour recharger la page qui a demandé la connexion...
		}
		else $ERR = trad($Client->err_num);

		# Ancre...
		$ancre = "connexion";
	}
	//////////////////////////////////////////////////


	//
	// OUBLI MOT DE PASSE...
	//
	if (isset($_POST['oubli_passwd_email'])) {
		$DEMANDE_PASSWD_OUBLIE = true;
		$oubli_passwd_email	= trim($_POST['oubli_passwd_email']);

		# Tester si l'email existe...
		$email_dest	= mystripslashes($oubli_passwd_email);
		if ($Client->oubli_passwd($oubli_passwd_email)) {
			# Email... les variables CLIENT_??? ont été générées par la fonction oubli_passwd...
			$Email->envoyer('compte_mot_passe_oublie', $email_dest, $_MAIL_CONTACT);
		} else $ERR = trad($Client->err_num);

		# Ancre...
		$ancre = "oubli";
	}
	//////////////////////////////////////////////////
?>
<div class="catalogue clearfix">
<div class="head">Connexion</div>
<div class="padding">
  <form name="form_connect_compte" id="form_connect_compte" method="post" action="" >
    <h2 class="header2"><a name="connexion" id="connexion"></a>Vos identifiants de connexion</h2>
    <?php if ($DEMANDE_CONNEXION && $ERR) { ?>
    <p class="erreur"><?php echo $ERR; ?></p>
    <?php } ?>
    <table border="0" cellpadding="0" cellspacing="0">
      <tr>
        <td><b>Adresse email :</b><br />
        <input name="compte_email" type="text" value="<?php echoif ("compte_email"); ?>" size="50" maxlength="50" /></td>
        <td><input name="deja_compte" type="hidden" id="deja_compte" value="1" /></td>
      </tr>
      <tr>
        <td><b>Mot de passe :</b><br />
        <input name="compte_passwd" type="password" id="compte_passwd" size="50" maxlength="20" /></td>
        <td valign="bottom"><input name="bt_envoyer" type="submit" id="bt_envoyer" value="Valider &gt;" class="bouton" style="margin-left:10px;" /></td>
      </tr>
    </table>
  </form>
  <p>&nbsp;</p>
  <p>&nbsp;</p>
  <h2 class="header2"><a name="oubli" id="oubli"></a>Vous avez oubli&eacute; votre mot de passe ?</h2>
  <?php
if ($DEMANDE_PASSWD_OUBLIE) {
	if ($ERR) {
?>
  <p class="erreur"><?php echo $ERR; ?></p>
  <?php } else { ?>
  <p class="ok"><?php echotrad("CLIENT_MOTPASSE_ENVOYE"); ?></p>
  <?php
		}	// fin $ERR...
	}	// fin Demande oubli passwd...
?>
  <form action="" method="post" name="form_oubli_passwd" id="form_oubli_passwd">
    <p>Inscrivez votre adresse mail vous recevrez vos identifiants de connexion.</p>
    <table  border="0" cellpadding="0" cellspacing="0">
      <tr>
        <td><b>Email : </b><br />
        <input name="oubli_passwd_email" type="text" id="oubli_passwd_email" value="<?php echoif ("oubli_passwd_email"); ?>" size="50" maxlength="50" /></td>
        <td valign="bottom"><input name="bt_envoyer2" type="submit" id="bt_envoyer2" value="Valider &gt;" class="bouton" style="margin-left:10px;" /></td>
      </tr>
    </table>
  </form>
  <p>&nbsp;</p>
  <h2 class="header2">Nouveau client ?</h2>
  <p><a href="compte_creer.php"><b>Inscrivez-vous &gt;&gt;&gt;</b></a></p>
  </div>
  </div>
  <?php modele_page("_modele_page.php", "Connexion"); ?>