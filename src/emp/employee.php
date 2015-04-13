<?php
$file = 'metrodb/datamodel.php';
$container = Metrodi_Container::getContainer();
$container->tryFileLoading($file);

class Emp_Employee extends Metrodb_Datamodel {

	public $dataItem;
	public $accountItem = NULL;
	public $tableName = 'employee';
	public $sharingModeRead='';

	public function createDataItem() {
		$dataItem = _make('dataitem', $this->tableName);
		$dataItem->hire_date    = '';
		$dataItem->emp_status   = 'a';
		$dataItem->user_account_id  = -1;
		$dataItem->group_id   = -1;

		$dataItem->_typeMap['user_account_id'] = 'int';
		$dataItem->_typeMap['edited_on']  = 'ts';
		$dataItem->_typeMap['created_on'] = 'ts';
		$dataItem->_typeMap['hire_date']  = 'date';
		$dataItem->_typeMap['emp_status'] = 'varchar';
		$dataItem->_typeMap['group_id']   = 'int';

		$this->accountItem = new Metrodb_DataItem('user_account');
		$this->accountItem->_typeMap['hire_date'] = 'date';
		return $dataItem;
	}


	/**
	 * Sets the user_account_id parameter
	 */
	public function setAccountId($id) {
		$this->dataItem->set('user_account_id', $id);
	}

	/**
	 * Gets the user_account_id parameter
	 */
	public function getAccountId() {
		return $this->dataItem->get('user_account_id');
	}

	public function getFirstname() {
		if ($this->getAccountId() < 1) {
			return '';
		}
		if ($this->accountItem->_isNew) {
			//load account item
			$this->accountItem->load( $this->getAccountId());
		}

		return $this->accountItem->get('firstname');
	}

	public function getLastname() {
		if ($this->getAccountId() < 1) {
			return '';
		}
		if ($this->accountItem->_isNew) {
			//load account item
			$this->accountItem->load( $this->getAccountId());
		}

		return $this->accountItem->get('lastname');
	}

	public static function findByAnyName($input, $asArray = false, $limit=100) {
		$finder = new Metrodb_DataItem('emp');
		$finder->andWhere('T2.firstname', $input.'%', 'LIKE');
		$finder->orWhereSub('T2.lastname', $input.'%', 'LIKE');
//		$finder->andWhere('emp_id', NULL, 'IS NOT');
		$finder->hasOne('user_account', 'user_account_id', 'user_account_id', 'T2');
		$finder->andWhere('emp_status', 'active');
		$finder->sort('T2.lastname', 'ASC');
		$finder->limit($limit);
		$finder->_rsltByPkey = false;


		if ($asArray) {
			return $finder->findAsArray();
		}
		return $finder->find();
	}

	/**
	 * Return an array of data items.
	 */
	public function loadRollingIncidents($rolling=6, $period='MONTH') {
		$incidentFinder = _makeNew('dataitem', 'emp_wpi');
		$incidentFinder->andWhere('emp_id', $this->get('employee_id'));
		$incidentFinder->andWhere('incident_date', 'DATE_SUB(CURDATE(),INTERVAL '.$rolling.' '.$period.')', '>', false);
		$incidentFinder->sort('incident_date');
		$incidentFinder->_rsltByPkey = false;
		return $incidentFinder->findAsArray();
	}

	/**
	 * Return an array of data items.
	 */
	public function loadRollingAttendance($days=365) {
		$incidentFinder = _makeNew('dataitem', 'emp_att');
		$incidentFinder->andWhere('emp_id', $this->get('employee_id'));
		$incidentFinder->andWhere('incident_date', 'DATE_SUB(CURDATE(),INTERVAL '.$days.' DAY)', '>=', false);
		$incidentFinder->andWhere('code', 'V', '!=');
		$incidentFinder->andWhere('code', 'P', '!=');
		$incidentFinder->sort('incident_date');
		$incidentFinder->_rsltByPkey = false;
		return $incidentFinder->findAsArray();
	}

	/**
	 * Return an array of data items.
	 */
	public function loadRollingAttendancePoints($date, $days=365) {
		$pointFinder = new Metrodb_DataItem('emp_att');
		$pointFinder->_cols = array('SUM(`points`) as points');
		$pointFinder->andWhere('emp_id', $this->get('employee_id'));
		$pointFinder->andWhere('incident_date', 'DATE_SUB(\''.$date.'\',INTERVAL '.$days.' DAY)', '>=', false);
		$pointFinder->andWhere('incident_date', '\''.$date.'\'', '<=', false);
		$pointFinder->andWhere('code', 'V', '!=');
//		$pointFinder->andWhere('approved', 'Y');
		$pointFinder->_rsltByPkey = false;
//		$pointFinder->_debugSql = true;
		$pointList = $pointFinder->findAsArray();
		return $pointList[0]['points'];
	}

	/**
	 * Calculate the number of vacation hours used since employee's hire date
	 * @return float number of hours used
	 */
	public function loadRollingVacationHours() {
		$f = $this->loadRollingVacationRecords();
		$hrs = 0;
		foreach ($f as $_f) {
			$hrs += floatval($_f['vac_hr']);
		}
		return $hrs;
	}

	/**
	 * Find the number of vacation incidents since the employee's hire date anniversary
	 * Find personal days since the beginning of the calendar year.
	 * @return Array list of emp_att records
	 */
	public function loadRollingVacationRecords() {
		//if today's day of year is less than hire date day of year
		// then use last year's year
		// else use current year's year 
		// for year anniversary
		if (intval(date('z')) < intval(date('z', strtotime($this->get('hire_date'))))) {
			$anniversaryYear = intval(date('Y')) - 1;
		} else {
			$anniversaryYear = intval(date('Y'));
		}
		$finder = new Metrodb_DataItem('emp_att');
//		$finder->_cols = array('emp_att.incident_date', 'emp_att.vac_hr', 'emp_att.*');
		$finder->andWhere('emp_att.code', 'V');
//		$finder->orWhereSub('emp_att.code', 'P');
		$finder->andWhere('emp_att.emp_id', $this->get('employee_id'));
		$finder->hasOne('employee', 'employee_id', 'emp_id', 'T0');
		$finder->andWhere('incident_date', 'MAKEDATE('.$anniversaryYear.', DAYOFYEAR(T0.`hire_date`))', '>', false);
		$finder->andWhere('incident_date', 'MAKEDATE('.$anniversaryYear.'+1, DAYOFYEAR(T0.`hire_date`))', '<=', false);
//echo		$finder->echoSelect();
//exit();
//		$finder->sort('incident_date');
		$finder->_rsltByPkey = false;
		$listVacation = $finder->findAsArray();

		$calendarStart = date('Y-m-d', gmmktime(0,0,0,1,1,$anniversaryYear));

		$finder = new Metrodb_DataItem('emp_att');
//		$finder->_cols = array('emp_att.incident_date', 'emp_att.vac_hr', 'emp_att.*');
//		$finder->andWhere('emp_att.code', 'V');
		$finder->andWhere('emp_att.code', 'P');
		$finder->andWhere('emp_att.emp_id', $this->get('employee_id'));
		$finder->hasOne('employee', 'employee_id', 'emp_id', 'T0');
		$finder->andWhere('incident_date', "'".$calendarStart."'", '>', false);
//		$finder->andWhere('incident_date', 'MAKEDATE('.$anniversaryYear.'+1, DAYOFYEAR(T0.`hire_date`))', '<', false);
//		$finder->echoSelect();
//exit();
//		$finder->sort('incident_date');
		$finder->_rsltByPkey = false;
		$listPersonal = $finder->findAsArray();


		return array_merge($listVacation, $listPersonal); //$finder->findAsArray();
	}


	public function getEmploymentStatusName() {
		return $this->get('emp_status');
	}
}
