<?php

include_once('src/cportal/lib/Cportal_Ticket.php');

class Emp_Safetymodel extends Metrodb_Datamodel {

	public $dataItem;
	public $ticketItem;
	public $tableName = 'emp_wtrain';
	public static $actions  = array();

	/*
	function __construct() {
		parent::Custserv_Ticket();
		$this->orderItem = new Cgn_DataItem('csrv_order');
		$this->dataItem->csrv_ticket_type_id = Custserv_Ticket_TYPE::$TICK_TYPE_ORDER;
	}
	 */

	public function createDataItem() {
		$dataItem = parent::createDataItem();
		$dataItem->_typeMap['incident_date'] = 'datetime';
		$dataItem->_typeMap['approved'] = 'string';
		$dataItem->set('approved', 'N');
		return $dataItem;
	}

	public function getDisplayName() {
		$act =  $this->get('code');

		if (! isset(self::$actions[$act])) {
			self::setTypeList();
		}

		return self::$actions[$act];
	}

	public static function setTypeList($l=NULL) {
		if (!is_array($l)) {
			$l = array();
			$appPath = dirname(__FILE__);
			$cfg =  parse_ini_file($appPath.'/config.ini',true);
			if (@file_exists($appPath.'/local.ini') ) { 
				$localCfg = parse_ini_file($appPath.'/local.ini',true);
				$cfg = array_merge($cfg, $localCfg);
			}
			foreach ($cfg as $_k => $_v) {
				if ($_k == 'config.training') {
					$l = $_v;
				}
			}
		}
		self::$actions = $l;
	}

}



class Cpemp_Wtrain_Ticket extends Cportal_Ticket_Model {

	public $dataItem = NULL;
	public $stageItem = NULL;

	public function __construct() {
		parent::__construct();
		$this->stageItem = new Emp_Safetymodel();
	}

	public function getMetadata() {
		$md = new Cportal_Ticket_Metadata();
		$md->code = 'WTR';
		$md->code = 'WTR';
		return $md;
		return new Cportal_Ticket_Metadata();
	}

	public function getStage() {
		return $this->stageItem;
	}

	public function setStage($m) {
		$this->stageItem = $m;
	}

	public function save() {
		parent::save();
		$this->stageItem->csrv_ticket_id = $this->dataItem->csrv_ticket_id;
		$this->stageItem->save();
	}

	/**
	 * Return a formatted description of this object
	 */
	function getDescription($t=array()) {
		ob_start();
		//include (CGN_SYS_PATH.'/local-modules/cpemp/templates/ticket_wtrain.html.php');
		include ('src/cpemp/views/ticket_wtrain.html.php');
		$html = ob_get_contents();
		ob_end_clean();
		return $html;
	}


	public function loadStage() {
		if (!$this->stageItem->dataItem->_isNew) {
			return;
		}
		$this->stageItem->dataItem->andWhere('csrv_ticket_id', $this->dataItem->getPrimaryKey());
		$this->stageItem->dataItem->load();
	}
}
