<?php

require_once("../library.inc.php");
require_once("../mysql.inc.php");
$db = DB_getLink();

$idlang = (isset($_REQUEST["id"]) ? stripslashes($_REQUEST["id"]) : null);
list($id, $lang) = explode(":", $idlang);
$id = intval($id);

$host = stripslashes($_SERVER["HTTP_HOST"]);
$row = do_sanity_check_admin($db, $id, $host);

if ($row->active == "Y") {
	die("<b>Diese Umfrage ist bereits aktiv.</b>");
}

if ($db->query("
	UPDATE `".$db->real_escape_string(DB_getSurveysTable())."`
	SET `active` = 'Y' WHERE `sid` = ".intval($id)))
{
	echo "<p><b>Umfrage erfolgreich aktiviert - all tasks done ;)</b></p>";
	echo "<p><a href=\"index.php\">Administration</a></p>";
	logmessage($id, $_SERVER["PHP_AUTH_USER"] . " hat die Umfrage aktiviert.");
	
	// loesche (alte) statistiken
	$rslt = $db->query("SELECT COUNT(`id`) AS 'votes' FROM `".$db->real_escape_string(DB_getResultTable($id))."`");
	$votesrow = @$rslt->fetch_object();
	$votes = $votesrow->votes;
	$rslt = $db->query("
		SELECT	CONCAT(`sid`,'X',`gid`,'X',`qid`) AS 'feldname',
			`qid` AS 'qid', `question` AS 'q'
		FROM	`".$db->real_escape_string(DB_getQuestionsTable())."`
		WHERE	`sid` = ".intval($id)." AND `type` = 'L'
		ORDER BY `question_order`");
	if (@unlink(getstatpath($id))) {
		echo "<p>Auswertung wurde geloescht!</p>";
	}
	if (@unlink(getstatpath_tokenlist($id))) {
		echo "<p>Tokenliste wurde geloescht!</p>";
	}
	while ($row = $rslt->fetch_object()) {
		if (@unlink(getstatpath_graph($row->feldname))) {
			echo "<p>Habe alte Umfrageergebnisse geloescht!</p>";
		}
		if (@unlink(getstatpath_fullgraph($row->feldname))) {
			echo "<p>Habe alte Umfrageergebnisse (Ausfuehrlich) geloescht!</p>";
		}
		$qrslt = $db->query("
			SELECT	`code`
			FROM `".$db->real_escape_string(DB_getAnswersTable())."`
			WHERE	`qid` = ".intval($row->qid));
		while ($qrow = $qrslt->fetch_object()) {
			if (@unlink(getstatpath_optimg($row->feldname, $qrow->code))) {
				echo "<p>Habe alte Auswahlbildchen geloescht!</p>";
			}
		}
		if (@unlink(getstatpath_optimg($row->feldname, "_____"))) {
			echo "<p>Habe alte Auswahlbildchen geloescht!</p>";
		}
	}
}

?>
