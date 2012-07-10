<?php

require_once("library.inc.php");
require_once("auswertung.inc.php");

$id = (isset($_REQUEST["id"]) ? intval($_REQUEST["id"]) : null);

// Falls wir ein altes Ergebnis haben, zeige dieses
if (file_exists(getstatpath($id))) {
	readfile(getstatpath($id));
	exit;
}

// SOC - Start of Code :P

require_once("smarty.inc.php");
$smarty = getSmarty();

require_once("mysql.inc.php");
$db = DB_getLink();

$rslt = $db->query("
	SELECT	`s`.`active` AS 'active', `s`.`usetokens` AS 'usetokens',
		UNIX_TIMESTAMP(`s`.`startdate`) AS 'start',
		UNIX_TIMESTAMP(`s`.`expires`) AS 'end',
		`l`.`surveyls_title` AS 'title', `l`.`surveyls_description` AS 'desc'
	FROM	`".$db->real_escape_string(DB_getSurveysTable())."` AS `s`
	LEFT JOIN `".$db->real_escape_string(DB_getSurveysLangTable())."` AS `l` ON (`l`.`surveyls_survey_id` = `s`.`sid`)
	WHERE	`s`.`sid` = " . intval($id) . " LIMIT 0,1");

if ($rslt == null || !($row = $rslt->fetch_object())) {
	$smarty->display("surveynotfound.html.tpl");
	exit;
}

if ($row->active == "Y" && $row->end < time()) {
	if ($db->query("
		UPDATE `".$db->real_escape_string(DB_getSurveysTable())."`
		SET `active` = 'N' WHERE `sid` = ".intval($id)) )
	{
		logmessage($id, "Die Umfrage wurde durch erreichen der Zeitgrenze erreicht.");
		generateAuswertung($db, $id);
	}
}

$rslt = $db->query("SELECT COUNT(`id`) AS 'votes' FROM `".$db->real_escape_string(DB_getResultTable($id))."`");
$votesrow = @$rslt->fetch_object();

$votes = $votesrow->votes;

$tokens = 0;
if ($row->usetokens == "Y") {
	$trslt = $db->query("
		SELECT	COUNT(`t`.`tid`) AS 'tokens'
		FROM	`".$db->real_escape_string(DB_getTokenTable($id))."` AS `t`");
	$trow = $trslt->fetch_object();
	$tokens = $trow->tokens;
}

$smarty->assign("id", $id);
$smarty->assign("title", $row->title);
$smarty->assign("start", $row->start);
$smarty->assign("end", $row->end);
$smarty->assign("desc", $row->desc);
$smarty->assign("votes", $votes);
$smarty->assign("tokens", $tokens);
$smarty->assign("active", $row->active == "Y");
$smarty->assign("usetokens", $row->usetokens == "Y");

// Verlinke eine evtl. vorhandenes Log
if (file_exists(getlogpath($id))) {
	$smarty->assign("loglink", getloglink($id));
}
// Verlinke eine evtl. vorhandene Tokenlist
if (file_exists(getstatpath_tokenlist($id))) {
	$smarty->assign("tokenlist", getstatlink_tokenlist($id));
}

$smarty->display("auswertung.html.tpl");

?>
