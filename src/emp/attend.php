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
     * Return true of the user has access to the permission
     *
     * Returns false if no permission or domain has been defined
     * @return Boolean  true if the user has permission
     */
    public function hasPermission($u, $domain, $perm) { 

		$this->loadConfig();
        if ($perm == '' || $domain == '') { 
            return FALSE;
        } 

		$perms = _get('perm.'.$domain);
        if ($perms !== NULL && 
            isset($perms[$perm]) ) { 
            $groups = explode(',', $perms[$perm]);
            foreach ($groups as $_g) { 
                if ($u->belongsToGroup($_g) ) { 
                    return TRUE;
                } 
            }
            return FALSE;
        } 
        return FALSE;
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
			$data   = _makeNew('wpi_model');
			$ticket = _makeNew('ticket_model');
		} else {
			$data   = _makeNew('attendance_model');
			$data->set('points', $points);
			$ticket = _makeNew('ticket_model');
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
	public function listTypesAction($request, $response) {
		$response->identifier = 'label';
		$corrective = $this->getConfig('attendance');
		$listItems = array();
		foreach( $corrective as $_k => $_c) {
			//$listItems[] = array('label' =>'('.$_k.') '.$_c, 'code'=>$_k, 'value'=>'('.$_k.') '.$_c);
			//for jeditable
			//$listItems[$_k] = '('.$_k.') '.$_c;
			//for x-editable
			//$listItems[] = array('text' =>'('.$_k.') '.$_c, 'value'=>'('.$_k.') '.$_c);
			$listItems[] = array('text' =>'('.$_k.') '.$_c, 'value'=>$_k);
		}
		//for jeditable
		//echo json_encode($listItems);

		//for x-editable (it cannot support anything more than the data in the response)
		echo json_encode($listItems);
		exit();
//		$response->items = $listItems;
	}

	/**
	 * Automatically turn the local.ini/config.ini settings for "config.corrective.att" into JSON data.
	 */
	public function listCorrAttAction($request, $response) {
		$this->loadConfig();
//		$response->identifier = 'label';
		$corrective = $this->getConfig('corrective.att');

		foreach( $corrective as $_k => $_c) {
			$response->addTo('items', array('text' =>$_c, 'code'=>$_k, 'value'=>$_k));
		}

		//for x-editable (it cannot support anything more than the data in the response)
		echo json_encode($response->items);
		exit();
	}

	public function updateTypeAction($request, $response) {
		$u = $request->getUser();

		$allowed = FALSE;

		$ticketId = $request->cleanInt('pk');
		$attType  = $request->cleanString('value');
		//was value submitted as '(X) some status'  ?
		if (strpos($attType, ')')) {
			sscanf($attType, '(%c) %s', $attType, $nothing);
		}
		if (trim($attType) == '') {
			$response->result = 'error';
			$response->statusCode = 401;
			return;
		}
		if (strlen(trim($attType)) !== 1) {
			$response->result = 'error';
			$response->statusCode = 401;
			return;
		}


		$ticket = _make('ticket_model');
		if (!$ticket->load($ticketId)) {
			$response->result = 'error';
			$response->error_code = 401;
			return;
		}
		$ticket->loadStage();
		$oldType = $ticket->stageItem->get('code');
		$ticket->stageItem->set('code', $attType);
		$ticket->stageItem->save();

		$this->_logTicketChange($ticketId, $u->userId, "type", $oldType, $attType, $u->username);
		$response->result  = 'ok';
		$response->newCode = $attType;
	}

	public function updateDateAction($request, $response) {
		$u = $request->getUser();

		$allowed = FALSE;
		if (!$this->hasPermission($u, 'attend', 'updateDate')) {
			$response->result = 'disallowed';
			$response->statusCode = 401;
			return;
		}

		$ticketId = $request->cleanInt('pk');
		if (strtotime($request->cleanString('value')) === FALSE) {
			$response->badDate = $request->cleanString('value');
			$response->result = 'bad';
			$response->statusCode = 400;
			return;
		}

		$attType  = gmdate('Y-m-d', strtotime($request->cleanString('value')));
//		$ticket = new Cportal_Ticket();
//		$ticket = new Workflow_Ticketmodel();
		$ticket = _make('ticket_model');
		if (!$ticket->load($ticketId)) {
			$response->result = 'disallowed';
			$response->statusCode = 401;
			return;
		}

		$ticket->loadStage();
		$oldType = $ticket->stageItem->get('incident_date');
		$ticket->stageItem->set('incident_date', $attType);
		$ticket->stageItem->save();


		$this->_logTicketChange($ticketId, $u->userId, "incident date", $oldType, $attType, $u->username);
		$response->result = 'good';
	}

	/**
	 * This is here for automatic permission mapping
	 * in main.php
	 */
	public function updatePointsAction($request, $response) {
		$this->updateFieldAction($request, $response);
	}

	public function updateFieldAction($request, $response) {
		$field     = $request->cleanString('name');
		$fieldDesc = $request->cleanString('fd');
		$u = $request->getUser();


		$ticketId   = $request->cleanInt('pk');
		$attValue   = $request->cleanFloat('value');
		if ($attValue == 0) {
			$attValue = NULL;
		}
		$ticket = _makeNew('dataitem', 'csrv_ticket');
		if (!$ticket->load($ticketId)) {
			$response->statusCode = 401;
			$response->result = 'error';
			return;
		}
		$ticketModel = _makeNew('ticket_model');
		$attend      = Workflow_Ticketmodel::ticketFactory($ticket);
		$oldValue    = $attend->stageItem->get($field);

		$attend->stageItem->set($field, $attValue);
		$attend->stageItem->save();

		$this->_logTicketChange($ticketId, $u->userId, $fieldDesc, $oldValue , $attValue, $u->username);
		$response->result = 'good';
	}

	public function updateDescAction($request, $response) {
		$u = $request->getUser();

		$allowed = FALSE;

		$ticketId = $request->cleanInt('pk');
		$newValue  = $request->cleanString('value');
		$ticket = _makeNew('dataitem', 'csrv_ticket');
		if (!$ticket->load($ticketId)) {
			$response->result = 'error';
			$response->error_code = 401;
			return;
		}
		$ticketModel = _makeNew('ticket_model');
		$attend      = Workflow_Ticketmodel::ticketFactory($ticket);
		$oldValue    = $attend->stageItem->get('description');
		$attend->stageItem->set('description', $newValue);
		$attend->stageItem->save();

		$this->_logTicketChange($ticketId, $u->userId, "description", $oldValue, $newValue, $u->username);
		$response->result = 'ok';
	}


	public function updateCorrAttAction($request, $response) {
		$u = $request->getUser();

		if (!$this->hasPermission($u, 'attend', 'updateCorrAtt')) {
			$response->result = 'disallowed';
			$response->statusCode = 401;
			return;
		}

		$ticketId   = $request->cleanInt('pk');
		$attValue   = $request->cleanString('value');
		$ticket = _make('ticket_model');
		if (!$ticket->load($ticketId)) {
			$response->result = 'disallowed';
			$response->statusCode = 400;
			return;
		}

		$ticket->loadStage();
		$oldValue = $ticket->stageItem->get('corr_act');
		$ticket->stageItem->set('corr_act', $attValue);
		$ticket->stageItem->save();

		$this->_logTicketChange($ticketId, $u->userId, "Corrective Action", $oldValue, $attValue, $u->username);
		$response->result = 'good';
		$response->newValue = $attValue;
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
				_set(substr($_k, 7), $_v);
			}

			if (strstr($_k,'perm.') ) { 
				_set($_k, $_v);
			}
		}
	}
}
