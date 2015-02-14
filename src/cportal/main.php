<?php

//class Cgn_Service_Main_Main extends Cgn_Service_Cportal_Main { }

class Main_Main extends Cportal_Main { }
class Cportal_Main {

	public $requireLogin = true;
	public $usesConfig   = true;
	public $usesPerms    = true;

	function __construct () {
	}

	/**
	 * Show a dashboard type home screen
	 */
	function mainAction($request, $response, $user) {
		$status = _make('dataitem', 'csrv_ticket_status');
		$status->andWhere('is_terminal', '0');
		$status->_rsltByPkey = TRUE;
		$response->status = $status->find();

		$type = _make('dataitem', 'csrv_ticket_type');
		$type->_rsltByPkey = TRUE;
		$response->types = $type->find();

		$tickets = array();
		foreach($response->status as $_st) {
			$loader = _makeNew('dataitem', 'csrv_ticket');
			$loader->_cols[] = 'count(*) as total';
//			$loader->groupBy('csrv_ticket_type_id');
			$loader->andWhere('csrv_ticket_status_id',$_st->csrv_ticket_status_id);
			$loader->andWhere('is_closed',0);
			$tickets[$_st->csrv_ticket_status_id] = $loader->find();
		}
		$response->tickets = $tickets;


		$userTickets = array();
		foreach($response->status as $_st) {
			//users should not be able to change the status to new.
			if ($_st->csrv_ticket_status_id == 1) { continue; }
			$loader = _makeNew('dataitem', 'csrv_ticket');
			$loader->_cols[] = 'csrv_ticket_id';
//			$loader->groupBy('csrv_ticket_type_id');
			$loader->andWhere('csrv_ticket_status_id',$_st->csrv_ticket_status_id);
			$loader->andWhere('is_closed',0);
			$loader->andWhere('owner_id', $user->userId);
			$userTickets[$_st->csrv_ticket_status_id] = $loader->find();
		}
		$response->userTickets = $userTickets;

		$newTicketsLoader = _makeNew('dataitem', 'csrv_ticket');
		$newTicketsLoader->andWhere('is_closed', 0);
		$newTicketsLoader->andWhere('csrv_ticket_status_id', 1);
		$newTicketsLoader->hasOne('user_login','user_login_id', 'owner_id', 'Tuser');
		$newTicketsLoader->hasOne('user_account','user_account_id','user_account_id','Tacc');
		$newTicketsLoader->_cols = array('csrv_ticket.*','Tuser.username', 'Tacc.contact_email', 'Tacc.lastname', 'Tacc.firstname');
		$newTicketsLoader->sort('created_on', 'DESC');

		$newLimit = _get('dashboard_new_limit', 20);
		$newTicketsLoader->limit($newLimit);
		$response->newTickets = $newTicketsLoader->find();
		$response->newLimit   = $newLimit;


		$recentTicketsLoader = _makeNew('dataitem', 'csrv_ticket');
		$recentTicketsLoader->andWhere('is_closed',0);
		$recentTicketsLoader->andWhere('csrv_ticket_status_id',1, '!=');
		$recentTicketsLoader->hasOne('user_login','user_login_id', 'owner_id', 'Tuser');
		$recentTicketsLoader->hasOne('user_account','user_account_id', 'user_account_id', 'Tacc');
		$recentTicketsLoader->_cols = array('csrv_ticket.*','Tuser.username', 'Tacc.contact_email', 'Tacc.lastname', 'Tacc.firstname');
		$recentTicketsLoader->sort('created_on','DESC');

		$oldLimit = _get('dashboard_old_limit', 20);
		$newTicketsLoader->limit($oldLimit);
		$response->set('recentTickets', $recentTicketsLoader->find());
		$response->oldLimit      = $oldLimit;

		//self::setupSidebar();

		_set('page.header', 'Dashboard');
	}


	/**
	 * Setup message queue info and ticket list window
	 */
	function setupSidebar() {
		//TODO: fixme this needs to be ported to a more pluggable arch.
		return;

//		$key = 'layout/content/mxq';
//		$val = dirname(__FILE__).'/csrv_queueinfo.php:Csrv_QueueInfo:csrv_queueinfo:showMainContent';

		$key2 = 'layout/content/ticketlist';
		$val2 = dirname(__FILE__).'/csrv_queueinfo.php:Csrv_QueueInfo:csrv_queueinfo:showTicketList';

		$libPath = Cgn_ObjectStore::getConfig('config://cgn/path/lib');
		$sysPath = Cgn_ObjectStore::getConfig('config://cgn/path/sys');
		$pluginPath = Cgn_ObjectStore::getConfig('config://cgn/path/plugin');
		$filterPath = Cgn_ObjectStore::getConfig('config://cgn/path/filter');

		/*
		$val = str_replace('@lib.path@',$libPath,$val);
		$val = str_replace('@sys.path@',$sysPath,$val);
		$val = str_replace('@plugin.path@',$pluginPath,$val);
		 */
		$val2 = str_replace('@lib.path@',$libPath,$val2);
		$val2 = str_replace('@sys.path@',$sysPath,$val2);
		$val2 = str_replace('@plugin.path@',$pluginPath,$val2);


		for($x=1; $x<2; $x++) {
		includeObject($val2);// Cgn_SystemRunner
			if ($x == 1) { $val = $val2; $key = $key2;}
			$classLoaderPackage = explode(':',$val);
			//if we have a method name (4th position)
			if ( @strlen($classLoaderPackage[3]) ) {
				Cgn_ObjectStore::storeConfig('object://'.$key.'/file',$classLoaderPackage[0]);
				Cgn_ObjectStore::storeConfig('object://'.$key.'/class',$classLoaderPackage[1]);
				Cgn_ObjectStore::storeConfig('object://'.$key.'/name',$classLoaderPackage[2]);
				Cgn_ObjectStore::storeConfig('object://'.$key.'/name',$classLoaderPackage[2]);
				Cgn_ObjectStore::storeConfig('object://'.$key.'/method',$classLoaderPackage[3]);
				//Cgn_ObjectStore::debug();
			} else {
				//we don't have a method name
				Cgn_ObjectStore::storeConfig('object://'.$key,$val);
			}
		}
	}

	public function onAuthFail($req, $res) {
		$res->set('redir', m_appurl('cpemp'));
	}

	public static function formatDate($date)
	{
		return date('M jS \'y', $date);
	}

	public static function formatTime($date)
	{
		return date('G:i a', $date);
	}
}
