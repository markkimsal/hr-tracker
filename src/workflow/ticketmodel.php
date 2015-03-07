<?php

class Workflow_Ticketmodel extends Metrodb_Datamodel {

	public $dataItem;
	public $accountItem;
	public $stageItem = NULL;

	public $tableName = 'csrv_ticket';
	public $sharingModeRead = '';

	public $searchIndexName = 'tickets';
	public $useSearch       = TRUE;


	public function __construct() {
		$this->dataItem = _make('dataitem', 'csrv_ticket');
		$this->dataItem->csrv_ticket_type_id = 0;
		$this->dataItem->csrv_ticket_status_id = Workflow_Ticket_Status::getDefaultStatusId();
		$this->dataItem->created_on = time();

		$this->accountItem = _make('dataitem', 'user_account');
	}

	/**
	 * Load any specific "stage" data model
	 */
	public function loadStage() {
		$finder = _makeNew('dataitem', 'csrv_ticket_type');
		$finder->load( $this->getTypeId() );
		$nameDi = $finder->get('mod_library');
		$this->stageItem = _makeNew($nameDi);
		$this->stageItem->load( 
			array('csrv_ticket_id'=>$this->dataItem->getPrimaryKey())
		);
	}

	public function getStage() {
		return $this->stageItem;
	}

	public function setStage($m) {
		$this->stageItem = $m;
	}

	public function getModel() {
		return $this->dataItem;
	}

	public function setModel($m) {
		$this->dataItem = $m;
	}

	/**
	 * Save this ticket and any associated account objects.
	 */
	public function save() {

		$db = Metrodb_Connector::getHandle(NULL, $this->dataItem->_table);
		$db->exec('SET AUTOCOMMIT=0');
		$db->exec('BEGIN');
		if (is_object($this->accountItem)) {
			$this->accountItem->save();
			$this->dataItem->set('user_account_id', $this->accountItem->getPrimaryKey());
		}
		if ($this->dataItem->get('csrv_ticket_type_id') == 0 ) {
			$this->loadTypeId();
		}
		//load up the ticket type if it is not set

		$newid = $this->dataItem->save();

		if (is_object($this->stageItem)) {
			$this->stageItem->set('csrv_ticket_id', $newid);
			$stageid = $this->stageItem->save();
			if (!$stageid) {
				$db->exec('ROLLBACK');
				return FALSE;
			}
		}

		Metrofw_Kernel::emit('workflow.ticket.save.after', $this);

		if ($this->useSearch === TRUE) {
//			$this->indexInSearch();
		}
		$db->exec('COMMIT');

		return $newid;
	}

	function getId() {
		return $this->dataItem->csrv_ticket_id;
	}

	function getTypeId() {
		return $this->dataItem->csrv_ticket_type_id;
	}

	function getStatusId() {
		return $this->dataItem->csrv_ticket_status_id;
	}

	/**
	 * Return a formatted description of this object
	 */
	function getDescription($t=array()) {
		return nl2br($this->dataItem->description);
	}

	/**
	 * @static
	 */
	public static function ticketFactory($dataItem) {
		$type = new Workflow_Tickettype($dataItem->get('csrv_ticket_type_id'));
		//$className = $type->get('class_name');
		$className = 'Workflow_Ticketmodel';

		$ticketObj = new $className();
		$ticketObj->setModel($dataItem);
		$ticketObj->loadStage();

		return $ticketObj;
	}

	function loadAccount() {
		if (!is_object($this->accountItem) &&
			$this->dataItem->user_account_id > 0) {
			$this->accountItem->load($this->dataItem->user_account_id);
			return;
		}
		$this->accountItem = new Metrodb_Dataitem('user_account');
		$this->accountItem->_cols = array('user_account.*');
		$this->accountItem->hasOne('csrv_ticket',
			'user_account_id', 'tk_tbl', 'user_account_id');
		$this->accountItem->andWhere('tk_tbl.csrv_ticket_id',$this->getId());
		$this->accountItem->load();
	}

	function getUsername() {
		if (! isset($this->dataItem->username) ) {
			$owner = Metrou_User::load($this->dataItem->owner_id);
			return $owner->username;
		}
		return $this->dataItem->username;
	}


	/**
	 * If the same reference number exists, load the data item and the order item
	 */
	function loadFromRefNum($refNum) {
		$this->dataItem->andWhere('ref_num', $refNum);
		$this->dataItem->andWhere('csrv_ticket_type_id', $this->getTypeId());
		$this->dataItem->load();
	}


	/**
	 * If the same reference number exists, load the data item and the order item
	 */
	function loadFromRefId($refNum) {
		$this->dataItem->andWhere('ref_id', $refNum);
		$this->dataItem->andWhere('csrv_ticket_type_id', $this->getTypeId());
		$this->dataItem->load();
	}

	/**
	 * Construct and return a new Workflow_Ticket_Metadata object
	 *
	 * @return Object  CPortal_Ticket_Meatadata object
	 */
	function getMetadata() {
		return new Workflow_Ticket_Metadata();
	}

	public function loadTypeId() {
		$md = $this->getMetadata();
		$className = strtolower(get_class($this->stageItem));

		$code = new Workflow_Tickettype($className);
		$this->dataItem->csrv_ticket_type_id = $code->get('csrv_ticket_type_id');
		return $code->get('csrv_ticket_type_id');
	}

	/**
	 * Collect extra fields for search indexing
	 */
	public function _collectSearchFields() {
		$this->loadAccount();
		$this->loadStage();

		$fields = parent::_collectSearchFields();
		$fields['date_created']      = array('type'=>'keyword',  'value'=>date('Y-m-d',$this->dataItem->created_on));
		$fields['database_id']       = array('type'=>'keyword',  'value'=>$this->dataItem->csrv_ticket_id);
		$fields['originator_email']  = array('type'=>'keyword',  'value'=>$this->accountItem->contact_email);
		$fields['summary']           = array('type'=>'text',     'value'=>'type = '.$this->getTypeId());
		$fields['body']              = array('type'=>'unstored',  'value'=> trim(strip_tags($this->getDescription())));
		//status name
		$fields['status']            = array('type'=>'unstored',  'value'=>Workflow_Ticket_Status::getStatusName($this->getStatusId()));
		return $fields;
	}

}

/**
 * This class is constructed and given out by any ticket subtype
 * as a means of gathering information about the ticket.
 */
class Workflow_Ticket_Metadata {

	public $code                 = '';
	public $classLoaderPackage   = '';
}


class  Workflow_Ticket_Status {

	static $STATUS_LIST = NULL;
	static $dataFinder  = NULL;

	static function setDataFinder($df) {
		Workflow_Ticket_Status::$dataFinder = $df;
	}

	static function getDataFinder() {
		if (Workflow_Ticket_Status::$dataFinder == NULL) {
			Workflow_Ticket_Status::$dataFinder = new Metrodb_Dataitem('csrv_ticket_status');
		}
		Workflow_Ticket_Status::$dataFinder->resetWhere();
		return Workflow_Ticket_Status::$dataFinder;
	}

	static function getDefaultStatus() {
		if (Workflow_Ticket_Status::$STATUS_LIST === NULL) {
			Workflow_Ticket_Status::loadStatusList();
		}
		foreach (Workflow_Ticket_Status::$STATUS_LIST as $_st) {
			if ($_st->get('is_initial') == 1) {
				return $_st;
			}
		}
		return -1;
	}

	static function getDefaultStatusId() {
		$st = Workflow_Ticket_Status::getDefaultStatus();
		return $st->get('csrv_ticket_status_id');
	}

	/**
	 * Return an ascii string representing the status for a particular status ID
	 *
	 * @return String status code
	 */
	static function getStatusCode($statusId) {
		if (Workflow_Ticket_Status::$STATUS_LIST === NULL) {
			Workflow_Ticket_Status::loadStatusList();
		}
		return self::$STATUS_LIST[$statusId]->code;
	}

	/**
	 * Return a human readable ascii string representing the status for a particular status ID
	 *
	 * @return String status display name
	 */
	static function getStatusName($statusId) {
		if (Workflow_Ticket_Status::$STATUS_LIST === NULL) {
			Workflow_Ticket_Status::loadStatusList();
		}
		return self::$STATUS_LIST[$statusId]->display_name;
	}

	static function loadStatusList() {
		//load ticket status list
		$finder = Workflow_Ticket_Status::getDataFinder();
		$finder->_rsltByPkey = TRUE;
		Workflow_Ticket_Status::$STATUS_LIST = $finder->find();
	}

	static function clearStatusList() {
		Workflow_Ticket_Status::$STATUS_LIST = NULL;
	}
}


class Workflow_Tickettype extends Metrodb_Datamodel {

	public $tableName  = 'csrv_ticket_type';
	public $sharingModeRead = '';

	public function __construct($id) {
		parent::__construct();
		if (is_numeric($id)) {
			$id = (int)$id;
			$this->load($id);
		} else if ($id != '') {
			$this->dataItem->andWhere('LOWER(class_name)', $id);
			$this->dataItem->loadExisting();
		}
	}

	public function initDataItem() {
		parent::initDataItem();
		$this->dataItem->_typeMap['mod_library'] = 'string';
		$this->dataItem->_typeMap['class_name']  = 'string';
	}

	public function getModLibrary() {
		return $this->get('mod_library');
	}

	public function getClassName() {
		return $this->get('class_name');
	}

	static function getCodeLetter($typeId) {
		$loader = new Metrodb_Dataitem('csrv_ticket_type');
		if (!$loader->load($typeId)) {
			return "N/A";
		}
		return $loader->get('abbrv');
	}

	public function loadRequiredClass() {
		if ($this->dataItem->get('mod_library') == '') {
			return FALSE;
		}
		list($mod, $file) = explode('::', $this->dataItem->get('mod_library'));
		include_once('src/'.strtolower($mod).'/lib/'.$file.'.php');
		return TRUE;
	}
}
