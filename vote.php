<?php

require_once("smarty.inc.php");
$smarty = getSmarty();

require_once("mysql.inc.php");
$db = DB_getLink();

$id = (isset($_REQUEST["sid"]) ? stripslashes($_REQUEST["sid"]) : null);
$lang = (isset($_REQUEST["lang"]) ? stripslashes($_REQUEST["lang"]) : null);
$token = (isset($_REQUEST["token"]) ? stripslashes($_REQUEST["token"]) : null);

checkIP($db, $smarty, $config->remoteip);

$rslt = $db->query("
	SELECT	`s`.`active` AS 'active', `s`.`usetokens` AS 'usetokens',
		`l`.`surveyls_title` AS 'title', `l`.`surveyls_description` AS 'desc',
		UNIX_TIMESTAMP(`s`.`startdate`) AS 'start',
		UNIX_TIMESTAMP(`s`.`expires`) AS 'end'
	FROM	`".$db->real_escape_string(DB_getSurveysTable())."` AS `s`
	LEFT JOIN `".$db->real_escape_string(DB_getSurveysLangTable())."` AS `l`
		ON (`l`.`surveyls_survey_id` = `s`.`sid`)
	WHERE `s`.`sid` = " . intval($id) . " && `l`.`surveyls_language` = '".$db->real_escape_string($lang)."'
	LIMIT 0,1");

if ($rslt == false || !($row = $rslt->fetch_object())) {
	$smarty->display("surveynotfound.html.tpl");
	exit;
}

$smarty->assign("title", $row->title);
$smarty->assign("start", $row->start);
$smarty->assign("end", $row->end);
$smarty->assign("desc", $row->desc);
$smarty->assign("id", $id);
$smarty->assign("lang", $lang);
$smarty->assign("token", $token);
$smarty->assign("active", $row->active == "Y");
$usetokens = ($row->usetokens == "Y");

/* Umfrage beendet? */
if ($row->active != "Y"
 || ($row->end !== null && $row->end < time()) ) {
	$smarty->display("surveyinactive.html.tpl");
	exit;
}

/* Token gueltig? */
if ($usetokens) {
	$rslt = $db->query("
		SELECT	1
		FROM	`".$db->real_escape_string(DB_getTokenTable($id))."`
		WHERE	`token` = '".$db->real_escape_string($token)."'");
	if (! $rslt->fetch_object()) {
		increase_ipcount($db, $ip);
		$smarty->display("tokeninvalid.html.tpl");
		exit;
	}
}

$smarty->assign("auswertunglink", "auswertung.php?id=".intval($id));
$smarty->assign("votelink", "vote.php?sid=".intval($id)."&lang=".urlencode($lang).(!empty($token) ? "&token=".urlencode($token) : ""));

/* Speichern */
if (isset($_POST["save"])) {
	$qrslt = $db->query("
		SELECT	CONCAT(`sid`,'X',`gid`,'X',`qid`) AS 'feldname', `qid` AS 'qid', `type` AS 'type'
		FROM	`".$db->real_escape_string(DB_getQuestionsTable())."`
		WHERE	`sid` = ".intval($id)." AND `language` = '".$db->real_escape_string($lang)."' AND `type` IN ('L', 'M')");
	$updatefelder = array();
	$insertfelder = array();
	while ($qrow = $qrslt->fetch_object()) {
		if ($qrow->type == 'L' && isset($_POST[$qrow->feldname])) {
			$updatefelder[] = "`".$db->real_escape_string($qrow->feldname)."` = '".$db->real_escape_string(stripslashes($_POST[$qrow->feldname]))."'";
			$insertfelder["`".$db->real_escape_string($qrow->feldname)."`"] = "'".$db->real_escape_string(stripslashes($_POST[$qrow->feldname]))."'";
		}
		if ($qrow->type == 'M') {
			if (!is_array($_POST[$qrow->feldname])) {
				// Sonst laeuft in_array spaeter amok!
				$_POST[$qrow->feldname] = array();
			}
			$arslt = $db->query("
				SELECT	`code` AS 'code'
				FROM	`".$db->real_escape_string(DB_getAnswersTable())."`
				WHERE	`qid` = ".intval($qrow->qid)." AND `language` = '".$db->real_escape_string($lang)."'");
			while ($arow = $arslt->fetch_object()) {
				$updatefelder[] = "`".$db->real_escape_string($qrow->feldname.$arow->code)."` = '".(in_array($arow->code, $_POST[$qrow->feldname]) ? "Y" : "N")."'";
				$insertfelder["`".$db->real_escape_string($qrow->feldname.$arow->code)."`"] = "'".(in_array($arow->code, $_POST[$qrow->feldname]) ? "Y" : "N")."'";
			}
		}
	}
	
	// Mindestens eine Frage brauchen wir schon
	if (count($updatefelder) <= 0) {
		die("This should never happen!");
	}
	
	if ($usetokens) {
		$db->query("
			UPDATE	`".$db->real_escape_string(DB_getResultTable($id))."`
			SET	`submitdate` = NOW(), ".implode(", ", $updatefelder)."
			WHERE	`token` = '".$db->real_escape_string($token)."'");
	}
	// Falls UPDATE nicht erfolgreich war, existierte die Zeile vermutlich noch nicht
	if ($db->affected_rows == 0 || !$usetokens) {
		$db->query("
			INSERT INTO `".$db->real_escape_string(DB_getResultTable($id))."`
				(`submitdate`, `lastpage`, `startlanguage`" .
				($usetokens == "Y" ? ", `token`" : "").",
				".implode(", ", array_keys($insertfelder)).")
			VALUES	(NOW(), 2, '".$db->real_escape_string($lang)."'" .
				($usetokens == "Y" ? ", '".$db->real_escape_string($token)."'" : "").",
				".implode(", ", array_values($insertfelder)).")");
	}
	// Entweder UPDATE oder INSERT hats wohl erwischt ;)
	if ($db->affected_rows == 1) {
		$smarty->assign("votecounted",1);
	} else {
		$smarty->assign("error_couldnotcountvote",1);
	}
}

/* Evtl vorhandene Zwischenergebnisse laden */
if ($usetokens) {	
	$rslt = $db->query("
		SELECT	*
		FROM	`".$db->real_escape_string(DB_getResultTable($id))."`
		WHERE	`token` = '".$db->real_escape_string($token)."'");
	if (! $options = $rslt->fetch_assoc()) {
		$options = array();
	} else {
		$smarty->assign("loadoldresults",1);
	}
}

/* Lade die Fragen */
$rslt = $db->query("
	SELECT	CONCAT(`sid`,'X',`gid`,'X',`qid`) AS 'feldname', `qid` AS `qid`, `title` AS 'title', `type` AS 'type', `question` AS 'q'
	FROM	`".$db->real_escape_string(DB_getQuestionsTable())."`
	WHERE	`sid` = ".intval($id)." AND `language` = '".$db->real_escape_string($lang)."' AND `type` IN ('L', 'M')
	ORDER BY `question_order`");
$questions = array();
while ($row = $rslt->fetch_object()) {
	$question = array();
	$question["name"] = $row->feldname;
	$question["value"] = (isset($options[$row->feldname]) ? $options[$row->feldname] : null);
	$question["title"] = $row->q;
	if ($row->type == "M") {
		$question["multiselect"] = true;
		$question["selectedoptions"] = array();
	}
	
	$arslt = $db->query("
		SELECT	`code`, `answer` AS `a`
		FROM	`".$db->real_escape_string(DB_getAnswersTable())."`
		WHERE	`qid` = ".intval($row->qid)." AND `language` = '".$db->real_escape_string($lang)."'
		ORDER BY `sortorder`");
	while ($arow = $arslt->fetch_object()) {
		if (isset($options[$row->feldname.$arow->code]) && $options[$row->feldname.$arow->code] == 'Y') {
			$question["selectedoptions"][] = $arow->code;
		}
		$question["options"][$arow->code] = $arow->a;
	}
	$questions[] = $question;
}

$smarty->assign("questions", $questions);

$smarty->display("vote.html.tpl");

?>
