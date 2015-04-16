<?php
class Emp_Main {

	public $requireLogin  = true;
	public $usesConfig    = true;
	public $usesPerms     = true;
	public $rpp           = 100;

	public function __construct () {
	}
	public function resources() {
		$this->loadConfig();

		_didef('employee_model', 'emp/employee.php');
		_didef('attendance_model', 'emp/attendancemodel.php');
		_didef('wpi_model', 'emp/wpimodel.php');
		_didef('safety_model', 'emp/safetymodel.php');

		$widget   =  'cgn/widget.php';
		$mvc      =  'cgn/mvc.php';
		$mvctable =  'cgn/mvc_table.php';
		$container = Metrodi_Container::getContainer();
		$container->tryFileLoading($widget);
		$container->tryFileLoading($mvc);
		$container->tryFileLoading($mvctable);
	}
	
	public function authorize($request, $response) {
		$appPath = dirname(__FILE__);
		$cfg =  parse_ini_file($appPath.'/config.ini',true);
		if (@file_exists($appPath.'/local.ini') ) { 
			$localCfg = parse_ini_file($appPath.'/local.ini',true);
			$cfg = array_merge($cfg, $localCfg);
		}
		$keySearch = 'perm.'.$request->modName;
		$listGroup = array();
		foreach ($cfg as $_k => $_v) {
			if ($_k == $keySearch) {
				if (!isset($_v[$request->actName])) {
					return TRUE;
				}
				$listGroup = explode(',', $_v[ $request->actName ]);
				break;
			}
		}
		$u = $request->getUser();
		foreach ($listGroup as $_grp) {
			if ($u->belongsToGroup($_grp)) {
				return TRUE;
			}
		}
		$request->unauthorized = TRUE;
		return FALSE;
	}

	/**
	 * Show 100 employees at a time, calculate attendance points based off a rolling 365 cut off date
	 * and calculate vacation hours based off the employee's hire date.
	 */
	public function mainAction($request, $response) {
		$finder = _makeNew('dataitem', 'employee');
		$finder->_cols = array('employee.*', 'T0.*', 
			'SUM( case WHEN DATE_SUB(CURDATE(), INTERVAL 365 DAY) < incident_date then `points` else NULL end) as points',
			'SUM( case
                 WHEN DAYOFYEAR(`hire_date`) - DAYOFYEAR(CURDATE()) <  0
                 AND DATE_SUB(incident_date, INTERVAL (DAYOFYEAR(`hire_date`) - DAYOFYEAR(CURDATE())) DAY) >= CURDATE()
                 THEN `vac_hr`
                 WHEN DAYOFYEAR(`hire_date`) - DAYOFYEAR(CURDATE()) >= 0
                 AND DATE_SUB(incident_date, INTERVAL (365 + (DAYOFYEAR(`hire_date`) - DAYOFYEAR(CURDATE())) ) DAY) >= CURDATE()
                 THEN `vac_hr`
                 ELSE NULL end) as vac_hr'
		);
		$finder->hasOne('user_account', 'user_account_id', 'user_account_id', 'T0');
		$finder->hasOne('emp_att', 'emp_id', 'employee_id', 'T1');
		$finder->andWhere('emp_status', 'active');

		$finder->limit(100);
		$finder->groupBy('employee.employee_id');

		$finder->sort('T0.lastname', 'ASC');

		$response->set('newTickets', $finder->find());

		_set('page.header', 'Employees');
	}


	public function viewAction($request, $response) {
		$model = _make('datamodel');
		$empid = $request->cleanInt('emp_id');
		$emp = _make('employee_model');

		$emp->load($empid);
		$response->emp = $emp;


		$response->vacHours       = $emp->loadRollingVacationHours();
		$vacList                  = $emp->loadRollingVacationRecords();
		$attendanceList           = $emp->loadRollingAttendance();
		$response->attPoints      = 0;
		//fix codes for names
		$attNames = _get('attendance');
		$corrAttNames =_get('corrective.att');
		$attList = array();
		foreach($attendanceList as $_idx => $_att) {
			$response->attPoints  += floatval($_att['points']);
			$_att['displayName']        = $attNames[$_att['code']];
			$_att['actionName']         = $corrAttNames[$_att['corr_act']];
			$_att['cumulativePoints']   = $emp->loadRollingAttendancePoints($_att['incident_date'], 365);
			$attList[$_idx] = $_att;
		}

		$attendanceList = $attList;
		foreach($vacList as $_idx => $_att) {
			$vacList[$_idx]['displayName']  = $attNames[$_att['code']];
			$vacList[$_idx]['actionName']  = $corrAttNames[$_att['corr_act']];
		}

		//ATT
		$widgetlib       =  _make('widget');
		$mvclib          =  _make('mvc');
		//$attendanceTable =  _make('mvctable', $attendanceList);
		$attendanceTable =  new Cgn_Mvc_TableView($attendanceList);
		$attendanceTable->classes[] = 'table';
		$attendanceTable->classes[] = 'table-striped';
		$response->set('attendanceTable', $attendanceTable);

		$dm = $response->attendanceTable->getModel();
		$dm->columns = array( 'points', 'cumulativePoints', 'displayName', 'incident_date', 'actionName', 'owner_initials', 'approved', 'description');
		$dm->headers = array( 'Points', 'Historic', 'Type', 'Date', 'Action', 'Initials', 'Complete', 'Comment');

		$dateRenderer = new Cgn_Mvc_Table_DateRenderer('m/d/Y');
		$dateRenderer->inputFormat = 'date';
		$response->attendanceTable->setColRenderer(3, $dateRenderer);
		$response->attendanceTable->setColWidth(0,'8%');
		$response->attendanceTable->setColWidth(1,'8%');
		$response->attendanceTable->setColWidth(2,'10%');
		$response->attendanceTable->setColWidth(3,'15%');
		$response->attendanceTable->setColWidth(4,'18%');
		$response->attendanceTable->setColWidth(5,'7%');
		$response->attendanceTable->setColWidth(6,'10%');
		$response->attendanceTable->setColWidth(7,'24%');
		$response->attendanceTable->attribs['border']=0;


		//VAC
		$response->set('vacationTable',  new Cgn_Mvc_TableView($vacList));
		$dm = $response->vacationTable->getModel();
		$dm->columns = array( 'vac_hr', 'displayName', 'incident_date', 'actionName', 'owner_initials', 'approved', 'description');
		$dm->headers = array( 'Hours', 'Type', 'Date', 'Action', 'Initials', 'Complete', 'Comment');

		$dateRenderer = new Cgn_Mvc_Table_DateRenderer('m/d/Y');
		$dateRenderer->inputFormat = 'date';
		$response->vacationTable->setColRenderer(2, $dateRenderer);

		$response->vacationTable->setColWidth(0,'10%');
		$response->vacationTable->setColWidth(1,'10%');
		$response->vacationTable->setColWidth(2,'15%');
		$response->vacationTable->setColWidth(3,'18%');
		$response->vacationTable->setColWidth(4,'7%');
		$response->vacationTable->setColWidth(5,'10%');
		$response->vacationTable->setColWidth(6,'30%');
		$response->vacationTable->attribs['border']=0;


		//WPI
		$wpiNames     = _get('wpi');
		$corrWpiNames = _get('corrective.wpi');
		$incidentList = $emp->loadRollingIncidents();

		foreach($incidentList as $_idx => $_att) {
			$incidentList[$_idx]['actionName']     = $corrWpiNames[$_att['corr_act']];
			$incidentList[$_idx]['incident_date']  = substr($_att['incident_date'], 0, 10);
			$incidentList[$_idx]['displayName']    = $wpiNames[$_att['code']];
		}


		$response->incidentTable = new Cgn_Mvc_TableView($incidentList);
		$dateRenderer = new Cgn_Mvc_Table_DateRenderer('m/d/Y');
		$dateRenderer->inputFormat = 'date';
		$response->incidentTable->setColRenderer(1, $dateRenderer);

		$dm = $response->incidentTable->getModel();
		$dm->columns = array( 'displayName', 'incident_date', 'actionName', 'owner_initials', 'approved', 'description');
		$dm->headers = array( 'Type', 'Date', 'Action', 'Initials', 'Complete', 'Comment');
		$response->incidentTable->setColWidth(0,'20%');
		$response->incidentTable->setColWidth(1,'15%');
		$response->incidentTable->setColWidth(2,'18%');
		$response->incidentTable->setColWidth(3,'7%');
		$response->incidentTable->setColWidth(4,'10%');
		$response->incidentTable->setColWidth(5,'30%');
		$response->incidentTable->attribs['border']=0;

		$trainingFinder = new Metrodb_DataItem('emp_wtrain');
		$trainingFinder->andWhere('emp_id', $empid);
		$trainingFinder->_rsltByPkey = false;
		$trainingList = $trainingFinder->findAsArray();
		$trainingNames = _get('training');
		foreach($trainingList as $_idx => $_att) {
			$trainingList[$_idx]['displayName']  = $trainingNames[$_att['code']];
		}

		//WTRAIN
		$response->trainingTable = new Cgn_Mvc_TableView($trainingList);
		$dateRenderer = new Cgn_Mvc_Table_DateRenderer('m/d/Y');
		$dateRenderer->inputFormat = 'date';
		$response->trainingTable->setColRenderer(1, $dateRenderer);

		$dm = $response->trainingTable->getModel();
		$dm->columns = array( 'displayName', 'incident_date', 'owner_initials');
		$dm->headers = array( 'Code', 'Date', 'Initials');
		$response->trainingTable->setColWidth(0,'20%');
		$response->trainingTable->setColWidth(1,'73%');
		$response->trainingTable->setColWidth(2,'7%');
		$response->trainingTable->attribs['border']=0;

		//forms
		$forms['attendrec'] = $this->_loadAttendanceForm('attendrec', array('empid'=>$empid));
		$forms['wpi']       = $this->_loadIncidentForm('wpi', array('empid'=>$empid));
		$forms['safetyrec'] = $this->_loadSafetyForm('safetyrec', array('empid'=>$empid));
		$response->set('forms', $forms);

		$response->att_date = date('Y-m-d');
		$response->wpi_date = date('Y-m-d');
		$response->safety_date = date('Y-m-d');


		$emp = _makeNew('employee_model', $request->cleanInt('emp_id'));

		_set('page.header', sprintf("%s, %s", $emp->getLastname(), $emp->getFirstname()));
		_set('page.subheader', 'Attendance Overview');
	}

	/**
	 * Show a form to make a new data item
	 */
	public function createAction($request, $response) {
		$this->_makeDataModel($request);

		//make the form
		$f = $this->_makeCreateForm($t, $this->dataModel, $request);
		$this->_makeFormFields($f, $this->dataModel, FALSE);
		$response->addTo('main', $f);
	}

	/**
	 * Function to create a default form
	 */
	protected function _makeCreateForm(&$t, $dataModel, $req) {
		$f = _makeNew('form', 'datacrud_01');
		$f->width="auto";
		$f->action = m_appurl($req->appName.'/'.$req->modName.'/save');
		$t['form'] = $f;
		return $f;
	}

	protected function _makeDataModel($req, $id=0) {
/*
		$file = 'cpemp/lib/Cpemp_Employee_Model.php';
		$container = Metrodi_Container::getContainer();
		$container->tryFileLoading($file);
*/
		$this->dataModel = _make('employee_model');

		if ($id > 0 ) {
	//		$this->dataModel->initBlank();
			$this->dataModel->createDataItem();
			$this->dataModel->load($id);
		} else {
//			$this->dataModel->initBlank();
			$this->dataModel->createDataItem();
		}
	}



	/**
	 * Attach form fields to the $f parmaeter
	 *
	 * @void
	 */
	protected function _makeFormFields($f, $dataModel, $editMode=FALSE) {
		$values = $dataModel->valuesAsArray();

		$es = $dataModel->get('emp_status');
		$widget = new Metroform_Form_ElementSelect('emp_status', 'Status');
		$widget->addChoice('Active', 'Active', $es == 'Active'? TRUE:FALSE);
		$widget->addChoice('Terminated', 'Terminated', $es == 'Terminated'? TRUE:FALSE);
		$widget->addChoice('WC Leave', 'WC Leave', $es == 'WC Leave'? TRUE:FALSE);
		$widget->addChoice('Med Leave', 'Med Leave', $es == 'Med Leave'? TRUE:FALSE);
		$widget->addChoice('Leave', 'Leave', $es == 'Leave'? TRUE:FALSE);
		$widget->addChoice('Promoted', 'Promoted', $es == 'Promoted'? TRUE:FALSE);
		$widget->addChoice('Transfered', 'Transfered', $es == 'Transfered'? TRUE:FALSE); 
		$widget->size = 1;
		$f->appendElement($widget, $es);

		$widget = new Metroform_Form_ElementInput('hire_date', 'Hire Date');
		$widget->size = 55;
		$f->appendElement($widget, $dataModel->get('hire_date'));
		unset($widget);

		//load account object.
		$widget = new Metroform_Form_ElementInput('firstname', 'First name');
		$f->appendElement($widget, $dataModel->getFirstname());

		$widget = new Metroform_Form_ElementInput('lastname', 'Last name');
		$f->appendElement($widget, $dataModel->getLastname());

		if ($editMode == TRUE) {
			$f->appendElement(new Metroform_Form_ElementHidden('id'), $dataModel->getPrimaryKey());
		}
	}

	/**
	 * Crud for overridden to apply Account data
	 */
	protected function _applyDataModelValues($req) {
		$vals = $this->dataModel->valuesAsArray();

		foreach ($vals as $_key => $_val) {
			if ($_key == $this->dataModel->get('_pkey')) {continue;}
			if ($req->hasParam($_key)) {
				$cleaned = $req->cleanString($_key);
				$this->dataModel->set($_key, $cleaned);
			}
		}
		$this->dataModel->accountItem->set('firstname', $req->cleanString('firstname'));
		$this->dataModel->accountItem->set('lastname', $req->cleanString('lastname'));
		$acctId = $this->dataModel->accountItem->save();
		$this->dataModel->set('user_account_id', $acctId);
	}


	public function queryEvent($req, $response) {
		$response->identifier = 'cpemp_id';
		$response->label = 'firstname';
		$search = substr($req->cleanString('displayname'), 0, -1);
		if (strlen($search) < 1) {
			$limit = 100;
		} else {
			$limit = 5;
		}
		$response->items = Cpemp_Employee_Model::findByAnyName($search, TRUE, $limit);
		foreach ($response->items as $k => $v) {
			$response->items[$k]['displayname'] = $response->items[$k]['lastname'] .', '.$response->items[$k]['firstname'];
		}

		$arr[]=array('lastname'=>'smith', 'user_account_id'=>99,'firstname'=>'bob',  "contact_email"=>"","title"=>"","org_name"=>null,"birth_date"=>"0","ref_id"=>"","ref_no"=>"0");
		$arr[]=array('firstname'=>'steve', 'lastname'=>'smith', 'user_account_id'=>98, "contact_email"=>"","title"=>"","org_name"=>null,"birth_date"=>"0","ref_id"=>"","ref_no"=>"0");
		$arr[]=array('firstname'=>'barry', 'lastname'=>'smith', 'user_account_id'=>97, "contact_email"=>"","title"=>"","org_name"=>null,"birth_date"=>"0","ref_id"=>"","ref_no"=>"0");

		//$response->items = $arr;

	}

	public function test_queryEvent($req, $response) {
		$arr[]=array('firstname'=>'bob', 'lastname'=>'smith', 'user_account_id'=>99);
		$arr[]=array('firstname'=>'steve', 'lastname'=>'smith', 'user_account_id'=>98);
		$arr[]=array('firstname'=>'barry', 'lastname'=>'smith', 'user_account_id'=>97);
		$response->identifier = 'user_account_id';
		$response->label = 'firstname';
		$response->items = $arr;
	}

	public static function formatDate($date)
	{
		if (strlen($date) > 4) {
			//date is a string
			$date = strtotime($date);
		}
		if ($date == 0) {
			return 'N/A';
		}
		return date('m/d/Y', $date);
	}

	public static function formatTime($date)
	{
		if ($date == 0) {
			return 'N/A';
		}
		return date('G:i a', $date);
	}

	public static function formatName($fname, $lname)
	{
		return $lname.', '.$fname;
	}

	/**
	 * Auto-generate a form using the form library
	 */
	public function _loadIncidentForm($formid, $values=array(), $edit=false) {
		$form = 'metroform/form.php';
		$file = 'emp/form.php';
		$container = Metrodi_Container::getContainer();
		$container->tryFileLoading($file);
		$container->tryFileLoading($form);

		$corrective = _get('corrective.wpi');
		return Emp_Form::loadIncidentForm($formid, $values, $edit, $corrective);
	}

	/**
	 * Auto-generate a form using the form library
	 */
	public function _loadSafetyForm($formid, $values=array(), $edit=false) {
		$form = 'metroform/form.php';
		$file = 'emp/form.php';
		$container = Metrodi_Container::getContainer();
		$container->tryFileLoading($file);
		$container->tryFileLoading($form);

		$training = _get('training');
		return Emp_Form::loadSafetyForm($formid, $values, $edit, $training);
	}

	/**
	 * Auto-generate a form using the form library
	 */
	public function _loadAttendanceForm($formid, $values=array(), $edit=false) {
		$form = 'metroform/form.php';
		$file = 'emp/form.php';
		$container = Metrodi_Container::getContainer();
		$container->tryFileLoading($form);
		$container->tryFileLoading($file);

		$corrective          = _get('corrective.att');
		$attendanceTypeList  = _get('attendance');
		return Emp_Form::loadAttendanceForm($formid, $values, $edit, $corrective, $attendanceTypeList);
	}


	public function debugEvent($req, $response) {
echo "<pre>\n";
		$db = Metrodb_Connector::getHandle();
		$db->query('
SELECT cpemp.*,T0.*
	
	FROM cpemp
	 LEFT JOIN `user_account` AS T0 
	ON cpemp.user_account_id = T0.`user_account_id`
--	LEFT JOIN `cpemp_att` AS T1 
--	ON cpemp.cpemp_id = T1.`cpemp_id`  
	 where 1=1 
--	and (incident_date >=  DATE_SUB(CURDATE(),INTERVAL 365 DAY)  or incident_date IS NULL ) 
	and emp_status = "active" 
	 GROUP BY  cpemp.cpemp_id 
	ORDER BY  T0.lastname  LIMIT 0, 100
');
		while ($db->nextRecord()) var_dump($db->record);
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
		}
	}


	/**
	 * Save an object.
	 *
	 * This method calls
	 *
	 * _makeDataModel($req, $id)
	 * _applyDataModelValues($req)
	 * _saveDataModel()
	 * and
	 * redirectHome
	 *
	 * in that order
	 */
	public function saveAction($request, $response) {
		$id = $request->cleanInt('id');

		$this->_makeDataModel($request, $id);

		$this->_applyDataModelValues($request);
		$x = $this->_saveDataModel();
		$response->redir = $this->getHomeUrl($request);
		$this->item = $this->dataModel;
	}

	protected function getHomeUrl($req, $params = array()) {
		return m_appurl($req->appName.'/'.$req->modName, $params);
	}

	/**
	 * Saves $this->dataModel
	 *
	 * @return Int  primary key of saved item or false on error
	 */
	protected function _saveDataModel() {
		return $this->dataModel->save();
	}
}

