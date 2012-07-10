<?php

require_once("../library.inc.php");

require_once("../mysql.inc.php");
$db = DB_getLink();

$host = stripslashes($_SERVER["HTTP_HOST"]);

$rslt = $db->query("
	SELECT	`s`.`sid` AS 'id', `s`.`active` AS 'active',
		`l`.`surveyls_title` AS 'title', `l`.`surveyls_language` AS 'lang'
	FROM	`".$db->real_escape_string(DB_getSurveysLangTable())."` AS `l`
	LEFT JOIN `".$db->real_escape_string(DB_getSurveysTable())."` AS `s` ON (`s`.`sid` = `l`.`surveyls_survey_id`)
	WHERE	   `s`.`admin` = '".$db->real_escape_string($host)."'
		OR '".$db->real_escape_string($host)."' = '".$db->real_escape_string($config->adminhost)."'");
$tokentables = array();
$c = 0;
while ($row = $rslt->fetch_assoc()) {
	$tokentables[intval($row['id'])]["id"] = intval($row['id']);
	$tokentables[intval($row['id'])]["active"] = ($row["active"] == 'Y');
	$tokentables[intval($row['id'])]["langs"][$row['lang']] = array("lang" => $row['lang'], "title" => $row['title']);
}

echo "<p>Angemeldet als ".$_SERVER["PHP_AUTH_USER"]."</p>";
echo "<p><a href=\"editsessionmails.php\">Mailadressen voreintragen</a></p>";
if ($host == $config->adminhost) {
	echo "<p><a href=\"manageblacklist.php\">Blacklist verwalten</a></p>";
}

if (isset($_POST["do"])) {
	$idlang = stripslashes($_POST["id"]);
	list($id,$lang) = explode(":", $idlang);
	
	// Kurze Kontrolle, ob dieser Eintrag vorhanden ist ;)
	if (isset($tokentables[$id]["langs"][$lang])) {	
		echo "<p><a href=\"insertmails.php?id=".$idlang."\">Adressen eintragen</a></p>";
		echo "<p><a href=\"composemail.php?id=".$idlang."\">Mails verschicken</a></p>";
		if ($tokentables[$id]["active"]) {
			echo "<p><a href=\"closesurvey.php?id=".$idlang."\">Umfrage beenden</a></p>";
		} else {
			echo "<p><a href=\"activatesurvey.php?id=".$idlang."\">Umfrage aktivieren</a></p>";
			echo "<p><a href=\"../auswertung.php?id=".$id."\">Auswertung anzeigen</a></p>";
		}
		echo "<p><a href=\"dropsurvey.php?id=".$idlang."\" onClick=\"return confirm('Sicher?')\">Umfrage l&ouml;schen</a></p>";
		exit;
	}
}

?>
<form action="" method="post">
	<fieldset>
		<p style="font-weight: bold;">F&uuml;r welche Umfrage?</p>
		<table border=1 cellpadding=5 cellspacing=0>
<?php
foreach ($tokentables AS $t) {
	foreach ($t["langs"] AS $l) {
		echo "<tr><td><input type=\"radio\" name=\"id\" value=\"" . $t["id"] . ":" . $l['lang'] . "\" />";
		echo "</td><td>" . $l["title"] . "</td></tr>";
	}
}
?>
		</table>
		<input type="submit" name="do" value="weiter" />
	</fieldset>
</form>
<p><b>Alternativ: <a href="composesurvey.php">Umfrage erstellen</a></b></p>
