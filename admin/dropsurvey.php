<?php

require_once("../library.inc.php");
require_once("../mysql.inc.php");
$db = DB_getLink();

$idlang = (isset($_REQUEST["id"]) ? stripslashes($_REQUEST["id"]) : null);
list($id, $lang) = explode(":", $idlang);
$id = intval($id);

$host = stripslashes($_SERVER["HTTP_HOST"]);
do_sanity_check_admin($db, $id, $host);

if (!$db->query("
	DELETE FROM `".$db->real_escape_string(DB_getSurveysTable())."`
	WHERE	`sid` = ".intval($id)))
{
	die("<p>Umfrage wurde nicht gel&ouml;scht!</p>");
}

if (!$db->query("
	DELETE FROM `".$db->real_escape_string(DB_getSurveysLangTable())."`
	WHERE	`surveyls_survey_id` = ".intval($id)))
{
	die("<p>Konnte Umfragetexte nicht l&ouml;schen!</p>");
}

if (!$db->query("
	DELETE FROM `".$db->real_escape_string(DB_getGroupsTable())."`
	WHERE	`sid` = ".intval($id)))
{
	die("<p>Konnte Umfragegruppe nicht l&ouml;schen!</p>");
}

/* Hilfstabellen loeschen */
if (!$db->query("
	DROP TABLE `".$db->real_escape_string(DB_getResultTable($id))."`"))
{
	echo("<p>Konnte Antworttabelle nicht l&ouml;schen!</p>");
}

if (!$db->query("
	DROP TABLE `".$db->real_escape_string(DB_getTokenTable($id))."`"))
{
	echo("<p>Konnte Tokentabelle nicht l&ouml;schen!</p>");
}

/* loesche alte statistiken */
if (@unlink(getlogpath($id))) {
	echo "<p>Log wurde geloescht!</p>";
}
if (@unlink(getstatpath_tokenlist($id))) {
	echo "<p>Tokenliste wurde geloescht!</p>";
}
$rslt = $db->query("SELECT COUNT(`id`) AS 'votes' FROM `".$db->real_escape_string(DB_getResultTable($id))."`");
if ($rslt != null) {
	$votesrow = @$rslt->fetch_object();
	$votes = $votesrow->votes;
	$rslt = $db->query("
		SELECT	CONCAT(`sid`,'X',`gid`,'X',`qid`) AS 'feldname',
			`qid` AS 'qid', `question` AS 'q'
		FROM	`".$db->real_escape_string(DB_getQuestionsTable())."`
		WHERE	`sid` = ".intval($id)." AND `type` = 'L'
		ORDER BY `question_order`");
	while ($row = $rslt->fetch_object()) {
		if (@unlink(getstatpath_graph($row->feldname))) {
			echo "<p>Habe alte Umfrageergebnisse geloescht!</p>";
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
	}
}

/* Fragen & Antworten loeschen */
$rslt = $db->query("SELECT `qid` FROM `".DB_getQuestionsTable()."` WHERE `sid` = ".intval($id));
while ($row = $rslt->fetch_object()) {
	$db->query("DELETE FROM `".DB_getAnswersTable()."` WHERE `qid` = ".intval($row->qid));
}
$db->query("DELETE FROM `".DB_getQuestionsTable()."` WHERE `sid` = ".intval($id));

echo "<p>Die Umfrage wurde gel&ouml;scht</p>";

?>
