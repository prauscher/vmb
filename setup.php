<?php

require_once("mysql.inc.php");
$db = DB_getLink();

if ($db->query("CREATE TABLE IF NOT EXISTS `".DB_getBlacklistTable()."` (
  `mailhash` char(32) NOT NULL,
  `sendmail` char(1) NOT NULL DEFAULT 'Y',
  PRIMARY KEY (`mailhash`)
)")) {
	echo "<p>Habe Blacklist-Tabelle erstellt.</p>";
}

if ($db->query("CREATE TABLE IF NOT EXISTS `".DB_getBlacklistTokenTable()."` (
  `mailhash` char(32) NOT NULL,
  `token` varchar(36) NOT NULL,
  `validtill` DATE NOT NULL,
  PRIMARY KEY (`mailhash`, `token`)
)")) {
	echo "<p>Habe Blacklist-TokenTabelle erstellt.</p>";
}

?>
