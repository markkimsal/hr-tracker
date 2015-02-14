<?php

class Cgn_Service_Cportal_Acct extends Cgn_Service {

	var $requireLogin = true;
	var $usesConfig = true;
	public $rpp = 100;

	function Cgn_Service_Cportal_Acct () {
		 /*
		$name = 'custserv';
		Cgn_ObjectStore::storeConfig("config://template/default/name", $name);
		 */
	}

	//not needed yet.
	/*
	function preEvent(&$req,&$t) {
		$x = Cgn_Db_Connector::getHandle('cserv');
		Cgn_DbWrapper::setHandle($x);
	}

	function postEvent(&$req,&$t) {
		$x = Cgn_Db_Connector::getHandle('default');
		Cgn_DbWrapper::setHandle($x);
	}
	 */


	/**
	 * Show a dashboard type home screen
	 */
	public function mainEvent(&$req, &$t) {
		$status = new Cgn_DataItem('csrv_ticket_status');
//		$status->andWhere('is_terminal','0');
		$t['status'] = $status->find();

		$type = new Cgn_DataItem('csrv_ticket_type');
		$t['types'] = $type->find();

		$filter = $req->cleanInt('type');

		$ticketsLoader = new Cgn_DataItem('user_account');
		//Scott wants to see all tickets
//		$ticketsLoader->andWhere('is_closed',0);
//		if ($filter != 0) {
//			$ticketsLoader->andWhere('csrv_ticket_type_id',$filter);
//		}
//		$ticketsLoader->hasOne('cgn_user','cgn_user_id','Tuser', 'owner_id');
//		$ticketsLoader->hasOne('user_account','user_account_id','Tacct', 'user_account_id');
//		$ticketsLoader->_cols = array('csrv_ticket.*','Tacct.contact_email');
		//Scott wants tickets show newest first on this page.
//		$ticketsLoader->orderBy('created_on DESC');

		//determine search string
		$srch = $req->cleanString('srch');
		if ($srch === '') {
			$srch = $req->cleanString('terms');
		}

		//determine if the search string is an ID search or not
		$idTerms = array();
		$idSrch = $req->cleanString('id-srch');
		if ($idSrch !== '') {
			$srch = 'id:'.$idSrch;
		}
//		$isIdSearch = $this->getIdSearch($srch, $idTerms);

		//determine if the search string is a date search or not
		$dateTerms = array();
		$dateMonth = $req->cleanInt('quick-srch-month');
		$dateDay = $req->cleanInt('quick-srch-day');
		$dateYear = $req->cleanInt('quick-srch-year');
		if ($dateMonth > 0) {
			$srch = $dateMonth.'-'.$dateDay.'-'.$dateYear;
		}
//		$isDateSearch = $this->getDateSearch($srch, $dateTerms);

		//Lucene Search
		if (!$isDateSearch && !$isIdSearch && $srch !== '') {
			include_once(CGN_LIB_PATH.'/Zend/Search/Lucene.php');
			$ids = $this->searchLucene($srch);
			if (count($ids) > 0) {
				$ticketsLoader->andWhere('csrv_ticket_id',  $ids, 'IN');
			} else {
				$req->getUser()->addMessage('No Results.');
				$t['searchCrit'] = array(
					'total_rec'=>0,
					'rpp'=>$this->rpp,
					'terms'=>$srch,
					'incl-old'=>'0'
				);
				return false;
			}
		}

		//date search
		if ($isDateSearch) {
//			$ticketsLoader->andWhere('csrv_ticket_type_id',$filter);
			$ticketsLoader->andWhere('created_on',$dateTerms['startTime'], '>=');
			$ticketsLoader->andWhere('created_on',$dateTerms['endTime'], '<=');
		}

		if ($isIdSearch) {
//			$ticketsLoader->andWhere('csrv_ticket_type_id',$filter);
			$ticketsLoader->andWhere('csrv_ticket_id','%'.$idTerms['id'].'%',' LIKE'); 
			$t['searchCrit']['terms'] = 'id:'.$idTerms['id'];
		}

		//let client cache search results for 4 min
		header('Expires: '.date('D, d M Y h:i:s T', time()+240));
		header('Cache-Control: public');
		header('Pragma: cache');


		if ($req->cleanInt('page')) {
			$ticketsLoader->limit($this->rpp, $req->cleanInt('page'));
		} else {
			$ticketsLoader->limit($this->rpp);
		}

		//save search criteria
		$searchCrit = array(
			'total_rec'=>$ticketsLoader->getUnlimitedCount(),
			'rpp'=>$this->rpp,
			'terms'=>$srch,
			'type'=>$filter,
			'incl-old'=>'0'
		);

		//do page math for next/prev pages
		$searchPages = array (
			'current_page'=>$req->cleanInt('page'),
			'next_page'=>$req->cleanInt('page')+1,
			'last_page'=>ceil($searchCrit['total_rec'] / $this->rpp)-1,
			'prev_page'=>$req->cleanInt('page')-1,
			'first_page'=>'0'
		);
		//don't allow broken next/prev links
		if ($searchPages['next_page'] >= $searchPages['last_page'] ) {
			$searchPages['next_page'] = $searchPages['last_page'];
		}
		if ($searchPages['prev_page'] < $searchPages['first_page'] ) {
			$searchPages['prev_page'] = $searchPages['first_page'];
		}

		$t['searchCrit']  = $searchCrit;
		$t['searchPages'] = $searchPages;


		$t['newTickets'] = $ticketsLoader->find();

		if ($filter != '') {
			$t['tabOn'] = $filter;
		} else {
			$t['tabOn'] = 0;
		}

//		self::setupSidebar();

	}

	public static function formatDate($date)
	{
		if ($date == 0) {
			return 'N/A';
		}
		return date('M jS \'y', $date);
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
}

?>
