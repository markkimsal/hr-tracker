<?php

class Emp_Form {

	public static function loadIncidentForm($formid, $values=array(), $edit=false, $actions=array()) {
		//Cgn::loadLibrary('Form::lib_cgn_form');
		//$a = Nofw_Associate::getAssociate();
		//$a->load('cpemp/lib/lib_cgn_form.php');
		$file = 'cpemp/lib/lib_cgn_form.php';
		$container = Metrodi_Container::getContainer();
		$container->tryFileLoading($file);

		//include_once(CGN_LIB_PATH.'/html_widgets/lib_cgn_widget.php');
		$f = new Metroform_Form($formid);
		$f->width="auto";
		$f->action = m_appurl('emp/attend/saveIncident');
		$f->label = 'Record work performance issue';
		$f->setShowTitle(false);
		$f->setShowCancel(false);

		$r1 = new Metroform_Form_ElementHidden('wpi_type', 'W');
		$f->appendElement($r1, 'W');


		$i1 = new Metroform_Form_ElementInput('wpi_date','When did this incident happen?');
		$i1->size=10;
		$i1->attrs['data-provide'] = 'datepicker';
		$f->appendElement($i1, @$values['wpi_date']? $values['wpi_date']: date('m/d/Y'));


		if (count($actions)) {
			$r2 = new Metroform_Form_ElementSelect('wpi_action', 'What corrective action was taken?');
			$r2->size = 1;
			foreach ($actions as $_k => $_v) {
				$r2->addChoice($_v, $_k);
			}
			$r2->required = true;
			$f->appendElement($r2);
		}


		$t1 = new Metroform_Form_ElementText('wpinote1', 'Optional Description', 7, 40);
		$f->appendElement($t1);
//		if ($values['edit'] == true) {
//			$link = new Metroform_Form_ElementInput('link_text','URL text<br/>(optional)');
//			$link->size = 55;
//			$f->appendElement($link,$values['link_text']);
//		}



//		$f->appendElement(new Metroform_Form_ElementHidden('id'),$values['cgn_content_id']);
		$f->appendElement(new Metroform_Form_ElementHidden('empid'), @$values['empid']);

		$f->showSubmit = FALSE;
		return $f;
	}

	public static function loadAttendanceForm($formid, $values=array(), $edit=false, $actions=array(), $types=array() ) {
		//Cgn::loadLibrary('Form::lib_cgn_form');
		//$a = Nofw_Associate::getAssociate();
		//$a->load('cpemp/lib/lib_cgn_form.php');
		$file = 'cpemp/lib/lib_cgn_form.php';
		$container = Metrodi_Container::getContainer();
		$container->tryFileLoading($file);

		//include_once(CGN_LIB_PATH.'/html_widgets/lib_cgn_widget.php');
		$f = new Metroform_Form($formid);
		$f->width="auto";
		$f->action = m_appurl('emp/attend/saveAttendance');
		$f->label = 'Record attendance issue';
		$f->setShowTitle(false);
		$f->setShowCancel(false);

		$r1 = new Metroform_Form_ElementSelect('att_type', 'What type of incident happened?');
		$r1->size = 1;
		$r1->addChoice('[Select an attendance type]', '');

		if (count($types)) {
			foreach ($types as $_k => $_v) {
				$r1->addChoice($_v, $_k);
			}
		} else {
			$r1->addChoice('Vacation', 'V');
			$r1->addChoice('Personal Day', 'P');
			$r1->addChoice('Late', 'L');
			$r1->addChoice('Early Leave', 'E');
			$r1->addChoice('No Call, No Show', 'N');
			$r1->addChoice('Absence (unapproved)', 'A');
			$r1->addChoice('Absence (consecutive)', 'C');
			$r1->addChoice('Bereavement', 'B');
			$r1->addChoice('Medical Leave', 'M');
			$r1->addChoice('Jury Duty', 'J');
			$r1->addChoice('Other', 'O');
		}

		$r1->required = true;
		$f->appendElement($r1);


		if (count($actions)) {
			$r2 = new Metroform_Form_ElementSelect('att_action', 'What corrective action was taken?');
			$r2->size = 1;
			foreach ($actions as $_k => $_v) {
				$r2->addChoice($_v, $_k);
			}
			$r2->required = true;
			$f->appendElement($r2);
		}

		$i1 = new Metroform_Form_ElementInput('att_date','When did this incident happen?');
		$i1->size=10;
		$i1->attrs['data-provide'] = 'datepicker';
		$f->appendElement($i1, @$values['att_date']? $values['att_date']: date('m/d/Y'));

		$i2 = new Metroform_Form_ElementInput('vac_hr','How many vacation hours were used?');
		$i2->size=7;
		$f->appendElement($i2, @$values['vac_hr']? $values['vac_hr']: 0);


		$t1 = new Metroform_Form_ElementText('note2', 'Optional Description', 7, 40);
		$f->appendElement($t1);
//		if ($values['edit'] == true) {
//			$link = new Metroform_Form_ElementInput('link_text','URL text<br/>(optional)');
//			$link->size = 55;
//			$f->appendElement($link,$values['link_text']);
//		}



//		$f->appendElement(new Metroform_Form_ElementHidden('id'),$values['cgn_content_id']);
		$f->appendElement(new Metroform_Form_ElementHidden('empid2'), @$values['empid']);

		$f->showSubmit = FALSE;
		return $f;
	}

	public static function loadSafetyForm($formid, $values=array(), $edit=false, $actions=array()) {
		//Cgn::loadLibrary('Form::lib_cgn_form');
		//$a = Nofw_Associate::getAssociate();
		//$a->load('cpemp/lib/lib_cgn_form.php');
		$file = 'metroform/form.php';
		$container = Metrodi_Container::getContainer();
		$container->tryFileLoading($file);
		$f = new Metroform_Form($formid);

		$f->width="auto";
		$f->action = m_appurl('emp/safety/saveIncident');
		$f->label = 'Enter a new safety record';
		$f->setShowTitle(false);
		$f->setShowCancel(false);

		if (count($actions)) {
			$r1 = new Metroform_Form_ElementRadio('safety_type', 'What type of training was completed?');
			$r1->size = 1;
			foreach ($actions as $_k => $_v) {
				$r1->addChoice($_v, $_k);
			}
			$r1->required = true;
			$f->appendElement($r1);
		}

		$i1 = new Metroform_Form_ElementInput('safety_date','When was training complete?');
		$i1->size=10;
		$i1->attrs['data-provide'] = 'datepicker';
		$f->appendElement($i1, @$values['safety_date']? $values['safety_date']: date('m/d/Y'));

		$f->appendElement(new Metroform_Form_ElementHidden('empid'), $values['empid']);

		$f->showSubmit = FALSE;
		return $f;
	}
}
