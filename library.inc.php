<?php

require_once("config.inc.php");

if (!headers_sent()) {
	header("Content-Type: text/html; Charset=".$config->charset);
}

function htmlentities2($text) {
	global $config;
	return iconv("ISO-8859-1", $config->charset, htmlentities(iconv($config->charset, "ISO-8859-1", $text),ENT_QUOTES,"ISO-8859-1"));
}

function logmessage($sid, $msg) {
	$fp = fopen(getlogpath($sid),"a");
	fputs($fp,date("(Y-m-d H:i:s)")." - ".$msg."\n");
	fclose($fp);
}

/*
 * IP-Ban
 */

function checkIP($db, $smarty, $ip) {
	$sql = "SELECT UNIX_TIMESTAMP(`banTill`) AS 'banTill' FROM `".DB_getIPTable()."` WHERE `ip` = '".addslashes($ip)."' AND `banTill` > NOW()";
	$rslt = $db->query($sql);
	if ($rslt->num_rows > 0) {
		$row = $rslt->fetch_assoc();
		$smarty->assign("banTill", $row["banTill"]);
		$smarty->assign("ip", $ip);
		$smarty->display("banned.html.tpl");
		exit;
	}
}

function increase_ipcount($db, $ip) {
	$sql = "SELECT `wrongTokenCount` FROM `".DB_getIPTable()."` WHERE `ip` = '".addslashes($ip)."'";
	$rslt = $db->query($sql);
	if ($rslt->num_rows > 0) {
		$row = $rslt->fetch_assoc();
		if ($row["wrongTokenCount"] > 3) {
			$sql = "UPDATE `".DB_getIPTable()."` SET `banTill` = DATE_ADD(NOW(), INTERVAL 7 DAYS)";
		} else {
			$sql = "UPDATE `".DB_getIPTable()."` SET `wrongTokenCount` = `wrongTokenCount` + 1";
		}
	} else {
		$sql = "UPDATE `".DB_getIPTable()."` (`ip`) VALUES ('".addslashes($ip)."')";
	}
	$db->query($sql);
}

/*
 * Link & Pfadgenerierung (link => extern ; pfad => server-intern)
 */

function getlink($file) {
	global $config;
	return $config->link . $file;
}

function getloglink($sid) {
	return getlink("statistiken/{$sid}.log.txt");
}

function getlogpath($sid) {
	return dirname(__FILE__)."/statistiken/{$sid}.log.txt";
}

function getstatlink($sid) {
	return getlink("statistiken/{$sid}.html");
}

function getstatpath($sid) {
	return dirname(__FILE__)."/statistiken/{$sid}.html";
}

function getstatlink_graph($feldname) {
	return getlink("statistiken/{$feldname}.png");
}

function getstatpath_graph($feldname) {
	return dirname(__FILE__)."/statistiken/{$feldname}.png";
}

function getstatlink_fullgraph($feldname) {
	return getlink("statistiken/{$feldname}.full.png");
}

function getstatpath_fullgraph($feldname) {
	return dirname(__FILE__)."/statistiken/{$feldname}.full.png";
}

function getstatlink_optimg($feldname, $opt) {
	return getlink("statistiken/{$feldname}-{$opt}.png");
}

function getstatpath_optimg($feldname, $opt) {
	return dirname(__FILE__)."/statistiken/{$feldname}-{$opt}.png";
}

function getstatlink_tokenlist($sid) {
	return getlink("statistiken/{$sid}.token.html");
}

function getstatpath_tokenlist($sid) {
	return dirname(__FILE__)."/statistiken/{$sid}.token.html";
}

/*
 * Zum Optionssortieren
 */

function compareOptionsByVotes($a, $b) {
	return $a["votes"] < $b["votes"];
}

function sortOptions(&$options) {
	return uasort($options, "compareOptionsByVotes");
}

/**
 * 
 **/

function do_sanity_check_admin($db, $id, $host) {
	global $config;
	$rslt = $db->query("
		SELECT	`s`.`active` AS 'active', `s`.`admin` AS 'admin'
		FROM	`".DB_getSurveysTable()."` AS `s`
		WHERE	`s`.`sid` = ".intval($id) );
	if (! $row = $rslt->fetch_object()) {
		die("<b>Kann die Umfrage nicht finden.</b>");
	}
	if ($row->admin != $host and $host != $config->adminhost) {
		die("<p>Permission denied, Pisser!</p>");
	}
	return $row;
}

/*
 * Mails schreiben
 */

function gentoken($len = 20) {
	$words = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789*?=/&%!"\'$(){}[]#-_.,:;<>|';
	$token = "";
	for ($i=0;$i<$len;$i++) {
		$token .= substr($words,rand(0,strlen($words)-1),1);
	}
	return $token;
}

function doFilter($text, $vars) {
	return str_replace(array_keys($vars), array_values($vars), $text);
}

function getMailhash($mail) {
	return md5(strtolower(trim($mail)));
}

/*
 * Wir geben die Token hier byRef zurueck
 */
function prepareMail($db, $id, $lang, $title, $desc, $start, $expire, &$token, &$optouttoken, &$body, &$header, &$subject) {
	global $config;

	$token = gentoken();
	$optouttoken = gentoken();
	$vars = array();
	$vars["{SURVEYNAME}"]		= $title;
	$vars["{SURVEYDESCRIPTION}"]	= $desc;
	$vars["{TOKEN}"]		= $token;
	$vars["{OPTOUTLINK}"]		= getlink("optout.php?token=".urlencode($optouttoken)."&");
	$vars["{START}"]		= strftime($config->strftimeformat, $start);
	$vars["{EXPIRE}"]		= strftime($config->strftimeformat, $expire);
	$vars["{SURVEYURL}"]		= getlink("vote.php?sid=".intval($id)."&token=".urlencode($token)."&lang=".urlencode($lang)."&");
	$vars["{RESULTURL}"]		= getlink("auswertung.php?id=".intval($id)."&");
	$vars["{OPTOUTDAYS}"]		= $config->optoutdays;

	$body	 = doFilter(getSessionValue($id.":".$lang, "body",	$db), $vars);
	$subject = doFilter(getSessionValue($id.":".$lang, "subject",	$db), $vars);
	$header	 = doFilter(getSessionValue($id.":".$lang, "header",	$db), $vars);

	// Benutze auch im Header unseren Zeichensatz
	mb_internal_encoding($config->charset);
	$subject = mb_encode_mimeheader($subject, $config->charset);

	/* Zu dem CRLF ... wenn ich einen regulaeren \r\n benutze, machen
	 * manche Webmailer & POP3-Dienste Probleme.
	 */
	$subject = preg_replace("$\r?\n$","\n",$subject);

	/* RFC 2822: "Each line of characters MUST be no more than 998
	 * characters, and SHOULD be no more than 78 characters, excluding
	 * the CRLF."
	 */
	$body = preg_replace("$\r?\n$","\n",$body);
	$body = wordwrap($body,998,"\n",true);
	$body = wordwrap($body,78,"\n");

	/* RFC 2822 verlangt \r\n als Zeilentrenner im Header
	 * Notiz von http://www.php.net/mail :
	 * If messages are not received, try using a LF (\n) only.
	 * Some poor quality Unix mail transfer agents replace LF by CRLF
	 * automatically (which leads to doubling CR if CRLF is used).
	 * This should be a last resort, as it does not comply with RFC 2822. 
	 */
	$header = preg_replace("$\r?\n$","\n",$header);
}

?>
