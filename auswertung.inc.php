<?php

require_once(dirname(__FILE__) . "/library.inc.php");

abstract class AuswertungGraph {
	protected $question;
	protected $tokencount;
	protected $votes;
	
	private $colorcodes = array();
	private $nextcolorcode = 0;
	
	public function __construct($question, $tokencount, $votes) {
		$this->question = $question;
		$this->tokencount = $tokencount;
		$this->votes = $votes;
	}
	
	protected function getColorByCode($img, $colorcode) {
		return ImageColorAllocate($img,base_convert(substr($colorcode,0,2),16,10),base_convert(substr($colorcode,2,2),16,10),base_convert(substr($colorcode,4,2),16,10));;
	}

	abstract protected function getHeight();
	abstract protected function getWidth();

	protected function getLegendFont() {
		return 4;
	}
	
	protected function getNichtWaehlerOption() {
		global $config;
		return array(
			"code" => $config->nichtwaehlercode,
			"votes" => $this->tokencount - $this->votes,
			"answer" => $config->nichtwaehleranswer,
			"optimg" => getstatlink_optimg($this->question["feldname"], $config->nichtwaehlercode),
			"isnichtwaehler" => true);
	}

	protected function getLongestAnswer() {
		$a = 0;
		foreach ($this->question["options"] AS $option) {
			$a = max($a, strlen($option["answer"]));
		}
		return $a;
	}

	protected function getMaxVotes() {
		$a = 0;
		foreach ($this->question["options"] AS $option) {
			$a = max($a, $option["votes"]);
		}
		return $a;
	}

	protected function initImage() {
		$height = $this->getHeight();
		$width = $this->getWidth();
		$img = ImageCreateTrueColor($width, $height);
		// Sonst ist alles Schwarz und deprimierend ;)
		$white = ImageColorAllocate($img,255,255,255);
		ImageFilledRectangle($img,0,0,$width,$height,$white);
		ImageColorTransparent($img,$white);
		
		return $img;
	}

	protected function getOptionColorcode(&$option) {
		if (!isset($this->colorcodes[$option["code"]])) {
			global $config;
			$id = $this->nextcolorcode++;
			$this->colorcodes[$option["code"]] = $config->colors[$id % count($config->colors)];
		}
		return $this->colorcodes[$option["code"]];
	}
	
	public function generateOptImg($option) {
		$optimg = ImageCreateTrueColor(14,14);
		ImageFilledRectangle($optimg,0,0,14,14,$this->getColorByCode($optimg,$this->getOptionColorcode($option)));
		ImagePNG($optimg, getstatpath_optimg($this->question["feldname"], $option["code"]));
		ImageDestroy($optimg);
	}
	
	public function generateGraph() {
		return $this->createGraph(getstatpath_graph($this->question["feldname"]));	
	}
	
	public function generateFullGraph() {
		return $this->createFullGraph(getstatpath_fullgraph($this->question["feldname"]));
	}
	
	abstract public function createGraph($filename, $paintnichtwaehler = false);
	public function createFullGraph($filename) {
		return $this->createGraph($filename, true);
	}
}

/*
 * Tortendiagramm
 */
class AuswertungPieGraph extends AuswertungGraph {
	private $arcpos = 0;
	private $legendeposx = null;
	private $legendeposy = null;

	protected function getLegendeWrap() {
		return 20;
	}

	protected function getHeight() {
		return 200;
	}

	protected function getWidth() {
		return $this->getHeight() + 15 + 5 + ImageFontWidth($this->getLegendFont()) * min($this->getLongestAnswer(), $this->getLegendeWrap());
	}

	private function calcS() {
		// Bugfix, sonst malt PHP das Diagramm "etwas" komisch
		return min($this->getWidth(), $this->getHeight())-1;
	}

	private function getCaptionX() {
		if ($this->getHeight() < $this->getWidth()) {
			return $this->calcS();
		}
		return 0;
	}

	private function getCaptionY() {
		if ($this->getHeight() > $this->getWidth()) {
			return $this->calcS();
		}
		return 0;
	}

	private function drawOption($img, $option, $full) {
		$s = $this->calcS();
		
		$angle = round($option["votes"] / $full * 360);
		$colorcode = $this->getOptionColorcode($option);
		$color = $this->getColorByCode($img,$colorcode);
		if ($angle > 0) {
			ImageFilledArc($img,$s/2,$s/2,$s,$s,$this->arcpos,$this->arcpos+$angle,$color,IMG_ARC_PIE);
		}
		ImageColorDeallocate($img, $color);
		$this->arcpos += $angle;
	}

	private function drawLegende($img, &$option, $captioncol) {
		global $config;
	
		if ($this->legendeposx == null) {
			$this->legendeposx = $this->getCaptionX()+5;
		}
		if ($this->legendeposy == null) {
			$this->legendeposy = $this->getCaptionY()+5;
		}
	
		// Legende zeichnen
		$cx = $this->legendeposx;
		$cy = $this->legendeposy;
	
		$answer = trim(iconv($config->charset, "ISO-8859-1", wordwrap($option["answer"],$this->getLegendeWrap(),"\n",true)));
		$answerlines = explode("\n", $answer);

		$color = $this->getColorByCode($img, $this->getOptionColorcode($option));
		ImageFilledRectangle($img,$cx,$cy,$cx+10,$cy+10,$color);
		foreach ($answerlines AS $i => $answerline) {
			ImageString($img,$this->getLegendFont(),$cx+15,$cy-3+$i*ImageFontHeight($this->getLegendFont()),$answerline,$captioncol);
		}
		ImageColorDeallocate($img, $color);
	
		$this->legendeposy += count(explode("\n", $option["answer"])) * ImageFontHeight($this->getLegendFont()) + 10;
	}
	
	public function clear() {
		$this->arcpos = 0;
		$this->legendeposx = null;
		$this->legendeposy = null;
	}
	
	public function createGraph($filename, $paintnichtwaehler = false) {
		if ($paintnichtwaehler) {
			$this->question["options"][] = $this->getNichtWaehlerOption();
			$max = $this->tokencount;
		} else {
			$max = $this->votes;
		}
		
		$img = $this->initImage();
		
		// Schriftfarbe fuer die Legende
		$captioncol = ImageColorAllocate($img,0,0,0);
		$diagrambordercol = $captioncol;
		
		$i=0; $arcpos=0; $fullarcpos=0;
		foreach ($this->question["options"] AS $option) {
			// Dieses Bildchen brauchen wir spaeter noch
			if (!file_exists(getstatpath_optimg($this->question["feldname"], $option["code"]))) {
				$this->generateOptImg($option);
			}
			// Ueberspringe Nichtwaehler bei der einfachen Form
			if (! $paintnichtwaehler && isset($option["isnichtwaehler"])) {
				continue;
			}
			
			$this->drawOption($img, $option, $max);
			$this->drawLegende($img, $option, $captioncol);
		}
		
		// Malen wir mal einen Kreis um das Diagramm
		ImageArc($img,$this->calcS()/2,$this->calcS()/2,$this->calcS(),$this->calcS(),0,360,$diagrambordercol);
		
		// Abspeichern und gut damit ;)
		if (ImagePNG($img,$filename) && ImageDestroy($img)) {
			return true;
		}
		return false;
	}
}

/*
 * Balken
 */
class AuswertungBarGraph extends AuswertungGraph {
	private $barpos = 0;
	private $legendepos = 0;

	protected function getHeight() {
		return ($this->getBarHeight()+$this->getBarSpace()+ImageFontHeight($this->getLegendFont())) * count($this->question["options"]);
	}

	protected function getWidth() {
		return $this->getBarSpaceX();
	}
	
	private function getBarSpaceX() {
		// Beachte die Längste Antwort nur im Bereich zwischen 300 und 500 Pixeln
		return min(max(ImageFontWidth($this->getLegendFont()) * $this->getLongestAnswer(), 300), 500);
	}
	
	private function getLegendPosY($i) {
		return $i * (ImageFontHeight($this->getLegendFont())+$this->getBarHeight()+$this->getBarSpace());
	}
	
	private function getBarPosY($i) {
		return $this->getLegendPosY($i) + ImageFontHeight($this->getLegendFont());
	}
	
	private function getBarHeight() {
		return 20;
	}
	
	private function getBarSpace() {
		return 5;
	}

	private function drawOption($img, $option, $maxvotes) {
		$size = round($option["votes"] / $maxvotes * $this->getBarSpaceX());
		$colorcode = $this->getOptionColorcode($option);
		$color = $this->getColorByCode($img,$colorcode);
		if ($size > 0) {
			ImageFilledRectangle($img, 0, $this->getBarPosY($this->barpos), $size, $this->getBarPosY($this->barpos) + $this->getBarHeight(), $color);
		}
		ImageColorDeallocate($img, $color);
		$this->barpos++;
	}

	private function drawLegende($img, &$option, $captioncol) {
		global $config;
	
		$color = $this->getColorByCode($img, $this->getOptionColorcode($option));
		ImageString($img, $this->getLegendFont(), 0, $this->getLegendPosY($this->legendepos), trim(iconv($config->charset, "ISO-8859-1", $option["answer"])), $captioncol);
		ImageColorDeallocate($img, $color);
	
		$this->legendepos++;
	}
	
	public function clear() {
		$this->barpos = 0;
		$this->legendepos = 0;
	}
	
	public function createGraph($filename, $paintnichtwaehler = false) {
		if ($paintnichtwaehler) {
			$this->question["options"][] = $this->getNichtWaehlerOption();
		}
		
		$img = $this->initImage();
		
		// Schriftfarbe fuer die Legende
		$captioncol = ImageColorAllocate($img,0,0,0);
		$diagrambordercol = $captioncol;
		
		foreach ($this->question["options"] AS $option) {
			// Dieses Bildchen brauchen wir spaeter noch
			if (!file_exists(getstatpath_optimg($this->question["feldname"], $option["code"]))) {
				$this->generateOptImg($option);
			}
			// Ueberspringe Nichtwaehler bei der einfachen Form
			if (! $paintnichtwaehler && isset($option["isnichtwaehler"])) {
				continue;
			}
			
			$this->drawOption($img, $option, $this->getMaxVotes());
			$this->drawLegende($img, $option, $captioncol);
		}
		
		// Abspeichern und gut damit ;)
		if (ImagePNG($img,$filename) && ImageDestroy($img)) {
			return true;
		}
		return false;
	}
}

function generateAuswertung($db, $id) {
	global $config;

	/** Grundinformationen **/
	$rslt = $db->query("
		SELECT	COUNT(`r`.`id`) AS 'votes', `sl`.`surveyls_title` AS 'title',
			UNIX_TIMESTAMP(`s`.`startdate`) AS 'start',
			UNIX_TIMESTAMP(`s`.`expires`) AS 'end',
			`s`.`active` AS 'active', `s`.`usetokens` AS 'usetokens',
			`sl`.`surveyls_description` AS 'desc'
		FROM	`".$db->real_escape_string(DB_getResultTable($id))."` AS `r`
		LEFT JOIN `".DB_getSurveysLangTable()."` AS `sl`
			ON (`sl`.`surveyls_survey_id` = ".intval($id).")
		LEFT JOIN `".DB_getSurveysTable()."` AS `s`
			ON (`s`.`sid` = `sl`.`surveyls_survey_id`)
		GROUP BY `sl`.`surveyls_survey_id`, `sl`.`surveyls_language`");
	$row = $rslt->fetch_object();
	$votes = $row->votes;
	$title = $row->title;
	$desc = $row->desc;
	$start = $row->start;
	$end = $row->end;
	$active = ($row->active == "Y");
	$usetokens = ($row->usetokens == "Y");
	
	if ($usetokens) {
		$tcrslt = $db->query("
			SELECT	COUNT(*) AS 'tokencount'
			FROM	`".$db->real_escape_string(DB_getTokenTable($id))."`");
		$tcrow = $tcrslt->fetch_object();
		$tokencount = $tcrow->tokencount;
	} else {
		$tokencount = $votes;
	}
	
	/** Fragen **/
	$rslt = $db->query("
		SELECT	CONCAT(`sid`,'X',`gid`,'X',`qid`) AS 'feldname', `type` AS 'type', `qid` AS 'qid', `question` AS 'q'
		FROM	`".$db->real_escape_string(DB_getQuestionsTable())."`
		WHERE	`sid` = ".intval($id)." AND `type` IN ('L', 'M')
		ORDER BY `question_order`");
	$questions = array(); $feldnames = array();
	while ($row = $rslt->fetch_object()) {
		// longestanswer brauchen wir spaeter fuer die Legende des Diagramms
		$question = array(	"qid" => $row->qid,	"q" => $row->q,
					"type" => $row->type,	"feldname" => $row->feldname,
					"options" => array(),	"longestanswer" => 0,
					"maxvotes" => 0);
		
		$qrslt = $db->query("
			SELECT	`a`.`code` AS 'code',
				`a`.`answer` as 'answer'
			FROM `".$db->real_escape_string(DB_getAnswersTable())."` AS `a`
			WHERE	`a`.`qid` = ".intval($row->qid)."
			GROUP BY `a`.`code`");
		while ($qrow = $qrslt->fetch_object()) {
			$srslt = $db->query("
				SELECT	COUNT(*) AS 'votes'
				FROM	`".$db->real_escape_string(DB_getResultTable($id))."` AS `s`
				WHERE	".($row->type == 'M' ?
						"`".$db->real_escape_string($row->feldname.$qrow->code)."` = 'Y'" :
						"`".$db->real_escape_string($row->feldname)."` = '".$db->real_escape_string($qrow->code)."'") );
			$srow = $srslt->fetch_object();
			
			$question["options"][$qrow->code] = array(
				"code" => $qrow->code,		"answer" => $qrow->answer,
				"votes" => $srow->votes,
				"optimg" => getstatlink_optimg($row->feldname, $qrow->code));
			$question["longestanswer"] = max($question["longestanswer"], strlen($qrow->answer));
			$question["maxvotes"] = max($question["maxvotes"], $srow->votes);
			
			// Felder vom Typ L kommen spaeter noch an die Reihe
			if ($row->type == 'M') {
				$feldnames[] = "`".$db->real_escape_string($row->feldname.$qrow->code)."`";
			}
		}
		sortOptions($question["options"]);
		
		$questions[$row->feldname] = $question;
		// Beim Typ "M" werden die Felder schon weiter oben eingefuegt
		if ($row->type == 'L') {
			$feldnames[] = "`".$db->real_escape_string($row->feldname)."`";
		}
	}
	
	if (count($feldnames) > 0) {
		/** Token **/
		$trslt = $db->query("SELECT `token`, `submitdate`, ".implode(", ", $feldnames)." FROM `".$db->real_escape_string(DB_getResultTable($id))."` ORDER BY `token`");
		$tokens = array();
		while ($token = $trslt->fetch_assoc()) {
			/* Formatiere den Array passend um */
			foreach ($questions AS $question) {
				if ($question["type"] == 'M') {
					$token[$question["feldname"]] = array();
					foreach ($question["options"] AS $option) {
						if ($token[$question["feldname"].$option["code"]] == 'Y') {
							$token[$question["feldname"]][] = $option["code"];
						}
						unset($token[$question["feldname"].$option["code"]]);
					}
				}
			}
			
			$tokens[] = $token;
		}
		$votes = count($tokens);
		// Wir wollen ja den globalen Template-Ordner nutzen
		chdir(dirname(__FILE__));
		require_once("smarty.inc.php");
		$smarty = getSmarty();
		$smarty->assign("title", $title);
		$smarty->assign("desc", $desc);
		$smarty->assign("votes", $votes);
		$smarty->assign("tokens", $tokens);
		$smarty->assign("questions", $questions);
		file_put_contents(getstatpath_tokenlist($id), $smarty->fetch("tokenlist.html.tpl"));
		
		/** Fuer die Diagramme **/
		// $votes == 0 wuerde zu div0-Bugs fuehren
		if ($votes > 0) {
			// Call ByRef hier wichtig, um den Graph nachtraeglich einzutragen
			foreach ($questions AS &$question) {
				switch ($question["type"]) {
				case 'M':
					$generator = new AuswertungBarGraph($question, $tokencount, $votes);
					break;
				case 'L':
					$generator = new AuswertungPieGraph($question, $tokencount, $votes);
					break;
				}
				if ($generator->generateGraph()) {
					$question["graph"] = getstatlink_graph($question["feldname"]);
				}
				$generator->clear();
				if ($usetokens && $generator->generateFullGraph()) {
					$question["fullgraph"] = getstatlink_fullgraph($question["feldname"]);
				}
			}
		}
		
		// Auswertungsseite bauen
		$smarty = getSmarty();
		$smarty->assign("id", $id);
		$smarty->assign("title", $title);
		$smarty->assign("start", $start);
		$smarty->assign("end", $end);
		$smarty->assign("desc", $desc);
		$smarty->assign("votes", $votes);
		$smarty->assign("tokens", $tokencount);
		$smarty->assign("active", $active);
		$smarty->assign("usetokens", $usetokens);
		
		// Verlinke eine evtl. vorhandenes Log
		if (file_exists(getlogpath($id))) {
			$smarty->assign("loglink", getloglink($id));
		}
		// Verlinke eine evtl. vorhandene Tokenlist
		if (file_exists(getstatpath_tokenlist($id))) {
			$smarty->assign("tokenlist", getstatlink_tokenlist($id));
		}
		// Male ueberall noch die Nicht-Abgestimmt-User dazu
		foreach ($questions AS &$question) {
			$question["options"][] = array(
				"code" => $config->nichtwaehlercode,
				"votes" => $tokencount - $votes,
				"answer" => $config->nichtwaehleranswer,
				"optimg" => getstatlink_optimg($question["feldname"], $config->nichtwaehlercode),
				"isnichtwaehler" => true);
		}
		$smarty->assign("questions", $questions);
		
		file_put_contents(getstatpath($id), $smarty->fetch("auswertung.html.tpl"));
	}
}

?>
