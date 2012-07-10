<?php

require_once("../library.inc.php");

require_once("../mysql.inc.php");
require_once("session.inc.php");
$db = DB_getLink();

$idlang = (isset($_REQUEST["id"]) ? stripslashes($_REQUEST["id"]) : null);
list($id, $lang) = explode(":", $idlang);
$id = intval($id);

$host = stripslashes($_SERVER["HTTP_HOST"]);
do_sanity_check_admin($db, $id, $host);

initSessionByDB($idlang, $db);

if (isset($_POST["reset"])) {
	clearSession($idlang);
}

if (isset($_POST["do"])) {
	$subject = stripslashes($_POST["subject"]);
	$header = trim(stripslashes($_POST["header"]));
	$body = stripslashes($_POST["body"]);
	
	setSessionValue($idlang,"subject",$subject);
	setSessionValue($idlang,"header",$header);
	setSessionValue($idlang,"body",$body);
	
	$db->query("
		UPDATE	`".$db->real_escape_string(DB_getSurveysLangTable())."`
		SET	`surveyls_email_invite_subj` = '".$db->real_escape_string($subject)."',
			`surveyls_email_invite` = '".$db->real_escape_string(rtrim($header)."\n\n".$body)."'
		WHERE	`surveyls_survey_id` = ".intval($id)." AND `surveyls_language` = '".$db->real_escape_string($lang)."'");
	
	echo "<p><b>Gespeichert! Bitte kontrolliere die Angaben erneut und klicke dannach auf &quot;Mails verschicken&quot;.</b></p>\n";
	echo "<p><a href=\"previewmail.php?id=".$idlang."\">Vorschau anzeigen</a></p>";
}

?>
<form action="" accept-charset="<?= $config->charset ?>" method="post">
	<fieldset>
		<input type="hidden" name="id" value="<?= $idlang ?>" />
		<table>
		<tr><th>Betreff *:</th><td><input type="text" size="30" name="subject" value="<?= htmlentities2(getSessionValue($idlang,"subject")) ?>" /></td></tr>
		<tr><th colspan="2">Header *:</th></tr>
		<tr><td colspan="2"><textarea rows=10 cols=50 name="header"><?= htmlentities2(getSessionValue($idlang,"header")) ?></textarea></td></tr>
		<tr><th colspan="2">Text *:</th></tr>
		<tr><td colspan="2"><textarea rows=30 cols=50 name="body"><?= htmlentities2(getSessionValue($idlang,"body")) ?></textarea></td></tr>
		</table>
		<p><strong>*</strong> Benutze die folgenden Platzhalter:</p>
		<table>
		<tr><th>{SURVEYNAME}</th><td>Titel der Umfrage</td></tr>
		<tr><th>{SURVEYDESCRIPTION}</th><td>Beschreibung der Umfrage</td></tr>
		<tr><th>{START}</th><td>Anfangsdatum</td></tr>
		<tr><th>{EXPIRE}</th><td>Wann die Umfrage endet</td></tr>
		<tr><th>{TOKEN}</th><td>Der Token im Klartext</td></tr>
		<tr><th>{OPTOUTLINK}</th><td>Link zum Austragen aus dem Meinungsbildtool</td></tr>
		<tr><th>{OPTOUTDAYS}</th><td>Wie viele Tage ein OptOut-Link g&uuml;ltig ist</td></tr>
		<tr><th>{SURVEYURL}</th><td>Link zum Teilnehmen an der Umfrage</td></tr>
		<tr><th>{RESULTURL}</th><td>Link zu den Ergebnissen der Umfrage</td></tr>
		</table>
		<input type="submit" name="reset" value="standart zur&uuml;cksetzen" />
		<input type="submit" name="do" value="speichern" />
	</fieldset>
</form>
