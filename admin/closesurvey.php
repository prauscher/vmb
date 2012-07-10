<?php

require_once("../config.inc.php");
require_once("../library.inc.php");
require_once("../auswertung.inc.php");
require_once("../mysql.inc.php");
$db = DB_getLink();

$idlang = (isset($_REQUEST["id"]) ? stripslashes($_REQUEST["id"]) : null);
list($id, $lang) = explode(":", $idlang);
$id = intval($id);

$host = stripslashes($_SERVER["HTTP_HOST"]);
$row = do_sanity_check_admin($db, $id, $host);

if ($row->active != "Y") {
	die("<b>Diese Umfrage ist bereits geschlossen.</b>");
}

if ($db->query("
	UPDATE `".$db->real_escape_string(DB_getSurveysTable())."`
	SET `active` = 'N' WHERE `sid` = ".intval($id)) )
{
	echo "<p><b>Umfrage erfolgreich beendet</b></p>";
	echo "<p><a href=\"../auswertung.php?id=".intval($id)."\">zur Auswertung</a></p>";
	echo "<p><a href=\"index.php\">Administration</a></p>";
	logmessage($id, $_SERVER["PHP_AUTH_USER"] . " hat die Umfrage beendet.");

	generateAuswertung($db, $id);
}

?>
