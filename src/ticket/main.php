<?php


class Ticket_Main {

	var $requireLogin = TRUE;
	var $rpp          = 200; //records per page
	var $usesPerms    = TRUE;

	public function resources() {
		associate_iAmA('datamodel', 'metrodb/datamodel.php');
		associate_getMeA('datamodel');
		include('src/cportal/lib/Cportal_Ticket.php');
	}
	function __construct() {
		/*
		$name = 'custserv';
		Cgn_ObjectStore::storeConfig("config://template/default/name", $name);
		 */
	}

	/**
	 * Own the ticket to the current user, if it's not locked and unowned.
	 * Also change the status to Processing.
	 *
	 * This event checks the permission "perm.TICKET_CODE" for a list of allowed groups
	 */
	function editAction($request, $response) {
		self::setupSidebar();

		$type = _getMeANew('dataitem', 'csrv_ticket_type');
		$response->types = $type->find();

		$response->editMode = TRUE;

		$status = _getMeANew('dataitem', 'csrv_ticket_status');
//		$status->andWhere('is_terminal', 0);
//		$status->andWhere('is_initial', 0);
		$response->status = $status->find();


		$ticket = _getMeANew('dataitem', 'csrv_ticket');
		$ticket->load($request->cleanInt('id'));
		if ($ticket->_isNew) {
			trigger_error('cannot find ticket id #'.$request->cleanInt('id'));
			return false;
		}

		$u = $request->getUser();
		$canOwn = $this->_checkTicketGroupPerms($u, $ticket, $response->types);
		if (!$canOwn) {
			$u->addSessionMessage('No permission to lock tickets of this type.');
			$this->presenter = 'redirect';
			$response->url = m_appurl('cportal/ticket/view', 
				array('id'=>$request->cleanInt('id'))
			);
			return false;

		}

		if ($ticket->is_locked == 1 && $ticket->owner_id != $u->userId) {
			$u->addSessionMessage('Ticket #'.$ticket->csrv_ticket_id.' is locked.');
			$this->presenter = 'redirect';
			$response->url = m_appurl('cportal/ticket/viewlock', 
				array('id'=>$request->cleanInt('id'))
			);
			return false;
		}

		$this->appendTicketList($request->cleanInt('id'), Cportal_Ticket_Type::getCodeLetter($ticket->csrv_ticket_type_id));

		if ($ticket->csrv_ticket_status_id == 1) {
			foreach ($response->status as $_st) {
				if ($_st->code == 'proc') {
					$oldValue = $response->status[$ticket->csrv_ticket_status_id]->display_name;
					$newValue = $response->status[$ticket->csrv_ticket_status_id]->display_name;
					$ticket->csrv_ticket_status_id = $_st->csrv_ticket_status_id;
					$ticket->save();
					$this->logTicketChange($ticket->csrv_ticket_id, $u->userId, 'Status', $oldValue, $newValue, $u->username);
				}
			}
		}

		if ($ticket->is_locked == 0) {
			$ticket->is_locked = 1;
			$this->logTicketChange($ticket->csrv_ticket_id, $u->userId, 'Lock', '0', 1, $u->username);
			$ticket->save();
		}
		if ($ticket->owner_id != $u->userId) {
			$oldUser = Cgn_User::load($ticket->owner_id);
			if (is_object($oldUser)) {
				$oldValue = $oldUser->username;
			}
			if ($oldValue == '') { $oldValue = 'nobody'; }
			$ticket->owner_id = $u->userId;
			$this->logTicketChange($ticket->csrv_ticket_id, $u->userId, 'Owner', $oldValue, null, $u->username);
			$ticket->save();
		}

		$response->ticketObj = Cportal_Ticket::ticketFactory($ticket);

		$final = _getMeANew('dataitem', 'csrv_ticket_status');
		$final->andWhere('is_terminal', 1);
		$response->final = $final->find();

		$comments = _getMeANew('dataitem', 'csrv_ticket_comment');
		$comments->andWhere('csrv_ticket_id', $ticket->csrv_ticket_id);
		$response->comments = $comments->find();
	}

	function viewEvent($request, $response) {
		$ticket = new Metrodb_Dataitem('csrv_ticket');
		$ticket->load($request->cleanInt('id'));
		if ($ticket->_isNew) {
			trigger_error('cannot find ticket id #'.$request->cleanInt('id'));
			return false;
		}

		$response->editMode = FALSE;

		$this->appendTicketList($request->cleanInt('id'), Cportal_Ticket_Type::getCodeLetter($ticket->csrv_ticket_type_id));

		$ticket->save();

		$response->ticketObj = Cportal_Ticket::ticketFactory($ticket);

		$type = new Metrodb_Dataitem('csrv_ticket_type');
		$response->types = $type->find();

		$status = new Metrodb_Dataitem('csrv_ticket_status');
//		$status->andWhere('is_terminal', 0);
		$response->status = $status->find();

		$comments = new Metrodb_Dataitem('csrv_ticket_comment');
		$comments->andWhere('csrv_ticket_id', $ticket->csrv_ticket_id);
		$response->comments = $comments->find();

		//use the edit template, but send a flag for viewonly mode
		$myTemplate =& Cgn_ObjectStore::getObject("object://defaultOutputHandler");
		$myTemplate->contentTpl = 'ticket_edit';
		$response->viewonly = true;

		self::setupSidebar();
	}

	function viewlockEvent($request, $response) {
		$ticket = new Metrodb_Dataitem('csrv_ticket');
		$ticket->load($request->cleanInt('id'));

		$response->ticketObj = Cportal_Ticket::ticketFactory($ticket);
	}

	function breaklockEvent($request, $response) {
		if (isset($request->vars['cncl-btn'])) {
			$this->presenter = 'redirect';
			$response->url = m_appurl('cportal/main'); 
			return false;
		}
		//assume submit button
		$ticket = new Metrodb_Dataitem('csrv_ticket');
		$ticket->load($request->cleanInt('id'));
		$u = $request->getUser();

		if ($ticket->_isNew) {
			$u->addSessionMessage('Lost Ticket ID');
			$this->presenter = 'redirect';
			$response->url = m_appurl('cportal/main'); 
			return false;
		}

		$this->unlockTicket($ticket,$u);

		//take ownership?
		if (isset($request->vars['take'])) {
			$this->ownTicket($ticket, $u);
			$this->presenter = 'redirect';
			$response->url = m_appurl('cportal/ticket/edit', array('id'=>$ticket->csrv_ticket_id));
			return true;
		}

		$this->presenter = 'redirect';
		$response->url = m_appurl('cportal/main');
	}

	function unlockEvent($request, $response) {
		$ticket = new Metrodb_Dataitem('csrv_ticket');
		$ticket->load($request->cleanInt('id'));
		$u = $request->getUser();

		if ($ticket->is_locked == 1 && $ticket->owner_id != $u->userId) {
			$u->addSessionMessage('Ticket #'.$ticket->csrv_ticket_id.' is not owned by you.');
			$this->presenter = 'redirect';
			$response->url = m_appurl('cportal/main'); 
			return false;
		}

		$this->unlockTicket($ticket,$u);
		$this->presenter = 'redirect';
		$response->url = m_appurl('cportal/main');
	}

	function unlockTicket($ticket, $u) {

		$oldValue = $ticket->is_locked;
		$ticket->is_locked = 0;
		$ticket->save();

		$u->addSessionMessage('Ticket #'.$ticket->csrv_ticket_id.' unlocked.');

		//log it
		$this->logTicketChange($ticket->csrv_ticket_id, $u->userId, 'Lock', $oldValue, 0, $u->username);
	}

	function ownTicket($ticket, $u) {
		$oldUser = Cgn_User::load($ticket->owner_id);
		$oldValue = $oldUser->username;
		if ($oldValue == '') { $oldValue = 'nobody'; }
		$ticket->owner_id = $u->userId;
		$this->logTicketChange($ticket->csrv_ticket_id, $u->userId, 'Owner', $oldValue, null, $u->username);
		$ticket->save();
	}

	function commentEvent($request, $response) {
		$ticket = new Metrodb_Dataitem('csrv_ticket');
		$id = $request->cleanInt('id');
		$ticket->load($request->cleanInt('id'));
		if ($id < 1) { 
			$u->addSessionMessage('No ID sent, somethign is broken. #'.$ticket->csrv_ticket_id);
			$this->presenter = 'redirect';
			$response->url = m_appurl('cportal/main');
			return false;
		}

		$comment = new Metrodb_Dataitem('csrv_ticket_comment');
		$comment->message = $request->cleanMultiLine('comment');
		$comment->csrv_ticket_id = $ticket->csrv_ticket_id;
		$comment->created_on = time();
		$comment->author_id = $request->getUser()->userId;
		$comment->author    = $request->getUser()->username;
		$comment->save();

		$u = $request->getUser();
		$u->addSessionMessage('Note added to ticket #'.$ticket->csrv_ticket_id);

		$this->presenter = 'redirect';
		$response->url = m_appurl('cportal/ticket/edit', array('id'=>$ticket->csrv_ticket_id));
	}


	function statusEvent($request, $response) {
		$ticket = new Metrodb_Dataitem('csrv_ticket');
		$id = $request->cleanInt('id');
		if ($id < 1) { 
			$u->addSessionMessage('No ID sent, somethign is broken. #'.$ticket->csrv_ticket_id);
			$this->presenter = 'redirect';
			$response->url = m_appurl('cportal/main');
			return false;
		}

		$ticket->load($request->cleanInt('id'));
		$statusId = $request->cleanInt('status_id');
		if ($statusId === 0 ) {
			$u = $request->getUser();
			$u->addSessionMessage('Not a valid status.');
			$this->presenter = 'redirect';
			$response->url = m_appurl('cportal/ticket/edit', array('id'=>$id));
			return false;
		}


		$oldStatus = $ticket->csrv_ticket_status_id;
		$ticket->csrv_ticket_status_id = $statusId;
		$ticket->save();

		$u = $request->getUser();
		$u->addSessionMessage('Status changed: Ticket #'.$ticket->csrv_ticket_id);

		//log it
		$status = new Metrodb_Dataitem('csrv_ticket_status');
		$status->load($oldStatus);
		$oldValue = $status->display_name;

		$newStatus = new Metrodb_Dataitem('csrv_ticket_status');
		$newStatus->load($statusId);
		$newValue = $newStatus->display_name;


		$this->logTicketChange($ticket->csrv_ticket_id, $u->userId, 'Status', $oldValue, $newValue, $u->username);

		$this->presenter = 'redirect';
		$response->url = m_appurl('cportal/ticket/edit', array('id'=>$id));
	}


	function mainEvent($request, $response) {

		$status = new Metrodb_Dataitem('csrv_ticket_status');
//		$status->andWhere('is_terminal','0');
		$response->status = $status->find();

		$type = new Metrodb_Dataitem('csrv_ticket_type');
		$response->types = $type->find();

		$filter = $request->cleanInt('type');

		$ticketsLoader = new Metrodb_Dataitem('csrv_ticket');
		//Scott wants to see all tickets
//		$ticketsLoader->andWhere('is_closed',0);
		if ($filter != 0) {
			$ticketsLoader->andWhere('csrv_ticket_type_id',$filter);
		}
		$ticketsLoader->hasOne('user_login','user_login_id','Tuser', 'owner_id');
		$ticketsLoader->hasOne('user_account','user_account_id','Tacc', 'user_account_id');
		$ticketsLoader->_cols = array('csrv_ticket.*','Tuser.username', 'Tacc.contact_email', 'Tacc.lastname', 'Tacc.firstname');
		//Scott wants tickets show newest first on this page.
		$ticketsLoader->orderBy('created_on DESC');

		//determine search string
		$srch = $request->cleanString('srch');
		if ($srch === '') {
			$srch = $request->cleanString('terms');
		}

		//determine if the search string is an ID search or not
		$idTerms = array();
		$idSrch = $request->cleanString('id-srch');
		if ($idSrch !== '') {
			$srch = 'id:'.$idSrch;
		}
		$isIdSearch = $this->getIdSearch($srch, $idTerms);

		//determine if the search string is a date search or not
		$dateTerms = array();
		$dateMonth = $request->cleanInt('quick-srch-month');
		$dateDay = $request->cleanInt('quick-srch-day');
		$dateYear = $request->cleanInt('quick-srch-year');
		if ($dateMonth > 0) {
			$srch = $dateMonth.'-'.$dateDay.'-'.$dateYear;
		}
		$isDateSearch = $this->getDateSearch($srch, $dateTerms);

		//determine if search string is a status or not
		$statusTerms = array();
		$isStatusSearch = $this->getStatusSearch($srch, $statusTerms);

		//Lucene Search
		if (!$isDateSearch && !$isIdSearch && !$isStatusSearch && $srch !== '') {
			include_once(CGN_LIB_PATH.'/Zend/Search/Lucene.php');
			$ids = $this->searchLucene($srch);
			if (count($ids) > 0) {
				$ticketsLoader->andWhere('csrv_ticket_id',  $ids, 'IN');
			} else {
				$request->getUser()->addMessage('No Results.');
				$response->searchCrit = array(
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
			$response->searchCrit['terms'] = 'id:'.$idTerms['id'];
		}

		if ($isStatusSearch) {
			$_st = array_shift($statusTerms);
			$ticketsLoader->andWhere('csrv_ticket_status_id', $_st); 
			foreach( $statusTerms as $_st) {
				$ticketsLoader->orWhereSub('csrv_ticket_status_id', $_st); 
			}
//			$response->searchCrit['terms'] = 'id:'.$idTerms['id'];
		}

		//let client cache search results for 4 min
		header('Expires: '.date('D, d M Y h:i:s T', time()+240));
		header('Cache-Control: public');
		header('Pragma: cache');


		if ($request->cleanInt('page')) {
			$ticketsLoader->limit($this->rpp, $request->cleanInt('page'));
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
			'current_page'=>$request->cleanInt('page'),
			'next_page'=>$request->cleanInt('page')+1,
			'last_page'=>ceil($searchCrit['total_rec'] / $this->rpp)-1,
			'prev_page'=>$request->cleanInt('page')-1,
			'first_page'=>'0'
		);
		//don't allow broken next/prev links
		if ($searchPages['next_page'] >= $searchPages['last_page'] ) {
			$searchPages['next_page'] = $searchPages['last_page'];
		}
		if ($searchPages['prev_page'] < $searchPages['first_page'] ) {
			$searchPages['prev_page'] = $searchPages['first_page'];
		}

		$response->searchCrit  = $searchCrit;
		$response->searchPages = $searchPages;


		$response->newTickets = $ticketsLoader->find();

		if ($filter != '') {
			$response->tabOn = $filter;
		} else {
			$response->tabOn = 0;
		}

		self::setupSidebar();
	}

	/**
	 * Show a form to allow the user to enter a message before finalizing
	 */
	function finalizeEvent($request, $response) {
		$ticket = new Metrodb_Dataitem('csrv_ticket');
		$ticket->load($request->cleanInt('id'));
		$response->finalStatusId = $request->cleanInt('status_id');
		if ($response->finalStatusId === 0 ) {
			$u = $request->getUser();
			$u->addSessionMessage('Not a valid status.');
			$this->presenter = 'redirect';
			$response->url = m_appurl('cportal/ticket/edit', array('id'=>$request->cleanInt('id')));
			return false;
		}

		if ($ticket->owner_id != $request->getUser()->userId) {
			$u = $request->getUser();
			$u->addSessionMessage('Ticket #'.$ticket->csrv_ticket_id.' unlocked.');
			return false;
		}

		$type = new Metrodb_Dataitem('csrv_ticket_type');
		$response->types = $type->find();

		$status = new Metrodb_Dataitem('csrv_ticket_status');
		$status->andWhere('is_terminal','1');
		$response->status = $status->find();


		$response->ticketObj = Cportal_Ticket::ticketFactory($ticket);
//		self::setupSidebar();
	}


	/**
	 * Close this ticket, unlock it, and send a signal.
	 * Signal could be one of:
	 *   csrv_ticket_closed_approv
	 *   csrv_ticket_closed_rej
	 *   csrv_order_closed_approv
	 *   csrv_order_closed_rej
	 */
	function closeEvent($request, $response) {
		$ticket = new Metrodb_Dataitem('csrv_ticket');
		$id = $request->cleanInt('id');
		$finalStatusId = $request->cleanInt('status_id');
		$u = $request->getUser();
		if ($id < 1) { 
			$u->addSessionMessage('Note added to ticket #'.$ticket->csrv_ticket_id);
			$this->presenter = 'redirect';
			$response->url = m_appurl('cportal/main');
			return false;
		}

		$ticket->load($request->cleanInt('id'));

		$ticket->csrv_ticket_status_id = $finalStatusId;
		$ticket->is_locked = 0;
		$ticket->is_closed = 1;
		$ticket->edited_on = time();

		$ticket->csrv_ticket_type_id = (int)$ticket->csrv_ticket_type_id;

		$statusCode = Cportal_Ticket_Status::getStatusCode($finalStatusId);
		$signalName = 'csrv_ticket_closed_'.$statusCode;

		/*
		if ($ticket->csrv_ticket_type_id === Cportal_Ticket_Type::$TICK_TYPE_ORDER) {
			$signalName = 'csrv_order_closed_'.$statusCode;

		}
		 */

		//Send a signal that this ticket was closed
		//could be :
		// csrv_ticket_closed_approv
		// csrv_ticket_closed_rej
		// csrv_order_closed_approv
		// csrv_order_closed_rej
		$this->ticket = $ticket;
		$signalResult = $this->emit($signalName);

		if ($signalResult !== FALSE) {
			$ticket->save();
		} else {
			$u = $request->getUser();
			$u->addSessionMessage('Unable to CLOSE ticket', 'msg_warn');
			$this->presenter = 'redirect';
			$response->url = m_appurl('cportal/ticket/finalize'). '?status_id='.$finalStatusId.'&id='.$request->cleanInt('id');
			return;
		}
		//save the optional comment
		if ($comment = $request->cleanString('comment')) {
			$comment = new Metrodb_Dataitem('csrv_ticket_comment');
			$comment->message = $request->cleanString('comment');
			$comment->csrv_ticket_id = $ticket->csrv_ticket_id;
			$comment->created_on = time();
			$comment->author_id = $request->getUser()->userId;
			$comment->author    = $request->getUser()->username;
			$comment->save();
		}



		$u = $request->getUser();
		$u->addSessionMessage('Ticket Closed: #'.$ticket->csrv_ticket_id);

		$this->presenter = 'redirect';
		$response->url = m_appurl('cportal/main/main');
	}


	/**
	 * send XML items down to ajax
	 */
	function logEvent($request, $response) {

		$type = new Metrodb_Dataitem('csrv_ticket_type');
		$response->types = $type->find();

		$status = new Metrodb_Dataitem('csrv_ticket_status');
		$status->andWhere('is_terminal', 0);
		$status->andWhere('is_initial', 0);
		$response->status = $status->find();


		$ticket = new Metrodb_Dataitem('csrv_ticket');
		$ticket->load($request->cleanInt('id'));
		if ($ticket->_isNew) {
			trigger_error('cannot find ticket id #'.$request->cleanInt('id'));
			return false;
		}

		$u = $request->getUser();
		//$response->ticketObj = Custserv_Ticket::ticketFactory($ticket);

		$type = $request->cleanString('t');
		$response->items = array();
		$itemTimes = array();
		if ($type == 'comments' || $type == 'both') {
			$comments = new Metrodb_Dataitem('csrv_ticket_comment');
			$comments->andWhere('csrv_ticket_id', $ticket->csrv_ticket_id);
			$comments->sort('created_on', 'ASC');
			$logs = $comments->find();
			foreach ($logs as $_cobj) {
				$response->items[] = $_cobj;
				$itemTimes[] = $_cobj->created_on;
			}
			unset($logs);
		}
		if ($type == 'status' || $type == 'both') {
			$status = new Metrodb_Dataitem('csrv_ticket_log');
			$status->andWhere('csrv_ticket_id', $ticket->csrv_ticket_id);
			$status->sort('created_on', 'ASC');
			$logs = $status->find();
			foreach ($logs as $_cobj) {
				$response->items[] = $_cobj;
				$itemTimes[] = $_cobj->created_on;
			}
			unset($logs);
		}
		//if type is not both, they will be sorted already based on the SQL sort;
		if ($type == 'both') {
			array_multisort($itemTimes,$response->items);
		}

		$this->presenter = 'self';
	}

	function output($request, $response) {
		header ('Content-Type: text/html');
		foreach ($response->items as $_cobj) {
			echo '<li>On '.date('M jS @G:i', $_cobj->created_on).' user <i>'. $_cobj->author.'</i>';
			if ( isset($_cobj->old_value) ) { 
				echo ' changed:<br/>';
				if ( isset($_cobj->new_value) ) { 
					echo nl2br('<b>'.$_cobj->attr.'</b> from &quot;'.$_cobj->old_value.'&quot; => &quot;'.$_cobj->new_value.'&quot;');
				} else {
					echo nl2br('<b>'.$_cobj->attr.'</b> from &quot;'.$_cobj->old_value.'&quot;');
				}
			} else {
				echo ' wrote:<br/>';
				echo nl2br($_cobj->message);
			}
			echo '</li>';
		}
	}


	function setupSidebar() {
//		$modulePath = Cgn::getModulePath('cportal');
//		include_once($modulePath.'/main.php');
//		Cgn_Service_Cportal_Main::setupSidebar();
	}

	function appendTicketList($id, $type='') {
		if (! isset($_COOKIE['ticketlist']) ) {
			$list = '';
		} else {
			$list = $_COOKIE['ticketlist'];
		}
		if ( strlen($list) ) {
			$ticketAr = explode(',',$list);
		} else {
			$ticketAr = array();
		}
		if (! in_array($id, $ticketAr)) {
			if (count($ticketAr) >= 5 ) {
				array_shift($ticketAr);
			}
			$ticketAr[] = $id.':'.$type;
			$list = implode(',',$ticketAr);
			setcookie('ticketlist', $list, 0, '/');
			$_COOKIE['ticketlist'] = $list;
		}
	}

	/**
	 * Search Lucene
	 *
	 * @return array list of database IDs
	 */
	public function searchLucene($terms) {

		if (!Cgn::loadLibrary('Search::lib_Cgn_Search_Index')) {
			return array();
		}

		$l = new Cgn_Search_Index('tickets');


		//basic multi term query.  Clean input string of quotes, use each word 
		//separated by a space as a search term.
		//*
		$query = new Zend_Search_Lucene_Search_Query_MultiTerm();

		//quotes have special meaning in this search language, remove them because
		//they might be inch symbols
//		$terms = str_replace('"', ' ' , $terms);

		//if there's an @ symbol, search the email.
		if (strstr($terms, '@') !== FALSE) {
			$query->addTerm(new Zend_Search_Lucene_Index_Term($terms, 'originator_email'), NULL);
		}

		//just take everything as lucene syntax
		$query->addTerm(new Zend_Search_Lucene_Index_Term($terms),         NULL);

		//multi term all other fields
/*
		$ts = explode(' ', $terms);
		//remove double spaces
		$ts = array_filter($ts, 'strlen');

		//basic search of all fields
		foreach ($ts as $_ts) {
			$query->addTerm(new Zend_Search_Lucene_Index_Term($_ts),         NULL);
		}
		// */


		$hits = $l->find($query);
		$ids = array();
		foreach ($hits as $h) {
			$ids[] = $h->database_id;
		}

		//*
		if  (count($ids) < 1) {
			$query = new Zend_Search_Lucene_Search_Query_Boolean();

			//try harder
			$bodyTerm = new Zend_Search_Lucene_Index_Term($terms, 'body');
			$fuzzyBody = new Zend_Search_Lucene_Search_Query_Fuzzy($bodyTerm, 0.4);
			$query->addSubquery($fuzzyBody, NULL);

			//date search is handled outside of lucene

			$dbTerm = new Zend_Search_Lucene_Index_Term($terms.'*', 'database_id');
			$dbWild = new Zend_Search_Lucene_Search_Query_Wildcard($dbTerm);
			$query->addSubquery($dbWild, NULL);

			//(body = terms~) OR (skus = terms*)

			$hits = $l->find($query);
			$ids = array();
			foreach ($hits as $h) {
				$ids[] = $h->database_id;
			}
		}
		 //*/

		return $ids;
	}

	public function rebuildSearchEvent($request, $response) {
		@ini_set('max_execution_time', 0);
		@set_time_limit(0);
		if (!Cgn::loadModLibrary('Cportal::Lucene_Util')) {
			return array();
		}

		$l = new Cgn_Lucene_Search();
		$index = $l->getIndex();
		$l->rebuildIndex();
	}


	function logTicketChange($ticketId, $userId, $attrName, $oldValue, $newValue=NULL, $username='') {
		//log it
		$log = new Metrodb_Dataitem('csrv_ticket_log');
		$log->_nuls[]        = 'new_value';
		$log->csrv_ticket_id = $ticketId;
		$log->author_id      = $userId;
		$log->author         = $username;
		$log->created_on     = time();
		$log->old_value      = $oldValue;
		$log->new_value      = $newValue;
		$log->attr = $attrName;
		return $log->save();
	}


	public static function formatDate($date)
	{
		return date('M jS \'y', $date);
	}

	public static function formatTime($date)
	{
		return date('G:i a', $date);
	}


	/**
	 * See if the srch parameter is in format %d-%d-%d
	 */
	public function getDateSearch($srch, &$dateTerms) {
		$dateTerms = sscanf($srch, "%d-%d-%d");
		if (is_int($dateTerms[0])
			&& is_int($dateTerms[1])
				&& is_int($dateTerms[2]) ){

					$dateTerms['startTime'] = mktime(0, 0, 0, $dateTerms[0], $dateTerms[1], $dateTerms[2]);
					$dateTerms['endTime']   = mktime(23, 59, 59, $dateTerms[0], $dateTerms[1], $dateTerms[2]);
					return TRUE;
		}
		return FALSE;
	}

	/**
	 * See if the srch parameter is in format %d or "id:%d"
	 */
	public function getIdSearch($srch, &$idTerms) {
		$idTerms = sscanf($srch, "id:%d");
		if (is_int($idTerms[0])){
			$idTerms['id'] = $idTerms[0];
			return TRUE;
		}
/*
		if ((string)intval($srch) === $srch) {
			$idTerms['id'] = intval($src);
			return TRUE;
		}
		 */
		return FALSE;
	}

	protected function _checkTicketGroupPerms($u, $ticket, $typeList) {
		$typeObj = @$typeList[$ticket->get('csrv_ticket_type_id')];
		$typeCode = $typeObj->get('code');
		if (!$typeCode) { return FALSE; }

return true;
		return $this->hasPermission($u, 'ticketOwn', $typeCode);
	}

	/**
	 * See if the srch parameter is a status value, return true
	 *
	 * @return boolean  true if terms are status terms
	 */
	public function getStatusSearch($srch, &$statusTerms) {
		$statusFinder = new Metrodb_Dataitem('csrv_ticket_status');
		$statusList = $statusFinder->find();
		$ts = explode(' ', $srch);
		//remove double spaces
		$ts = array_filter($ts, 'strlen');

		foreach ($ts as $_ts) {
			foreach ($statusList as $_sl) {
				if (strtolower($_sl) == strtolower($_sl->get('display_name'))) {
				$statusTerms[] = $_sl->get('csrv_ticket_status_id');
				}
			}
		}
		if (count($statusTerms)) {
			return TRUE;
		}
		return FALSE;
	}
}

