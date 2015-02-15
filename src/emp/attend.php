<?php

class Emp_Attend {

	/**
	 * Read config.ini and local.ini for [perms.modName] actName=group,group
	 * 
	 * @param type $request
	 * @param type $response
	 */
	public function authorize($request, $response) {
		
		
	}
	
	public $requireLogin = true;
	public $usesConfig = true;
	public $usesPerms  = true;
	public $rpp = 100;

	public function __construct () {
	}
	public function resources() {
		$this->loadConfig();
	}

	/**
	 *
	 */
	public function approveEvent($req, &$t) {
		$u = $req->getUser();
		$attid = $req->cleanInt('id');
		$att = new Cpemp_Att_Model($attid);
		$att->set('approved', 'Y');
		$att->save();

		$t['url'] = m_appurl('cportal/ticket/edit', array('id'=>$att->get('csrv_ticket_id')));
		$this->presenter = 'redirect';
		$u = $req->getUser();
		$response->addUserMessage('Issue approved.');
		return;
	}

	/**
	 * Save a work performance incident
	 */
	public function saveIncidentAction($request, $response) {
		$u = $request->getUser();
		$points = $this->getConfig('incident.points');

		$cpempid = $request->cleanInt('empid');
		$wpitype = $request->cleanString('wpi_type');
		$ca      = $request->cleanMultiLine('wpi_action');

		$desc = $request->cleanMultiLine('wpinote1');

		if (isset($points[$wpitype])) {
			$points = $points[$wpitype];
		} else {
			$points = 0;
		}

		if ($wpitype == 'W') {
//			$data = new Cpemp_Wpi_Model();
			$data = _makeNew('wpi_model');
			$ticket = new Cpemp_Wpi_Ticket();
		} else {
			$data = new Cpemp_Att_Model();
			$data->set('points', $points);
			$ticket = new Cpemp_Att_Ticket();
		}


		$data->set('code', $wpitype);
		$data->set('created_on', time());
		$data->set('updated_on', time());
		$data->set('owner_id', $u->userId);
		$data->set('owner_initials', $this->_getUserInitials($u));

		$data->set('incident_date', date('Y-m-d', strtotime($request->cleanString('wpi_date'))));
		$data->set('emp_id', $cpempid);
		$data->set('description', $desc);
		$data->set('corr_act', $ca);

		$values = $data->valuesAsArray();
		$values['wpi_type'] = $values['code'];
		$values['wpi_action'] = $values['corr_act'];

		//check time permissions
		if (!$this->_allowableDateRange($request->cleanString('wpi_date'), $u)) {
			$response->addUserMessage('Incident date too far in the past or unable to read date.', 'msg_warn');
			$response->redir = m_appurl('emp/main/view', array('emp_id'=>$cpempid));
			return;
		}


		$form = $this->_loadIncidentForm();
		if (!$form->validate($values)) {
			//error
			$response->redir = m_appurl('emp/main/view', array('emp_id'=>$cpempid));
			$u = $request->getUser();
			$response->addUserMessage('Form is missing required fields.', 'msg_warn');
			return;
		}


		$ticket->setStage($data);
		//load up employee account info.
		$emp = _makeNew('employee_model');
		$emp->load($cpempid);
		$acct = _makeNew('dataitem', 'user_account');
		$acct->load($emp->get('user_account_id'));
		$ticket->accountItem = $acct;
		$ticket->save();

		$response->redir = m_appurl('emp/main/view', array('emp_id'=>$cpempid));

		$response->addTo('sparkMsg', 'Incident saved.');
	}

	/**
	 * Save an attendance issue
	 */
	public function saveAttendanceAction($request, $response) {
		$u = $request->getUser();
		$points = $this->getConfig('incident.points');

		$empid = $request->cleanInt('empid2');
		$wpitype = $request->cleanString('att_type');
		$desc = $request->cleanMultiLine('note2');
		$ca   = $request->cleanMultiLine('att_action');
		$vh      = $request->cleanFloat('vac_hr');
		if ($vh == 0) { $vh = NULL; }

		if (isset($points[$wpitype])) {
			$points = $points[$wpitype];
		} else {
			$points = 0;
		}

		if ($wpitype == 'W') {
			$data = _makeNew('wpi_model');
			$ticket = new Cpemp_Wpi_Ticket();
		} else {
			$data = _makeNew('attendance_model');
			$data->set('points', $points);
			$ticket = new Cpemp_Att_Ticket($data);
		}

		$data->set('code', $wpitype);
		$data->set('created_on', time());
		$data->set('updated_on', time());
		$data->set('owner_id', $u->userId);
		$data->set('owner_initials', $this->_getUserInitials($u));

		$data->set('incident_date', date('Y-m-d', strtotime($request->cleanString('att_date'))));
		$data->set('emp_id', $empid);
		$data->set('description', $desc);
		$data->set('corr_act', $ca);
		$data->set('vac_hr', $vh);

		$values = $data->valuesAsArray();
		$values['att_type'] = $values['code'];
		$values['att_action'] = $values['corr_act'];

		if (!$this->_allowableDateRange($request->cleanString('att_date'), $u)) {
			$response->addUserMessage('Incident date too far in the past or unable to read date.', 'msg_warn');
			$this->presenter = 'redirect';
			$response->redir = m_appurl('emp/main/view', array('emp_id'=>$empid));
			return;
		}


		$form = $this->_loadAttendanceForm();
		if (!$form->validate($values)) {
			//error
			$response->redir = m_appurl('emp/main/view', array('emp_id'=>$empid));
			$this->presenter = 'redirect';
			$u = $request->getUser();
			//$response->addUserMessage('Form is missing required fields.', 'msg_warn');
			return;
		}
		$ticket->setStage($data);


		//load up employee account info.
		$emp = _make('employee_model');
		$emp->load($empid);

		$acct = _make('dataitem', 'user_account');
		$acct->load($emp->get('user_account_id'));
		$ticket->accountItem = $acct;

		$ticket->save();

		$response->redir  = m_appurl('emp/main/view', array('emp_id'=>$empid));
//		$t['url'] = m_appurl('emp/main/view', array('emp_id'=>$empid));
		$this->presenter = 'redirect';

		$u = $request->getUser();
		//$response->addUserMessage('Incident saved.');
	}



	/**
	 * Auto-generate a form using the form library
	 */
	public function _loadIncidentForm($values=array(), $edit=false) {
		$form = 'metroform/form.php';
		$file = 'emp/form.php';
		$container = Metrodi_Container::getContainer();
		$container->tryFileLoading($file);
		$container->tryFileLoading($form);


		$corrective = _get('corrective.wpi');
		return Emp_Form::loadIncidentForm('wpi', $values, $edit, $corrective);
	}

	/**
	 * Auto-generate a form using the form library
	 */
	public function _loadAttendanceForm($values=array(), $edit=false) {
		$form = 'metroform/form.php';
		$file = 'emp/form.php';
		$container = Metrodi_Container::getContainer();
		$container->tryFileLoading($file);
		$container->tryFileLoading($form);

		$corrective          = _get('corrective.att');
		$attendanceTypeList  = _get('attendance');
		return Emp_Form::loadAttendanceForm('attend', $values, $edit, $corrective, $attendanceTypeList);
	}


	/**
	 * Use the module's configuration to determine if this user can set the desired date
	 * @return Boolean true if the user can create an incident for the given date
	 */
	public function _allowableDateRange($date, $u) {
		$time = strtotime($date);
		if ($time === FALSE) {
			return FALSE;
		}
		$configs = $this->getConfig('incident.limit');
		$hours = $configs['limithours'];
		$seconds = $hours * 60 * 60;
		$now = time();
		if ($now - $seconds < $time) {
			return true;
		}
		//date in the past too far, check permissions
		$perms = $this->getConfig('perms');
		$groups = explode(',', $perms['overridetime']);
		foreach ($groups as $_g) {
			if ($u->belongsToGroup($_g)) return true;
		}

		return false;
	}


	/**
	 * Automatically turn the local.ini/config.ini settings for "config.attendance" into JSON data.
	 */
	public function listTypesAction($req, &$t) {
		$t->identifier = 'label';
		$corrective = $this->getConfig('attendance');
		$listItems = array();
		foreach( $corrective as $_k => $_c) {
			//$listItems[] = array('label' =>'('.$_k.') '.$_c, 'code'=>$_k, 'value'=>'('.$_k.') '.$_c);
			//for jeditable
			$listItems[$_k] = '('.$_k.') '.$_c;
		}
		//for jeditable
		echo json_encode($listItems);
		exit();
//		$t->items = $listItems;
	}

	/**
	 * Automatically turn the local.ini/config.ini settings for "config.corrective.att" into JSON data.
	 */
	public function listCorrAttEvent($req, &$t) {
		$t['identifier'] = 'label';
		$corrective = $this->getConfig('corrective.att');
		foreach( $corrective as $_k => $_c) {
			$t['items'][] = array('label' =>$_c, 'code'=>$_k, 'value'=>$_c);
		}
	}


	public function updateTypeAction($req, &$t) {
		$u = $req->getUser();

		$allowed = FALSE;

		$ticketId = $req->cleanInt('csrv_ticket_id');
		$attType  = $req->cleanString('newvalue');
		$ticket = new Cportal_Ticket_Model();
		if (!$ticket->load($ticketId)) {
			$t->result = 'error';
			$t->error_code = 401;
			return;
		}
		$attend  = Cportal_Ticket_Model::ticketFactory($ticket);
		$oldType = $attend->stageItem->get('code');
		$attend->stageItem->set('code', $attType);
		$attend->stageItem->save();

		$this->_logTicketChange($ticketId, $u->userId, "type", $oldType, $attType, $u->username);
		$t->result = 'ok';
	}

	public function updateDateEvent($req, &$t) {
		$u = $req->getUser();

		$allowed = FALSE;
		if (!$this->hasPermission($u, $this->serviceName, 'updateDate')) {
			$t['result'] = 'disallowed';
			return;
		}

		$ticketId = $req->cleanInt('csrv_ticket_id');
		if (strtotime($req->cleanString('date')) === FALSE) {
			$t['badDate'] = $req->cleanString('date');
			$t['result'] = 'bad';
			return;
		}

		$attType  = gmdate('Y-m-d', strtotime($req->cleanString('date')));
		$ticket = new Cportal_Ticket();
		if (!$ticket->load($ticketId)) {
			$t['result'] = 'disallowed';
			return;
		}
		$attend  = Cportal_Ticket::ticketFactory($ticket);
		$oldType = $attend->stageItem->get('incident_date');
		$attend->stageItem->set('incident_date', $attType);
		$attend->stageItem->save();

		$this->_logTicketChange($ticketId, $u->userId, "incident date", $oldType, $attType, $u->username);
		$t['result'] = 'good';
	}

	/**
	 * This is here for automatic permission mapping
	 * in main.php
	 */
	public function updatePointsAction($request, $response) {
		$this->updateFieldAction($request, $response);
	}

	public function updateFieldAction($req, &$t) {
		$field     = $req->cleanString('f');
		$fieldDesc = $req->cleanString('fd');
		$u = $req->getUser();


		$ticketId   = $req->cleanInt('csrv_ticket_id');
		$attValue   = $req->cleanFloat('newvalue');
		if ($attValue == 0) {
			$attValue = NULL;
		}
		$ticket = new Cportal_Ticket_Model();
		if (!$ticket->load($ticketId)) {
			$t->result = 'error';
			return;
		}
		$attend    = Cportal_Ticket_Model::ticketFactory($ticket);
		$oldValue  = $attend->stageItem->get($field);
		$attend->stageItem->set($field, $attValue);
		$attend->stageItem->save();

		$this->_logTicketChange($ticketId, $u->userId, $fieldDesc, $oldValue , $attValue, $u->username);
		$t->result = 'good';
	}

	public function updateDescAction($req, &$t) {
		$u = $req->getUser();

		$allowed = FALSE;

		$ticketId = $req->cleanInt('csrv_ticket_id');
		$newValue  = $req->cleanString('newvalue');
		$ticket = new Cportal_Ticket_Model();
		if (!$ticket->load($ticketId)) {
			$t->result = 'error';
			$t->error_code = 401;
			return;
		}
		$attend  = Cportal_Ticket_Model::ticketFactory($ticket);
		$oldType = $attend->stageItem->get('description');
		$attend->stageItem->set('description', $newValue);
		$attend->stageItem->save();

		$this->_logTicketChange($ticketId, $u->userId, "description", $oldType, $attType, $u->username);
		$t->result = 'ok';
	}


	public function updateCorrAttEvent($req, &$t) {
		$u = $req->getUser();

		if (!$this->hasPermission($u, $this->serviceName, 'updateCorrAtt')) {
			$t['result'] = 'disallowed';
			return;
		}

		$ticketId   = $req->cleanInt('csrv_ticket_id');
		$attValue   = $req->cleanString('corr_act');
		$ticket = new Cportal_Ticket();
		if (!$ticket->load($ticketId)) {
			$t['result'] = 'disallowed';
			return;
		}
		$attend    = Cportal_Ticket::ticketFactory($ticket);
		$oldValue = $attend->stageItem->get('corr_act');
		$attend->stageItem->set('corr_act', $attValue);
		$attend->stageItem->save();

		$this->_logTicketChange($ticketId, $u->userId, "Corrective Action", $oldValue, $attValue, $u->username);
		$t['result'] = 'good';
	}

	protected function _logTicketChange($ticketId, $userId, $attrName, $oldValue, $newValue, $username='') {
		//log it
		$log = new Metrodb_Dataitem('csrv_ticket_log');
		$log->csrv_ticket_id = $ticketId;
		$log->author_id      = $userId;
		$log->author         = $username;
		$log->created_on     = time();
		$log->old_value      = $oldValue;
		$log->new_value      = $newValue;
		$log->attr = $attrName;
		return $log->save();
	}

	protected function _getUserInitials($u) {
		$ownerName = $u->getDisplayName();
		$ownerName = explode(' ', $ownerName);
		$ownerInitials = '';
		foreach ($ownerName as $_o) {
			$ownerInitials .= strtoupper( substr($_o, 0, 1));
		}
		//user dones't have firstname/lastname, just use email or username or whatever
		if (strlen($ownerInitials) == 1 ) {
			$ownerInitials = $u->getDisplayName();
		}
		return $ownerInitials;
	}

	public function getConfig($key) {
		$cfg =  parse_ini_file(dirname(__FILE__).'/config.ini',true);

		if (!isset($cfg['config.'.$key])) {
			return NULL;
		}
		return $cfg['config.'.$key];
	}


	public function loadConfig() {
		$appPath = dirname(__FILE__);
		$cfg =  parse_ini_file($appPath.'/config.ini',true);
		if (@file_exists($appPath.'/local.ini') ) { 
			$localCfg = parse_ini_file($appPath.'/local.ini',true);
			$cfg = array_merge($cfg, $localCfg);
		}
		foreach ($cfg as $_k => $_v) {
			if (substr($_k, 0, 7) == 'config.') {
				associate_set(substr($_k, 7), $_v);
			}
		}
	}
}
