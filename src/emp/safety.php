<?php

class Emp_Safety {

	public $rpp = 100;

	/**
	 */
	public function saveIncidentAction($request, $response) {
		$u = $request->getUser();

		$cpempid = $request->cleanInt('empid');
		$wpitype = $request->cleanString('safety_type');
		$data = _makeNew('safety_model');

		$data->set('code', $wpitype);
		$data->set('created_on', time());
		$data->set('updated_on', time());
		$data->set('owner_id', $u->userId);
		$data->set('owner_initials', $this->_getUserInitials($u));

		$data->set('incident_date', date('Y-m-d', strtotime($request->cleanString('safety_date'))));
		$data->set('emp_id', $cpempid);

		$values = $data->valuesAsArray();
		$values['safety_type'] = $values['code'];
		$values['safety_date'] = $values['incident_date'];
		$form = $this->_loadSafetyForm();
		if (!$form->validate($values)) {
			//error
			$response->redir = m_appurl('emp/main/view', array('emp_id'=>$cpempid));
			$this->presenter = 'redirect';
//			$u = $request->getUser();
			$response->addTo('sparkMsg', array('msg'=>'Form is missing required fields.', 'type'=>'msg_warn'));
//			$u->addSessionMessage('Form is missing required fields.', 'msg_warn');
			return;
		}


		$ticket = new Cpemp_Wtrain_Ticket();

		$ticket->setStage($data);
		//load up employee account info.
		$emp = _makeNew('employee_model');
		$emp->load($cpempid);
		$acct = new Metrodb_Dataitem('user_account');
		$acct->load($emp->get('user_account_id'));
		$ticket->accountItem = $acct;
		$ticket->save();
//		$data->save();

		$response->redir = m_appurl('emp/main/view', array('emp_id'=>$cpempid));
		$this->presenter = 'redirect';

		$response->addTo('sparkMsg', array('msg'=>'Training saved.', 'type'=>'msg_success'));

	}

	/**
	 * Auto-generate a form using the form library
	 */
	public function _loadSafetyForm($values=array(), $edit=false) {
		include_once('src/cpemp/lib/Cpemp_Form.php');
		return Cpemp_Form::loadSafetyForm($values, $edit);
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
}
