<?php
include_once('src/cportal/lib/Cportal_Ticket.php');

class Emp_Attendancemodel extends Metrodb_Datamodel {

	public $dataItem;
	public $tableName = 'emp_att';
	public $sharingModeRead = '';
	public static $actions   = array();
	public static $typeList  = array();

	public function createDataItem() {
		$dataItem = parent::createDataItem();
		$dataItem->_typeMap['incident_date'] = 'datetime';
		$dataItem->_typeMap['points']        = 'float';
		$dataItem->_typeMap['approved']      = 'string';
		$dataItem->_typeMap['vac_hr']        = 'float';
		$dataItem->_nuls[]                   = 'vac_hr';
		$dataItem->set('approved', 'N');
		$dataItem->set('created_on', time());
		return $dataItem;
	}


	public function getTypeName() {
		$act = $this->get('code');

		if (! isset(self::$typeList[$act])) {
			self::setTypeList();
		}

		if(! isset( self::$typeList[$act])) {
			return '('.$this->get('code').') Unknown';
		}
		return self::$typeList[$act];
	}

	public function getCorrectiveName() {
		$act =  $this->get('corr_act');
		if (! isset(self::$actions[$act])) {
			self::setCorrectiveList();
		}

		return self::$actions[$act];
	}

	public static function setCorrectiveList($l=NULL) {
		if (!is_array($l)) {
			$l = array();
			$appPath = dirname(__FILE__);
			$cfg =  parse_ini_file($appPath.'/config.ini',true);
			if (@file_exists($appPath.'/local.ini') ) { 
				$localCfg = parse_ini_file($appPath.'/local.ini',true);
				$cfg = array_merge($cfg, $localCfg);
			}
			foreach ($cfg as $_k => $_v) {
				if ($_k == 'config.corrective.att') {
					$l = $_v;
				}
			}
		}
		self::$actions = $l;
	}

	public static function setTypeList($l=NULL) {
		if (!is_array($l)) {
			$l = array();
			$appPath = dirname(dirname(__FILE__));
			$cfg =  parse_ini_file($appPath.'/config.ini',true);
			if (@file_exists($appPath.'/local.ini') ) { 
				$localCfg = parse_ini_file($appPath.'/local.ini',true);
				$cfg = array_merge($cfg, $localCfg);
			}
			foreach ($cfg as $_k => $_v) {
				if ($_k == 'config.attendance') {
					$l = $_v;
				}
			}
		}
		self::$typeList = $l;
	}
}

class Cpemp_Att_Ticket extends Cportal_Ticket_Model {

	public $dataItem = NULL;
	public $stageItem = NULL;

	public function __construct() {
		parent::__construct();
		$this->stageItem = new Emp_Attendancemodel();
//		$this->stageItem = $model;
	}

	public function getMetadata() {
		$md = new Cportal_Ticket_Metadata();
		$md->code = 'ATT';
		$md->classLoaderPackage = '@sys.path@/local-modules/cpemp/lib/Cpemp_Att_Model.php:Cpemp_Att_Model';
		return $md;
		return new Cportal_Ticket_Metadata();
	}

	public function setStage($s) {
		$this->stageItem = $s;
	}

	public function getStage() {
		return $this->stageItem;
	}

	/**
	 * Return a formatted description of this object
	 */
	function getDescription($t=array()) {
		ob_start();
		include ('src/cpemp/views/ticket_att.html.php');
		$html = ob_get_contents();
		ob_end_clean();
		return $html;
	//	return Cgn_Template::parseTemplateFile('views/foo.html.php');
	//	return nl2br($this->stageItem->get('description'));
	}

	public function loadStage() {
		if (!$this->stageItem->dataItem->_isNew) {
			return;
		}

		$this->stageItem->dataItem->andWhere('csrv_ticket_id', $this->dataItem->getPrimaryKey());
		$this->stageItem->dataItem->load();
	}
}
