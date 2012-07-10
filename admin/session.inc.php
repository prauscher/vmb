<?php

require_once(dirname(__FILE__) . "/../config.inc.php");

session_start();

function clearSession($id) {
	unset($_SESSION["lime"][$id]);
}

function initSessionByDB($idlang, $db) {
	list($id,$lang) = explode(":", $idlang);
	$id = intval($id);
	$rslt = $db->query("
		SELECT	`surveyls_email_invite_subj` AS 'subject', `surveyls_email_invite` AS 'text'
		FROM	`".$db->real_escape_string(DB_getSurveysLangTable())."`
		WHERE	`surveyls_survey_id` = ".intval($id)." AND `surveyls_language` = '".$db->real_escape_string($lang)."'");
	$row = $rslt->fetch_assoc();
	setSessionValue($idlang,"subject",$row["subject"]);
	// Header & Body sind durch \n\n getrennt. Jedoch kann der Body noch mehr \n\n enthalten
	$parts = explode("\n\n",$row["text"]);
	$header = array_shift($parts);
	$body = implode("\n\n",$parts);
	setSessionValue($idlang,"header",$header);
	setSessionValue($idlang,"body",$body);
}

function setSessionValue($id,$name,$val) {
	$_SESSION["lime"][$id][$name] = $val;
}

function getSessionValue($idlang, $name) {
	if (isset($_SESSION["lime"][$idlang][$name]) && $_SESSION["lime"][$idlang][$name] != null) {
		return $_SESSION["lime"][$idlang][$name];
	}
	
	global $config;
	$defaults = array();
	$defaults["subject"] = $config->default_subject;
	$defaults["header"] = $config->default_header;
	$defaults["body"] = $config->default_body;
	if (isset($defaults[$name])) {
		setSessionValue($idlang,$name,$defaults[$name]);
		return $defaults[$name];
	}
	return null;
}

function addSessionMail($a) {
	if (!isset($_SESSION["lime"]["mails"])) {
		$_SESSION["lime"]["mails"] = array();
	}
	if (trim($a) == "") {
		return;
	}
	$_SESSION["lime"]["mails"][] = trim($a);
}

function getSessionMails() {
	if (!isset($_SESSION["lime"]["mails"])) {
		$_SESSION["lime"]["mails"] = array();
	}
	return $_SESSION["lime"]["mails"];
}

function clearSessionMails() {
	$_SESSION["lime"]["mails"] = array();
}

?>
