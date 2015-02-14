<?php

require_once(dirname(__FILE__).'/../../../../boot/bootstrap.php');
require_once(CGN_LIB_PATH.'/lib_cgn_data_item.php');
require_once(CGN_LIB_PATH.'/lib_cgn_data_model.php');
include_once( dirname(__FILE__).'/../lib/Cportal_Ticket.php');

Mock::generate('Cgn_DataItem');

class Tickets extends UnitTestCase {


	public function testNewObjectShouldHaveGoodDefaults() {
		$ticket = new Cportal_Ticket();
		$this->assertTrue(true);
	}

	public function testSaveItems() {
		$ticket = new Cportal_Ticket();
		$data  = new MockCgn_DataItem('csrv_ticket');
		$data->setReturnValue('save', 99);
		$data->setReturnValue('get', 98, array('cgn_account_id'));
		$data->expectOnce('set', array('cgn_account_id', 98));
		$ticket->setModel($data);

		$stage = new MockCgn_DataItem('ticket_stage');
		$stage->expectOnce('set', array('csrv_ticket_id', 99));
		$stage->setReturnValue('get', 99, array('csrv_ticket_id'));

		$acct  = new MockCgn_DataItem('cgn_account');
		$acct->expectOnce('save');
		$acct->expectOnce('getPrimaryKey');
		$acct->setReturnValue('getPrimaryKey', 98);

		$ticket->setStage($stage);
		$ticket->accountItem = $acct;
		$ticket->save();

		$this->assertEqual($stage->get('csrv_ticket_id'), 99);

		$this->assertEqual($ticket->dataItem->get('cgn_account_id'), 98);
	}


}
