<?php

class Cportal_Ticket_Model extends Metrodb_Datamodel {

	public $dataItem;
	public $accountItem;
	public $stageItem    = NULL;

	public $tableName = 'csrv_ticket';
	public $sharingModeRead = '';

	public $searchIndexName = 'tickets';
	public $useSearch       = TRUE;


	public function __construct() {
		$this->dataItem = new Metrodb_Dataitem('csrv_ticket');
		$this->dataItem->csrv_ticket_type_id = 0;
		$this->dataItem->csrv_ticket_status_id = Cportal_Ticket_Status::getDefaultStatusId();
		$this->dataItem->created_on = time();

		//$this->accountItem = new Metrodb_Dataitem('cserv_ticket_account');
		$this->accountItem = new Metrodb_Dataitem('user_account');
	}

	/**
	 * Load any specific "stage" data model
	 */
	public function loadStage() {
	}


	public function setStage($s) {
		$this->stageItem = $s;
	}

	public function getStage() {
		return $this->stageItem;
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

		Metrofw_Kernel::emit('csrv_ticket_save_after', $this);

/*
		if (Cgn_ObjectStore::hasConfig('object://signal/signal/handler')) {
			//$sigHandler = Cgn_ObjectStore::getObject('object://defaultSignalHandler');
			Cgn_Signal_Mgr::emit('csrv_ticket_save_after', $this);
		}
*/

		if ($this->useSearch === TRUE) {
			$this->indexInSearch();
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
		$type = new Cportal_Ticket_Type($dataItem->get('csrv_ticket_type_id'));
		$className = $type->get('class_name');
		if ($className == '') {
			$className = 'Cportal_Ticket';
		}

//		_didef($type->get('mod_library'), $type->get('class_name'));
//		$ticketObj = _makeNew($type->get('mod_library'));

		$type->loadRequiredClass();

		$ticketObj = new $className();
		$ticketObj->setModel($dataItem);
		$ticketObj->loadStage();

		/*
		switch ($dataItem->csrv_ticket_type_id) {
			case Cportal_Ticket_Type::$TICK_TYPE_ORDER:
				Cgn::loadModLibrary('Custserv::Custserv_Order');
				return Custserv_Order::makeFromTicket($ticketObj);
				break;
			case Custserv_Ticket_Type::$TICK_TYPE_RMA:
				Cgn::loadModLibrary('Custserv::Custserv_Rma');
				return Custserv_Rma::makeFromTicket($ticketObj);
				break;
			case Custserv_Ticket_Type::$TICK_TYPE_QUOTE:
				Cgn::loadModLibrary('Custserv::Custserv_Quote');
				return Custserv_Quote::makeFromTicket($ticketObj);
				break;

			default:
				return $ticketObj;
		}
		 */
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
	 * Construct and return a new Cportal_Ticket_Metadata object
	 *
	 * @return Object  CPortal_Ticket_Meatadata object
	 */
	function getMetadata() {
		return new Cportal_Ticket_Metadata();
	}

	public function loadTypeId() {
		$md = $this->getMetadata();
		$mdCode = @$md->code;
		$code = new Cportal_Ticket_Type($mdCode);
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
		$fields['status']            = array('type'=>'unstored',  'value'=>Cportal_Ticket_Status::getStatusName($this->getStatusId()));
		return $fields;
	}

}

/**
 * This class is constructed and given out by any ticket subtype
 * as a means of gathering information about the ticket.
 */
class Cportal_Ticket_Metadata {

	public $code                 = '';
	public $classLoaderPackage   = '';
}


class  Cportal_Ticket_Status {

	static $STATUS_LIST = NULL;

	static $dataFinder = NULL;

	static function setDataFinder($df) {
		Cportal_Ticket_Status::$dataFinder = $df;
	}

	static function getDataFinder() {
		if (Cportal_Ticket_Status::$dataFinder == NULL) {
			Cportal_Ticket_Status::$dataFinder = new Metrodb_Dataitem('csrv_ticket_status');
		}
		Cportal_Ticket_Status::$dataFinder->resetWhere();
		return Cportal_Ticket_Status::$dataFinder;
	}

	static function getDefaultStatus() {
		if (Cportal_Ticket_Status::$STATUS_LIST === NULL) {
			Cportal_Ticket_Status::loadStatusList();
		}
		foreach (Cportal_Ticket_Status::$STATUS_LIST as $_st) {
			if ($_st->get('is_initial') == 1) {
				return $_st;
			}
		}
		return -1;
	}

	static function getDefaultStatusId() {
		$st = Cportal_Ticket_Status::getDefaultStatus();
		return $st->get('csrv_ticket_status_id');
	}

	/**
	 * Return an ascii string representing the status for a particular status ID
	 *
	 * @return String status code
	 */
	static function getStatusCode($statusId) {
		if (Cportal_Ticket_Status::$STATUS_LIST === NULL) {
			Cportal_Ticket_Status::loadStatusList();
		}
		return self::$STATUS_LIST[$statusId]->code;
	}

	/**
	 * Return a human readable ascii string representing the status for a particular status ID
	 *
	 * @return String status display name
	 */
	static function getStatusName($statusId) {
		if (Cportal_Ticket_Status::$STATUS_LIST === NULL) {
			Cportal_Ticket_Status::loadStatusList();
		}
		return self::$STATUS_LIST[$statusId]->display_name;
	}

	static function loadStatusList() {
		//load ticket status list
		$finder = Cportal_Ticket_Status::getDataFinder();
		$finder->_rsltByPkey = TRUE;
		Cportal_Ticket_Status::$STATUS_LIST = $finder->find();
	}

	static function clearStatusList() {
		Cportal_Ticket_Status::$STATUS_LIST = NULL;
	}
}
/*
class  Cportal_Ticket_Type {

	static $TICK_TYPE_ORDER = 1;
	static $TICK_TYPE_RMA   = 2;
	static $TICK_TYPE_QUOTE = 3;

	static $TICK_ABBRV_ORDER = 'O';
	static $TICK_ABBRV_RMA   = 'R';
	static $TICK_ABBRV_QUOTE = 'Q';


	static function getDefaultType() {
		return Custserv_Ticket_Type::$TICK_TYPE_ORDER;
	}

	/**
	 * Return true if the ticket is an order type
	 *
	 * @param $ticket Object the ticket in question
	 * @bool 	true if the ticket is an order type
	// * /
	static function isTicketOrder($ticket) {
		if ($ticket->getTypeId() == self::$TICK_TYPE_ORDER) {
			return true;
		}
		return false;
	}

	/**
	 * Return a code letter for a particular type ID
	*/ 
/*
	static function getCodeLetter($typeId) {
		switch($typeId) {
			case self::$TICK_TYPE_ORDER:
			return self::$TICK_ABBRV_ORDER;

			case self::$TICK_TYPE_RMA:
			return self::$TICK_ABBRV_RMA;

			case self::$TICK_TYPE_QUOTE:
			return self::$TICK_ABBRV_QUOTE;
		}
		return "N/A";
	}
}
/*
 */
class Cportal_Ticket_Type extends Metrodb_Datamodel {

	public $tableName  = 'csrv_ticket_type';
	public $sharingModeRead = '';

	public function __construct($id) {
		parent::__construct();
		if (is_numeric($id)) {
			$id = (int)$id;
			$this->load($id);
		} else if ($id != '') {
			$this->dataItem->andWhere('code', $id);
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
