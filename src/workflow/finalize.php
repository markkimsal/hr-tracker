<?php

/**
 * _iCanHandle('csrv_ticket_closed_approv', 'workflow/finalize.php::approveTicket');
 * _iCanHandle('csrv_ticket_closed_rej',    'workflow/finalize.php::rejectTicket');
 */
class Workflow_Finalize {

	/**
	 * Delete an incident ticket permanently
	 *
	 * Ticket object is $sig->getSource()->ticket;
	 *
	 * @return bool   FALSE if there's some sort of error, it should stop the signal's emitting code
	 */
	public function rejectTicket($sig) {
		$ticket = $sig->get('source')->ticket;

		$type = new Workflow_Tickettype($ticket->get('csrv_ticket_type_id'));
		$className = $type->get('class_name');
		if (stripos($className, 'emp') === FALSE) {
			return FALSE;
		}

		$incidentTicket = Workflow_Ticketmodel::ticketFactory($ticket);
		$incident = $incidentTicket->getStage();
		//object has already been deleted, some kind of error
		if ($incident->_isNew) {
			return FALSE;
		}

		//override row-level access because we are in
		//a part of the code that can only be called by a library.
		$incident->sharingModeDelete = '';
		/*
		$incident->dataItem->echoDelete();
		exit();
		// */
		$incident->delete();
		return TRUE;
	}

	/**
	 * Take an approved attendance and "sign" it by the user.
	 *
	 * @return bool   FALSE if there's some sort of error, it should stop the signal's emitting code
	 */
	public function approveTicket($sig) {
		$ticket = $sig->get('source')->ticket;

		$type = new Workflow_Tickettype($ticket->get('csrv_ticket_type_id'));
		$className = $type->get('class_name');
		if (stripos($className, 'emp') === FALSE) {
			return FALSE;
		}

		$incidentTicket = Workflow_Ticketmodel::ticketFactory($ticket);
		$incident = $incidentTicket->getStage();

		$incident->set('approved', 'Y');
		$incident->save();

		return TRUE;
	}
}
