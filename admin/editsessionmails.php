<?php

include("../config.inc.php");
include("session.inc.php");

if (isset($_POST["do"])) {
	$a = stripslashes($_POST["a"]);
	$as = explode("\n", $a);
	foreach ($as AS $a) {
		addSessionMail($a);
	}
	
}

if (isset($_POST["clear"])) {
	clearSessionMails();
}

?>
<p><a href="selectsurvey.php">Zur&uuml;ck zur &Uuml;bersichtsmaske</a></p>
<?php

if (isset($_POST["show"])) {
	echo "<pre>".implode("\n", getSessionMails())."</pre>";
}

?>
<form action="" accept-charset="<?= $config->charset ?>" method="post">
	<fieldset>
		<input type="hidden" name="id" value="<?= $idlang ?>" />
		<p style="font-weight: bold;">Bitte die gew&uuml;nschten Empf&auml;nger auflisten (eine Mailadresse pro Zeile):</p>
		<textarea name="a" rows=20 cols=50></textarea><br />
		<input type="submit" name="do" value="eintragen" />
		<input type="submit" name="show" value="anzeigen" />
		<input type="submit" name="clear" value="leeren" />
	</fieldset>
</form>
