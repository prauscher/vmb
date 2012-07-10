<?php

/**
 * Ein Kommentar fuer die Hessen:
 *	PENIS!
 */

require_once("smarty.inc.php");
$smarty = getSmarty();

require_once("mysql.inc.php");
$db = DB_getLink();

$host = stripslashes($_SERVER["HTTP_HOST"]);

$rslt = $db->query("
	SELECT	`l`.`surveyls_title` AS 'title', `s`.`active` AS 'active', `s`.`usetokens` AS 'usetokens',
		UNIX_TIMESTAMP(`s`.`startdate`) AS 'start',
		UNIX_TIMESTAMP(`s`.`expires`) AS 'end',
		`s`.`sid` AS `id`, `l`.`surveyls_language` AS `lang`
	FROM	`".$db->real_escape_string(DB_getSurveysLangTable())."` AS `l`
	LEFT JOIN `".$db->real_escape_string(DB_getSurveysTable())."` AS `s` ON (`l`.`surveyls_survey_id` = `s`.`sid`)
	WHERE	(`s`.`admin` = '".$db->real_escape_string($host)."' AND `s`.`private` = 'Y') OR `s`.`private` = 'N'
	ORDER BY `s`.`datecreated` DESC");
$surveys = array();
while ($row = $rslt->fetch_object()) {
	$survey = array();
	$survey["title"] = $row->title;
	$survey["start"] = $row->start;
	$survey["end"] = $row->end;
	$survey["id"] = $row->id;
	$survey["lang"] = $row->lang;
	$survey["active"] = ($row->active == "Y");
	$survey["usetokens"] = ($row->usetokens == "Y");
	
	$surveys[] = $survey;
}
$smarty->assign("surveys", $surveys);

$smarty->display("index.html.tpl");

?>
