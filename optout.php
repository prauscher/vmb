<?php

require_once("config.inc.php");
require_once("mysql.inc.php");
$db = DB_getLink();

require_once("smarty.inc.php");
$smarty = getSmarty();

$token = (isset($_REQUEST["token"]) ? stripslashes($_REQUEST["token"]) : null);
$ip = $config->remoteip;

checkIP($db, $smarty, $ip);

$rslt = $db->query("
	SELECT	`b`.`mailhash` AS 'mailhash', `b`.`sendmail` AS 'sendmail'
	FROM	`".$db->real_escape_string(DB_getBlacklistTokenTable())."` AS `bt`
	LEFT JOIN `".$db->real_escape_string(DB_getBlacklistTable())."` AS `b`
		ON (`bt`.`mailhash` = `b`.`mailhash`)
	WHERE	`bt`.`token` = '".$db->real_escape_string($token)."' AND
		`bt`.`validtill` >= NOW()");

if ($rslt == false || !($row = $rslt->fetch_object())) {
	increase_ipcount($db, $ip);
	$smarty->display("optouttokeninvalid.html.tpl");
	exit;
}

if ($rslt->num_rows > 1) {
	$smarty->display("optouttokenambiguous.html.tpl");
	exit;
}

$mailhash = $row->mailhash;
$sendmail = ($row->sendmail == 'Y');

if (isset($_POST["optout"])) {
	$mail = stripslashes($_POST["mail"]);
	
	if (getMailhash($mail) != $mailhash) {
		$smarty->assign("mailinvalid", true);
	} elseif (! $sendmail) {
		$smarty->assign("alreadyoptouted", true);
	} else {
		if ($db->query("UPDATE `".$db->real_escape_string(DB_getBlacklistTable())."` SET `sendmail` = 'N' WHERE `mailhash` = '".$db->real_escape_string($mailhash)."'")) {
			// Noch mal den User informieren (Anti-Cracker und so)
			mail($mail, $config->optout_subject, $config->optout_body, $config->optout_header);

			$smarty->display("optoutperformed.html.tpl");
			exit;
		}
		$smarty->assign("couldnotoptout", true);
	}
}

$smarty->assign("sendmail", $sendmail);
$smarty->assign("token", $token);
$smarty->display("optoutform.html.tpl");

?>
