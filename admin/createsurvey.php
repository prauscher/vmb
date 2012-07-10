<?php

require_once("../library.inc.php");
require_once("../mysql.inc.php");
$db = DB_getLink();

function destrftime($format, $str) {
	$a = strptime($str, $format);
	return mktime($a["tm_hour"],$a["tm_min"],$a["tm_sec"],$a["tm_mon"]+1,$a["tm_mday"],$a["tm_year"]+1900);
}

$title = (isset($_POST["title"]) ? stripslashes($_POST["title"]) : null);
$start = (!empty($_POST["start"]) ? destrftime($config->strftimeformat,stripslashes($_POST["start"])) : null);
$end = (!empty($_POST["end"]) ? destrftime($config->strftimeformat,stripslashes($_POST["end"])) : null);
$desc = (isset($_POST["desc"]) ? stripslashes($_POST["desc"]) : null);
$options = (isset($_POST["options"]) ? explode("\n", stripslashes($_POST["options"])) : null);
$host = stripslashes($_SERVER["HTTP_HOST"]);
$multiselect = isset($_POST["multiselect"]);
$usetokens = isset($_POST["usetokens"]);
$private = ($host == $config->adminhost) ? isset($_POST["private"]) : true;
// Oder werden wir jemals was anderes brauchen? :D
$lang = "de-informal";

if (empty($title)) {
	die("<p>Titel leer oO</p>");
}

if ($start !== null && ($start < time()-5*24*60*60 || $start > time()+20*24*60*60)) {
	die("<p>Startdatum ung&uuml;ltig!</p>");
}

if ($end !== null && ($end < time()-24*60*60 || $end > time()+60*24*60*60)) {
	die("<p>Enddatum ung&uuml;ltig!</p>");
}

if (empty($desc)) {
	die("<p>Beschreibung leer oO</p>");
}

if (count($options) < 2) {
	die("<p>Weniger als 2 Optionen oO</p>");
}

function gensafename($str, $len) {
	return substr(rand(10,99).preg_replace("$[^0-9a-z]$", "_", strtolower($str)),0,$len);
}

/** ID schon belegt? **/
do {
	$id = rand(10000,99999);
	$rslt = $db->query("
		SELECT 1
		FROM	`".$db->real_escape_string(DB_getSurveysTable())."`
		WHERE	`sid` = " . intval($id));
} while ($rslt->fetch_row());

/** Umfrage einfuegen **/
if (!$db->query("
	INSERT INTO `".$db->real_escape_string(DB_getSurveysTable())."`
		(`sid`, `language`, `datecreated`, `startdate`, `expires`, `tokenanswerspersistence`, `usetokens`, `format`, `admin`, `private`)
	VALUES	(".intval($id).", '".$db->real_escape_string($lang)."', NOW(), ".($start === null ? "NULL" : "'".date("Y-m-d H:i:s",$start)."'") . ", ".($end === null ? "NULL" : "'".date("Y-m-d H:i:s",$end)."'") . ", 'Y', '".($usetokens ? "Y" : "N")."', 'G', '".$db->real_escape_string($host)."', '".($private ? "Y" : "N")."')"))
{
	die("<p>Konnte Umfrage nicht einfuegen!</p>");
}

if (!$db->query("
	INSERT INTO `".$db->real_escape_string(DB_getSurveysLangTable())."`
		(`surveyls_survey_id`, `surveyls_language`, `surveyls_title`, `surveyls_description`)
	VALUES	(".intval($id).", '".$db->real_escape_string($lang)."', '".$db->real_escape_string($title)."', '".$db->real_escape_string($desc)."')"))
{
	die("<p>Konnte Umfragetexte nicht speichern!</p>");
}

/** QuestionGroup erstellen **/
if (!$db->query("
	INSERT INTO `".$db->real_escape_string(DB_getGroupsTable())."`
		(`sid`, `language`)
	VALUES	(".intval($id).", '".$db->real_escape_string($lang)."')"))
{
	die("<p>Konnte Umfragegruppe nicht einfuegen!</p>");
}
$gid = $db->insert_id;

/** Frage einfuegen **/
if (!$db->query("
	INSERT INTO `".$db->real_escape_string(DB_getQuestionsTable())."`
		(`sid`, `language`, `type`, `gid`, `title`, `question`)
	VALUES	(".intval($id).", '".$db->real_escape_string($lang)."', '".($multiselect ? "M" : "L")."', '".intval($gid)."', '".$db->real_escape_string(gensafename($title,20))."', '".$db->real_escape_string($title)."')"))
{
	die("<p>Konnte Frage nicht einfuegen!</p>");
}
$qid = $db->insert_id;

/* Antworten einfuegen */
foreach ($options AS $i => $o) {
	$name = gensafename($o,5);
	$options[$name] = $o;
	unset($options[$i]);
	if (!$db->query("
		INSERT INTO `".$db->real_escape_string(DB_getAnswersTable())."`
			(`qid`, `code`, `answer`, `sortorder`, `language`)
		VALUES	(".intval($qid).", '".$db->real_escape_string($name)."', '".$db->real_escape_string(trim($o))."', ".intval($i+1).", '".$db->real_escape_string($lang)."')"))
	{
		die("<p>Konnte Antwort &quot;".trim($o)."&quot; nicht einfuegen!</p>");
	}
}

/* SurveyTabelle erstellen */
function getResultFields($fieldname, $multiselect, $options) {
	global $db;
	if ($multiselect) {
		$sql = "";
		foreach ($options AS $name => $o) {
			$sql .= "`".$db->real_escape_string($fieldname.$name)."` VARCHAR(5) DEFAULT NULL,\n";
		}
		return $sql;
	}
	return "`".$db->real_escape_string($fieldname)."` VARCHAR(5) DEFAULT NULL,";
}
if (!$db->query("CREATE TABLE `".$db->real_escape_string(DB_getResultTable($id))."` (
		`id` INT NOT NULL AUTO_INCREMENT,
		`submitdate` datetime,
		`lastpage` INT DEFAULT NULL,
		`startlanguage` VARCHAR(20) NOT NULL,
		`token` VARCHAR( 36 ) DEFAULT NULL,
		".getResultFields($id."X".$gid."X".$qid, $multiselect, $options)."
		PRIMARY KEY (`id`) )"))
{
	die("<p>Konnte Antworttabelle nicht anlegen!</p>");
}

/* TokenTabelle erstellen */
if ($usetokens) {
	if (!$db->query("
		CREATE TABLE `".$db->real_escape_string(DB_getTokenTable($id))."` (
			`tid` INT NOT NULL AUTO_INCREMENT,
			`firstname` varchar(40) DEFAULT NULL,
			`lastname` varchar(40) DEFAULT NULL,
			`email` text,
			`emailstatus` text,
			`token` varchar(36) DEFAULT NULL,
			`language` varchar(25) DEFAULT NULL,
			`sent` varchar(17) DEFAULT 'N',
			`remindersent` varchar(17) DEFAULT 'N',
			`remindercount` INT DEFAULT '0',
			`completed` varchar(17) DEFAULT 'N',
			`validfrom` datetime,
			`validuntil` datetime,
			`mpid` INT,
			PRIMARY KEY (`tid`),
			KEY `token` (`token`) )"))
	{
		die("<p>Konnte Tokentabelle nicht anlegen!</p>");
	}
}

logmessage($id, $_SERVER["PHP_AUTH_USER"] . " hat die Umfrage erstellt.");

echo "<p>Uff! Vorbereitungen erfolgreich abgeschlossen!</p>";
echo '<p><a href="insertmails.php?id='.$id.':'.$lang.'">Mailadressen einfuegen</a></p>';

?>
