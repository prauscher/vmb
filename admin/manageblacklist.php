<?php

require_once("../library.inc.php");
require_once("../mysql.inc.php");
$db = DB_getLink();

$rslt = $db->query("SELECT COUNT(*) AS 'blacklisted' FROM `".$db->real_escape_string(DB_getBlacklistTable())."` WHERE `sendmail` = 'N'");
$row = $rslt->fetch_object();

echo "<p>Momentan werden an {$row->blacklisted} Mailadressen keine Mails verschickt.</p>";

$host = stripslashes($_SERVER["HTTP_HOST"]);

if ($host != $config->adminhost) {
	exit;
}

if (isset($_POST["optin"])) {
	$mail = stripslashes($_POST["mail"]);
	
	if ($db->query("UPDATE `".$db->real_escape_string(DB_getBlacklistTable())."` SET `sendmail` = 'Y' WHERE `mailhash` = '".$db->real_escape_string(getMailhash($mail))."'")) {
		echo "<p>{$mail} freigeschaltet!</p>";
	}
}

?>
<h1>Mailadressen freischalten (Opt-In)</h1>
<form action="" method="post">
 <fieldset>
  <label for="mail">Mailadresse:</label>
  <input type="text" name="mail" onLoad="this.focus();" />
  <input type="submit" name="optin" value="Opt-In" />
 </fieldset>
</form>
