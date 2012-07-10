-- phpMyAdmin SQL Dump
-- version 3.2.2.1deb1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Erstellungszeit: 20. Januar 2010 um 16:10
-- Server Version: 5.1.37
-- PHP-Version: 5.2.10-2ubuntu6.4

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Datenbank: `limesurvey`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `lime_ips`
--

CREATE TABLE IF NOT EXISTS `lime_ips` (
  `ip` varchar(16) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `banTill` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`ip`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `lime_answers`
--

CREATE TABLE IF NOT EXISTS `lime_answers` (
  `qid` int(11) NOT NULL DEFAULT '0',
  `code` varchar(5) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `answer` text COLLATE utf8_unicode_ci NOT NULL,
  `default_value` char(1) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'N',
  `assessment_value` int(11) NOT NULL DEFAULT '0',
  `sortorder` int(11) NOT NULL,
  `language` varchar(20) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'en',
  PRIMARY KEY (`qid`,`code`,`language`),
  KEY `answers_idx2` (`sortorder`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `lime_assessments`
--

CREATE TABLE IF NOT EXISTS `lime_assessments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sid` int(11) NOT NULL DEFAULT '0',
  `scope` varchar(5) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `gid` int(11) NOT NULL DEFAULT '0',
  `name` text COLLATE utf8_unicode_ci NOT NULL,
  `minimum` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `maximum` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `message` text COLLATE utf8_unicode_ci NOT NULL,
  `language` varchar(20) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'en',
  PRIMARY KEY (`id`,`language`),
  KEY `assessments_idx2` (`sid`),
  KEY `assessments_idx3` (`gid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `lime_conditions`
--

CREATE TABLE IF NOT EXISTS `lime_conditions` (
  `cid` int(11) NOT NULL AUTO_INCREMENT,
  `qid` int(11) NOT NULL DEFAULT '0',
  `scenario` int(11) NOT NULL DEFAULT '1',
  `cqid` int(11) NOT NULL DEFAULT '0',
  `cfieldname` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `method` char(2) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `value` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`cid`),
  KEY `conditions_idx2` (`qid`),
  KEY `conditions_idx3` (`cqid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `lime_blacklist`
--

CREATE TABLE IF NOT EXISTS `lime_blacklist` (
  `mailhash` char(32) COLLATE utf8_unicode_ci NOT NULL,
  `sendmail` char(1) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Y',
  PRIMARY KEY (`mailhash`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `lime_blacklist_tokens`
--

CREATE TABLE IF NOT EXISTS `lime_blacklist_tokens` (
  `mailhash` char(32) COLLATE utf8_unicode_ci NOT NULL,
  `token` varchar(36) COLLATE utf8_unicode_ci NOT NULL,
  `validtill` date NOT NULL,
  PRIMARY KEY (`mailhash`,`token`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `lime_groups`
--

CREATE TABLE IF NOT EXISTS `lime_groups` (
  `gid` int(11) NOT NULL AUTO_INCREMENT,
  `sid` int(11) NOT NULL DEFAULT '0',
  `group_name` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `group_order` int(11) NOT NULL DEFAULT '0',
  `description` text COLLATE utf8_unicode_ci,
  `language` varchar(20) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'en',
  PRIMARY KEY (`gid`,`language`),
  KEY `groups_idx2` (`sid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `lime_labels`
--

CREATE TABLE IF NOT EXISTS `lime_labels` (
  `lid` int(11) NOT NULL DEFAULT '0',
  `code` varchar(5) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `title` text COLLATE utf8_unicode_ci,
  `sortorder` int(11) NOT NULL,
  `assessment_value` int(11) NOT NULL DEFAULT '0',
  `language` varchar(20) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'en',
  PRIMARY KEY (`lid`,`sortorder`,`language`),
  KEY `ixcode` (`code`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `lime_labelsets`
--

CREATE TABLE IF NOT EXISTS `lime_labelsets` (
  `lid` int(11) NOT NULL AUTO_INCREMENT,
  `label_name` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `languages` varchar(200) COLLATE utf8_unicode_ci DEFAULT 'en',
  PRIMARY KEY (`lid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `lime_questions`
--

CREATE TABLE IF NOT EXISTS `lime_questions` (
  `qid` int(11) NOT NULL AUTO_INCREMENT,
  `sid` int(11) NOT NULL DEFAULT '0',
  `gid` int(11) NOT NULL DEFAULT '0',
  `type` char(1) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'T',
  `title` varchar(20) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `question` text COLLATE utf8_unicode_ci NOT NULL,
  `preg` text COLLATE utf8_unicode_ci,
  `help` text COLLATE utf8_unicode_ci,
  `other` char(1) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'N',
  `mandatory` char(1) COLLATE utf8_unicode_ci DEFAULT NULL,
  `lid` int(11) NOT NULL DEFAULT '0',
  `lid1` int(11) NOT NULL DEFAULT '0',
  `question_order` int(11) NOT NULL,
  `language` varchar(20) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'en',
  PRIMARY KEY (`qid`,`language`),
  KEY `questions_idx2` (`sid`),
  KEY `questions_idx3` (`gid`),
  KEY `questions_idx4` (`type`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `lime_question_attributes`
--

CREATE TABLE IF NOT EXISTS `lime_question_attributes` (
  `qaid` int(11) NOT NULL AUTO_INCREMENT,
  `qid` int(11) NOT NULL DEFAULT '0',
  `attribute` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `value` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`qaid`),
  KEY `question_attributes_idx2` (`qid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `lime_quota`
--

CREATE TABLE IF NOT EXISTS `lime_quota` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sid` int(11) DEFAULT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `qlimit` int(8) DEFAULT NULL,
  `action` int(2) DEFAULT NULL,
  `active` int(1) NOT NULL DEFAULT '1',
  `autoload_url` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `quota_idx2` (`sid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `lime_quota_languagesettings`
--

CREATE TABLE IF NOT EXISTS `lime_quota_languagesettings` (
  `quotals_id` int(11) NOT NULL AUTO_INCREMENT,
  `quotals_quota_id` int(11) NOT NULL DEFAULT '0',
  `quotals_language` varchar(45) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'en',
  `quotals_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `quotals_message` text COLLATE utf8_unicode_ci NOT NULL,
  `quotals_url` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `quotals_urldescrip` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`quotals_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `lime_quota_members`
--

CREATE TABLE IF NOT EXISTS `lime_quota_members` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sid` int(11) DEFAULT NULL,
  `qid` int(11) DEFAULT NULL,
  `quota_id` int(11) DEFAULT NULL,
  `code` varchar(11) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sid` (`sid`,`qid`,`quota_id`,`code`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `lime_saved_control`
--

CREATE TABLE IF NOT EXISTS `lime_saved_control` (
  `scid` int(11) NOT NULL AUTO_INCREMENT,
  `sid` int(11) NOT NULL DEFAULT '0',
  `srid` int(11) NOT NULL DEFAULT '0',
  `identifier` text COLLATE utf8_unicode_ci NOT NULL,
  `access_code` text COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(320) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ip` text COLLATE utf8_unicode_ci NOT NULL,
  `saved_thisstep` text COLLATE utf8_unicode_ci NOT NULL,
  `status` char(1) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `saved_date` datetime NOT NULL,
  `refurl` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`scid`),
  KEY `saved_control_idx2` (`sid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `lime_settings_global`
--

CREATE TABLE IF NOT EXISTS `lime_settings_global` (
  `stg_name` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `stg_value` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`stg_name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Daten für Tabelle `lime_settings_global`
--

INSERT INTO `lime_settings_global` (`stg_name`, `stg_value`) VALUES
('DBVersion', '142'),
('SessionName', 'ls75645485646557527254'),
('updateavailable', '0'),
('updatelastcheck', '2010-01-16 11:44:17'),
('updatecheckperiod', '7'),
('sitename', 'LimeSurvey'),
('defaultlang', 'en'),
('defaulttemplate', 'default'),
('defaulthtmleditormode', 'inline'),
('timeadjust', '+0 hours'),
('usepdfexport', '0'),
('addTitleToLinks', ''),
('sessionlifetime', '3600'),
('siteadminemail', 'your@email.org'),
('siteadminbounce', 'your@email.org'),
('siteadminname', 'Your Name'),
('emailmethod', 'smtp'),
('emailsmtphost', 'localhost'),
('emailsmtpuser', ''),
('emailsmtpssl', ''),
('emailsmtpdebug', '0'),
('maxemails', '50'),
('surveyPreview_require_Auth', '1'),
('filterxsshtml', '1'),
('usercontrolSameGroupPolicy', '1'),
('shownoanswer', '1'),
('repeatheadings', '25');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `lime_surveys`
--

CREATE TABLE IF NOT EXISTS `lime_surveys` (
  `sid` int(11) NOT NULL,
  `owner_id` int(11) NOT NULL,
  `admin` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `active` char(1) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'N',
  `expires` datetime DEFAULT NULL,
  `startdate` datetime DEFAULT NULL,
  `adminemail` varchar(320) COLLATE utf8_unicode_ci DEFAULT NULL,
  `private` char(1) COLLATE utf8_unicode_ci DEFAULT NULL,
  `faxto` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `format` char(1) COLLATE utf8_unicode_ci DEFAULT NULL,
  `template` varchar(100) COLLATE utf8_unicode_ci DEFAULT 'default',
  `language` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `additional_languages` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `datestamp` char(1) COLLATE utf8_unicode_ci DEFAULT 'N',
  `usecookie` char(1) COLLATE utf8_unicode_ci DEFAULT 'N',
  `notification` char(1) COLLATE utf8_unicode_ci DEFAULT '0',
  `allowregister` char(1) COLLATE utf8_unicode_ci DEFAULT 'N',
  `allowsave` char(1) COLLATE utf8_unicode_ci DEFAULT 'Y',
  `autonumber_start` bigint(11) DEFAULT '0',
  `autoredirect` char(1) COLLATE utf8_unicode_ci DEFAULT 'N',
  `allowprev` char(1) COLLATE utf8_unicode_ci DEFAULT 'Y',
  `printanswers` char(1) COLLATE utf8_unicode_ci DEFAULT 'N',
  `ipaddr` char(1) COLLATE utf8_unicode_ci DEFAULT 'N',
  `refurl` char(1) COLLATE utf8_unicode_ci DEFAULT 'N',
  `datecreated` date DEFAULT NULL,
  `publicstatistics` char(1) COLLATE utf8_unicode_ci DEFAULT 'N',
  `publicgraphs` char(1) COLLATE utf8_unicode_ci DEFAULT 'N',
  `listpublic` char(1) COLLATE utf8_unicode_ci DEFAULT 'N',
  `htmlemail` char(1) COLLATE utf8_unicode_ci DEFAULT 'N',
  `tokenanswerspersistence` char(1) COLLATE utf8_unicode_ci DEFAULT 'N',
  `assessments` char(1) COLLATE utf8_unicode_ci DEFAULT 'N',
  `usecaptcha` char(1) COLLATE utf8_unicode_ci DEFAULT 'N',
  `usetokens` char(1) COLLATE utf8_unicode_ci DEFAULT 'N',
  `bounce_email` varchar(320) COLLATE utf8_unicode_ci DEFAULT NULL,
  `attributedescriptions` text COLLATE utf8_unicode_ci,
  `emailresponseto` text COLLATE utf8_unicode_ci,
  `tokenlength` tinyint(2) DEFAULT '15',
  PRIMARY KEY (`sid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `lime_surveys_languagesettings`
--

CREATE TABLE IF NOT EXISTS `lime_surveys_languagesettings` (
  `surveyls_survey_id` int(10) unsigned NOT NULL DEFAULT '0',
  `surveyls_language` varchar(45) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'en',
  `surveyls_title` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `surveyls_description` text COLLATE utf8_unicode_ci,
  `surveyls_welcometext` text COLLATE utf8_unicode_ci,
  `surveyls_endtext` text COLLATE utf8_unicode_ci,
  `surveyls_url` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `surveyls_urldescription` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `surveyls_email_invite_subj` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `surveyls_email_invite` text COLLATE utf8_unicode_ci,
  `surveyls_email_remind_subj` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `surveyls_email_remind` text COLLATE utf8_unicode_ci,
  `surveyls_email_register_subj` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `surveyls_email_register` text COLLATE utf8_unicode_ci,
  `surveyls_email_confirm_subj` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `surveyls_email_confirm` text COLLATE utf8_unicode_ci,
  `surveyls_dateformat` int(10) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`surveyls_survey_id`,`surveyls_language`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `lime_surveys_rights`
--

CREATE TABLE IF NOT EXISTS `lime_surveys_rights` (
  `sid` int(10) unsigned NOT NULL DEFAULT '0',
  `uid` int(10) unsigned NOT NULL DEFAULT '0',
  `edit_survey_property` tinyint(1) NOT NULL DEFAULT '0',
  `define_questions` tinyint(1) NOT NULL DEFAULT '0',
  `browse_response` tinyint(1) NOT NULL DEFAULT '0',
  `export` tinyint(1) NOT NULL DEFAULT '0',
  `delete_survey` tinyint(1) NOT NULL DEFAULT '0',
  `activate_survey` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`sid`,`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `lime_templates`
--

CREATE TABLE IF NOT EXISTS `lime_templates` (
  `folder` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `creator` int(11) NOT NULL,
  PRIMARY KEY (`folder`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `lime_templates_rights`
--

CREATE TABLE IF NOT EXISTS `lime_templates_rights` (
  `uid` int(11) NOT NULL,
  `folder` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `use` int(1) NOT NULL,
  PRIMARY KEY (`uid`,`folder`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `lime_users`
--

CREATE TABLE IF NOT EXISTS `lime_users` (
  `uid` int(11) NOT NULL AUTO_INCREMENT,
  `users_name` varchar(64) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `password` blob NOT NULL,
  `full_name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `parent_id` int(10) unsigned NOT NULL,
  `lang` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `email` varchar(320) COLLATE utf8_unicode_ci DEFAULT NULL,
  `create_survey` tinyint(1) NOT NULL DEFAULT '0',
  `create_user` tinyint(1) NOT NULL DEFAULT '0',
  `delete_user` tinyint(1) NOT NULL DEFAULT '0',
  `superadmin` tinyint(1) NOT NULL DEFAULT '0',
  `configurator` tinyint(1) NOT NULL DEFAULT '0',
  `manage_template` tinyint(1) NOT NULL DEFAULT '0',
  `manage_label` tinyint(1) NOT NULL DEFAULT '0',
  `htmleditormode` varchar(7) COLLATE utf8_unicode_ci DEFAULT 'default',
  `one_time_pw` blob,
  `dateformat` int(10) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`uid`),
  UNIQUE KEY `users_name` (`users_name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=2 ;

--
-- Daten für Tabelle `lime_users`
--

INSERT INTO `lime_users` (`uid`, `users_name`, `password`, `full_name`, `parent_id`, `lang`, `email`, `create_survey`, `create_user`, `delete_user`, `superadmin`, `configurator`, `manage_template`, `manage_label`, `htmleditormode`, `one_time_pw`, `dateformat`) VALUES
(1, 'admin', 0x63643036366665346664656431666630366138623362643365393265343738616634626561653937623262653966623365336262343230376134306131616261, 'Your Name', 0, 'en', 'your@email.org', 1, 1, 1, 1, 1, 1, 1, 'default', NULL, 1);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `lime_user_groups`
--

CREATE TABLE IF NOT EXISTS `lime_user_groups` (
  `ugid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `description` text COLLATE utf8_unicode_ci NOT NULL,
  `owner_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`ugid`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `lime_user_in_groups`
--

CREATE TABLE IF NOT EXISTS `lime_user_in_groups` (
  `ugid` int(10) unsigned NOT NULL,
  `uid` int(10) unsigned NOT NULL,
  PRIMARY KEY (`ugid`,`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

