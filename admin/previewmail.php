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

function decodeRFC2045($text, $charset) {
	preg_match_all('#=\?([-a-zA-Z0-9_]+)\?([QqBb])\?(.*)\?=(\s|$)#U', $text, $matches, PREG_SET_ORDER);
	foreach ($matches AS $m) {
		switch (strtolower($m[2])) {
		case 'b':
			$m[3] = imap_base64($m[3]);
			break;
		case 'q':
			$m[3] = str_replace("_", " ", imap_qprint($m[3]));
			break;
		default:
			// prohibited by RFC2045
			continue;
		}
		$text = str_replace($m[0], iconv($m[1], $charset, $m[3]), $text);
	}
	return $text;
}

$rslt = $db->query("
	SELECT	`l`.`surveyls_title` AS 'title', `l`.`surveyls_description` AS 'desc',
		UNIX_TIMESTAMP(`s`.`startdate`) AS 'start', UNIX_TIMESTAMP(`s`.`expires`) AS 'expire'
	FROM	`".$db->real_escape_string(DB_getSurveysLangTable())."` AS `l`
	LEFT JOIN `".$db->real_escape_string(DB_getSurveysTable())."` AS `s` ON (`l`.`surveyls_survey_id` = `s`.`sid`)
	WHERE	`surveyls_survey_id` = " . intval($id) . " AND `surveyls_language` = '".$db->real_escape_string($lang)."'");
if (!$row = @$rslt->fetch_object()) {
	die("<p>Umfrage nicht gefunden :'(</p>");
}

$title = $row->title;
$desc = $row->desc;
$start = $row->start;
$expire = $row->expire;

prepareMail($db, $id, $lang, $title, $desc, $start, $expire, $token, $optouttoken, $body, $header, $subject);

echo "<pre style=\"font-weight:bold;\">".htmlentities2(decodeRFC2045($subject, $config->charset))."</pre>";
echo "<pre style=\"font-style:italic;\">".htmlentities2($header)."</pre>";
echo "<pre class=\"body\">".htmlentities2($body)."</pre>";

echo "<p><a href=\"composemail.php?id=".$idlang."\">Korrigieren</a></p>\n";
echo "<p><a href=\"sendmail.php?id=".$idlang."\">Mails verschicken</a></p>\n";

?>
