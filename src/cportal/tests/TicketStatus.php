<?php

require_once(dirname(__FILE__).'/../../../../boot/bootstrap.php');
require_once(CGN_LIB_PATH.'/lib_cgn_data_item.php');
require_once(CGN_LIB_PATH.'/lib_cgn_data_model.php');
include_once( dirname(__FILE__).'/../lib/Cportal_Ticket.php');

Mock::generate('Cportal_Ticket_Status');
Mock::generate('Cgn_DataItem');

class TicketStatus extends UnitTestCase {


	public function testStatusHasGoodNum() {

		Cportal_Ticket_Status::clearStatusList();

		$faux = new MockCgn_DataItem('csrv_ticket_status');
		$newStatus = new Cgn_DataItem('csrv_ticket_status');
		$newStatus->set('csrv_ticket_status_id', 999);
		$newStatus->set('code', 'new');
		$newStatus->set('display_name', 'New');
		$newStatus->set('is_terminal', 0);
		$newStatus->set('is_initial', 1);
		$newStatus->set('abbrv', 'new');
		$newStatus->set('hex_color', '000000');

		$oldStatus = new Cgn_DataItem('csrv_ticket_status');
		$oldStatus->set('csrv_ticket_status_id', 2);
		$oldStatus->set('code', 'close');
		$oldStatus->set('display_name', 'Closed');
		$oldStatus->set('is_terminal', 1);
		$oldStatus->set('is_initial', 0);
		$oldStatus->set('abbrv', 'close');
		$oldStatus->set('hex_color', 'FF0000');


		$fauxStatusList = array(
			999=>$newStatus,
			2=>$oldStatus,
		);

		$faux->setReturnValue('find', $fauxStatusList);
		Cportal_Ticket_Status::setDataFinder($faux);


		$defStatus = Cportal_Ticket_Status::getDefaultStatus();
		$this->assertEqual( 999, $defStatus->get('csrv_ticket_status_id'));
		$this->assertEqual( 'new', $defStatus->get('code'));
		$this->assertEqual( '1', $defStatus->get('is_initial'));
	}
}
