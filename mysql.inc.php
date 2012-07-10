<?php

require_once("config.inc.php");

function DB_getLink() {
	global $config;
	$mysqli = new mysqli($config->mysql_host, $config->mysql_user, $config->mysql_pass, $config->mysql_name);
	$mysqli->set_charset($config->charset);
	return $mysqli;
}

function DB_getPrefix() {
	return "lime_";
}

function DB_getIPTable() {
	return DB_getPrefix() . "ips";
}

function DB_getSurveysTable() {
	return DB_getPrefix() . "surveys";
}

function DB_getBlacklistTable() {
	return DB_getPrefix() . "blacklist";
}

function DB_getBlacklistTokenTable() {
	return DB_getPrefix() . "blacklist_tokens";
}

function DB_getSurveysLangTable() {
	return DB_getPrefix() . "surveys_languagesettings";
}

function DB_getGroupsTable() {
	return DB_getPrefix() . "groups";
}

function DB_getQuestionsTable() {
	return DB_getPrefix() . "questions";
}

function DB_getAnswersTable() {
	return DB_getPrefix() . "answers";
}

function DB_getResultTable($id) {
	return DB_getPrefix() . "survey_" . intval($id);
}

function DB_getTokenTable($id) {
	return DB_getPrefix() . "tokens_" . intval($id);
}

function DB_getTable($id) {
	return DB_getPrefix() . "_" . intval($id);
}

?>
