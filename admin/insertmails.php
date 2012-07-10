<?php

require_once("../config.inc.php");
require_once("../library.inc.php");
require_once("session.inc.php");

require_once("../mysql.inc.php");
$db = DB_getLink();

$idlang = (isset($_REQUEST["id"]) ? stripslashes($_REQUEST["id"]) : null);
list($id, $lang) = explode(":", $idlang);
$id = intval($id);

$host = stripslashes($_SERVER["HTTP_HOST"]);
do_sanity_check_admin($db, $id, $host);

if (isset($_POST["do"])) {
	$a = stripslashes($_POST["a"]);
	$as = explode("\n", $a);
	$c = 0; $b = 0;
	foreach ($as AS $a) {
		$a = trim($a);
		$inform = array();
		if ($a == "") {
			continue;
		}
		$rslt = $db->query("SELECT `sendmail` FROM `".$db->real_escape_string(DB_getBlacklistTable())."` WHERE `mailhash` = '".$db->real_escape_string(getMailhash($a))."'");
		if ($row = $rslt->fetch_object()) {
			if ($row->sendmail != "Y") {
				$b++;
				continue;
			}
		} else {
			$db->query("INSERT INTO `".$db->real_escape_string(DB_getBlacklistTable())."` (`mailhash`) VALUES ('".$db->real_escape_string(getMailhash($a))."')");
		}
		if ($db->query("
			INSERT INTO `".$db->real_escape_string(DB_getTokenTable($id))."`
				(`emailstatus`, `email`, `language`)
			VALUES	('OK', '".$db->real_escape_string($a)."', '".$lang."')"))
		{
			$c++;
		} else {
			echo "<p><b>".$a." konnte nicht eingefuegt werden.</b></p>\n";
		}
	}
	
	$rslt = $db->query("
		SELECT	`email`
		FROM	`".$db->real_escape_string(DB_getTokenTable($id))."`");
	$as = array(); $duplikate = array();
	while ($row = $rslt->fetch_object()) {
		if (in_array($row->email, $as)) {
			$duplikate[] = $row->email;
		} else {
			$as[] = $row->email;
		}
	}
	unset($as);
	
	if (count($duplikate) > 0) {
		echo "<p><b>Es wurden Duplikate gefunden.</b></p>";
		echo "<p><a href=\"?id=".$idlang."&amp;clearduplikate=1\">Bereinigen</a></p>";
		echo "<pre>".implode("\n", $duplikate)."</pre>";
	}
	
	logmessage($id, $_SERVER["PHP_AUTH_USER"] . " hat $c Adressen eingetragen.");
	
	echo "<p><b>Habe $c Adressen eingefuegt!</b></p>\n";
	echo "<p><b>$b Adressen wurden auf eigenen Wunsch uebersprungen (OptOut).</b></p>\n";
	echo "<p><a href=\"composemail.php?id=".$idlang."\">Mails verschicken</a></p>";
}

if (isset($_REQUEST["clear"])) {
	if ($db->query("DELETE FROM `".$db->real_escape_string(DB_getTokenTable($id))."`")) {
		echo "<p>Habe {$db->affected_rows} Adressen gel&ouml;scht.</p>";
	} else {
		echo "<p>Fehler beim Leeren...</p>";
	}
}

if (isset($_REQUEST["clearduplikate"])) {
	$rslt = $db->query("
		SELECT	`tid`, `email`
		FROM	`".$db->real_escape_string(DB_getTokenTable($id))."`");
	$as = array(); $c = 0; $duplikate = array();
	while ($row = $rslt->fetch_object()) {
		if (in_array($row->email, $as)) {
			if ($db->query("DELETE FROM `".$db->real_escape_string(DB_getTokenTable($id))."`
					WHERE `tid` = ".intval($row->tid))) {
				$c++;
			} else {
				echo "<p>Weiterhin doppelt in der Datenbank: {$row->email}</p>";
			}
		} else {
			$as[] = $row->email;
		}
	}
	
	echo "<p><b>Habe $c Duplikate gel&ouml;scht!</b></p>";
	echo "<p><a href=\"composemail.php?id=".$idlang."\">Mails verschicken</a></p>";
	exit;
}

if (isset($_POST["show"])) {
	$rslt = $db->query("
		SELECT	`email`
		FROM	`".$db->real_escape_string(DB_getTokenTable($id))."`");
	echo "<p><b>Momentan stehen {$rslt->num_rows} Adressen im Verteiler.</b></p>";
	echo "<p><a href=\"composemail.php?id=".$idlang."\">Mails verschicken</a></p>";
	echo "<pre>";
	while ($row = $rslt->fetch_object()) {
		echo $row->email . "\n";
	}
	echo "</pre>";
}

?>
<form action="" accept-charset="<?= $config->charset ?>" method="post">
	<fieldset>
		<input type="hidden" name="id" value="<?= $idlang ?>" />
		<p style="font-weight: bold;">Bitte die gew&uuml;nschten Empf&auml;nger auflisten (eine Mailadresse pro Zeile):</p>
		<textarea name="a" rows=20 cols=50><?= implode("\n", getSessionMails()) ?></textarea><br />
		<input type="submit" name="do" value="eintragen" />
		<input type="submit" name="show" value="anzeigen" />
		<input type="submit" name="clear" value="leeren" />
	</fieldset>
</form>
