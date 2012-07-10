<?php

require_once("../config.inc.php");
require_once("../library.inc.php");
require_once("../mysql.inc.php");
require_once("session.inc.php");
$db = DB_getLink();

$idlang = (isset($_REQUEST["id"]) ? stripslashes($_REQUEST["id"]) : null);
list($id, $lang) = explode(":", $idlang);
$id = intval($id);

$host = stripslashes($_SERVER["HTTP_HOST"]);
do_sanity_check_admin($db, $id, $host);

$rslt = $db->query("
	SELECT	`l`.`surveyls_title` AS 'title', `l`.`surveyls_description` AS 'desc',
		UNIX_TIMESTAMP(`s`.`startdate`) AS 'start', UNIX_TIMESTAMP(`s`.`expires`) AS 'expire'
	FROM	`".$db->real_escape_string(DB_getSurveysLangTable())."` AS `l`
	LEFT JOIN `".$db->real_escape_string(DB_getSurveysTable())."` AS `s`
		ON (`l`.`surveyls_survey_id` = `s`.`sid`)
	WHERE	`surveyls_survey_id` = " . intval($id) . " AND `surveyls_language` = '".$db->real_escape_string($lang)."'");
if (!$row = @$rslt->fetch_object()) {
	die("<p>Umfrage nicht gefunden :'(</p>");
}

$title = $row->title;
$desc = $row->desc;
$start = $row->start;
$expire = $row->expire;

if (!is_numeric($config->bulkmailcount) || $config->bulkmailcount <= 0) {
	$config->bulkmailcount = 30;
}

$rslt = $db->query("SELECT `t`.`tid` AS 'tid', `t`.`email` AS 'email' FROM `".DB_getTokenTable($id)."` AS `t` WHERE `sent` = 'N' AND `language` = '" . $db->real_escape_string($lang) . "' LIMIT 0,".intval($config->bulkmailcount));
$c = 0;
while ($row = $rslt->fetch_object()) {
	if (empty($row->tid) || empty($row->email)) {
		continue;
	}
	// Das Magische Vorbereitungsscript ;)
	prepareMail($db, $id, $lang, $title, $desc, $start, $expire, $token, $optouttoken, $body, $header, $subject);

	// Verschicke die Mail und im anonymisiere den Eintrag gleich mit jupis@invalid
	if ($db->query("INSERT INTO `".DB_getBlacklistTokenTable()."` (`mailhash`, `token`, `validtill`) VALUES ('".$db->real_escape_string(getMailhash($row->email))."', '".$db->real_escape_string($optouttoken)."', '".$db->real_escape_string(date("Y-m-d", time() + $config->optoutdays*24*60*60))."')")
	  && mail($row->email, $subject, $body, $header)
	  && $db->query("
		UPDATE	`".DB_getTokenTable($id)."`
		SET	`sent` = NOW(), `email` = 'jupis@invalid', `token` = '".$db->real_escape_string($token)."'
		WHERE	`tid` = ".intval($row->tid)))
	{
		$c++;
	}
}

echo "<p><b>Habe $c Mails verschickt und anonymisiert.</b></p>\n";
if ($c > 0) {
	logmessage($id, $_SERVER["PHP_AUTH_USER"] . " hat Token an $c Adressen verschickt.");
	echo "<p><a href=\"sendmail.php?id=".$idlang."\">Weiter!</a></p>\n";
} else {
	echo "<p><a href=\"activatesurvey.php?id=".$idlang."\">Umfrage aktivieren</a></p>";
}

?>
