<?php

$config = new stdClass;
$config->remoteip = $_SERVER["REMOTE_ADDR"];

/* AdminHost, der Globale Umfragen verwalten kann. */
$config->adminhost = "localhost:8080";

/* Charset, welches verwendet werden soll */
$config->charset = "UTF-8";

/* Administrator */
$config->adminname = "Administrator";
$config->adminmail = "patrick.rauscher@piratenpartei-hessen.de";
$config->replytomail = "piraten@invalid";

/* Datenbankeinstellungen */
$config->mysql_host = "localhost";
$config->mysql_user = "root";
$config->mysql_pass = "anything92";
$config->mysql_name = "limesurvey";

/* Unser Link. Wichtig in den Mails */
$config->link = "http://192.168.100.166/~prauscher/lime/";

/* Farben in der Statistik */
$config->colors = array("004586", "ff420e", "ffd320", "579d1c", "7e0021", "83caff", "314004", "aecf00", "4b1f6f", "ff950e", "c5000b", "0084d1");

/* Standartwerte fuer die Mails */
$config->default_subject = "[PPH Umfrage] {SURVEYNAME}";
$config->default_header = "From: {$config->adminname} <{$config->adminmail}>\r\nReply-To: {$config->replytomail}\r\nContent-Type: text/plain; Charset={$config->charset}";
$config->default_body = "Ahoi!\n\n{SURVEYDESCRIPTION}\n\nAb dem {START} kannst du unter {SURVEYURL} abstimmen.\nDein Token lautet {TOKEN}, anhand dessen (und der Abstimmungsliste) kannst du am Ende nachvollziehen, ob deine Stimme erfolgreich gewertet wurde.\nDie Umfrage endet am {EXPIRE}. Ab dann kannst du unter {RESULTURL} das Ergebnis einsehen.\nFalls du keine weiteren Mails zu Meinungsbildern erhalten möchtest, klicke bitte auf {OPTOUTLINK}. Dieser Link ist nur {OPTOUTDAYS} Tage gültig!\nWichtig: Bitte antworte nicht auf diese Mail, sondern schicke Nachfragen zur Umfrage an {$config->adminmail}.\n\nKlarmachen zum Ändern!\n";

/* OptOut-Mail */
$config->optoutdays = 7;
$config->optout_subject = "[PPH Umfrage] Austragen deiner Mailadresse";
$config->optout_header = "From: {$config->adminname} <{$config->adminmail}>\r\nReply-To: piraten@invalid\r\nContent-Type: text/plain; Charset={$config->charset}";
$config->optout_body = "Ahoi!\n\nDeine Mailadresse wurde aus der Sammelstelle für Meinungsbilder der Piratenpartei Hessen gelöscht. Falls du wieder eingetragen werden möchtest, melde dich bitte beim {$config->adminname} unter {$config->adminmail}.\n\nKlarmachen zum Ändern!\n";

/* Auswertungkonstanten */
$config->nichtwaehlercolor = array_shift($config->colors);
$config->nichtwaehlercode = "_____";
$config->nichtwaehleranswer = "Nicht abgestimmt";

/* Ein wenig fuer den Adminbereich */
$config->strftimeformat = "%d.%m.%Y %H:%M:%S";
/* Wie viele Mails auf einmal verschickt werden koennen (sendmail.php) */
$config->bulkmailcount = 50;

?>
