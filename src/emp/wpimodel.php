<?php

include_once('src/workflow/ticketmodel.php');

class Emp_Wpimodel extends Metrodb_Datamodel {

	public $dataItem          = NULL;
	public $ticketModel       = NULL;
	public $tableName         = 'emp_wpi';
	public $sharingModeRead   = '';
	public $sharingModeCreate = '';
	public static $actions    = array();

	public function createDataItem() {
		$dataItem = parent::createDataItem();
		$dataItem->_typeMap['incident_date'] = 'datetime';
		$dataItem->_typeMap['approved'] = 'string';
		$dataItem->set('approved', 'N');
		return $dataItem;
	}

	public function getTypeName() {
		switch ($this->get('code')) {
		case 'N':
			return 'No call, no show';
		case 'P':
			return 'Personal day';
		case 'W':
			return 'Work Performance';
		default:
			return '('.$this->get('code').') Unknown';
		}
	}

	public function getCorrectiveName() {
		$act =  $this->get('corr_act');

		if (! isset(self::$actions[$act])) {
			self::setCorrectiveList();
		}

		return self::$actions[$act];
	}

	public function getTicketModel() {
		if ($this->ticketModel == NULL) {
			$this->ticketModel = _make('ticketmodel');
		}
		return $this->ticketModel;
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
				if ($_k == 'config.corrective.wpi') {
					$l = $_v;
				}
			}

/*
			$serviceConfig =& Cgn_ObjectStore::getObject('object://defaultConfigHandler');
			$serviceConfig->initModule('emp');
			foreach ($serviceConfig->getModuleKeys('emp') as $k) {
				if ($k == 'corrective.wpi')
				$l = $serviceConfig->getModuleVal('emp', $k);
			}
*/
		}
		self::$actions = $l;
	}
}

class Cpemp_Wpi_Ticket extends Workflow_Ticketmodel {

	public $dataItem = NULL;
	public $stageItem = NULL;

	public function __construct() {
		parent::__construct();
		$this->stageItem = new Emp_Wpimodel();
	}

	public function getMetadata() {
		$md = new Workflow_Ticket_Metadata();
		$md->code = 'WPI';
		return $md;
		return new Workflow_Ticket_Metadata();
	}

	public function getStage() {
		return $this->stageItem;
	}

	public function setStage($m) {
		$this->stageItem = $m;
	}

	/**
	 * Return a formatted description of this object
	 */
	function getDescription($t=array()) {
		ob_start();
		include ('src/cpemp/views/ticket_wpi.html.php');
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
