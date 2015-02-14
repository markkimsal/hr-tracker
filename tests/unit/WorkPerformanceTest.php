<?php

class WorkPerformanceTest extends PHPUnit_Framework_TestCase {

	public function setUp() {
		_didef('wpimodel', 'emp/wpimodel.php');
	}

	public function test_new_incident_creates_ticket() {
		$incident = _make('wpimodel');
		$ticket = $incident->getTicketModel();
		$this->assertEquals('metrodi_proto', strtolower(get_class($ticket)));
	}

	public function test_unknown_code() {
		$incident = _make('wpimodel');
		$incident->dataItem->set('code', 'Z');
		$type = $incident->getTypeName();
		$this->assertEquals('(Z) Unknown', $type);
	}

	public function test_approved_starts_as_no() {
		$incident = _make('wpimodel');
		$approved = $incident->dataItem->get('approved');
		$this->assertEquals('N', $approved );
	}
}
