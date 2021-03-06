<?php


class Metroform_Form {

	var $name         = '';
	var $elements     = array();
	var $hidden       = array();
	var $label        = '';
	var $action;
	var $method;
	var $enctype;
	var $layout       = NULL;      //layout object to render the form
	var $width        = '450px';
	var $style        = array();
	var $showSubmit   = TRUE;
	var $labelSubmit  = 'Save';
	var $showCancel   = TRUE;
	var $labelCancel  = 'Cancel';
	var $actionCancel = 'javascript:history.go(-1);';
	var $showLabel    = TRUE;



	var $formHeader = '';
	var $formFooter = '';

	function Metroform_Form($name = 'metroform', $action='', $method='POST', $enctype='') {
		$this->name = $name;
		$this->action = $action;
		$this->method = $method;
		$this->enctype = $enctype;
	}

	function appendElement($e,$value='') {
		if ($value !== '') {
			$e->setValue($value);
//			$e->value = $value;
		}
		if ($e->type == 'hidden') {
			$this->hidden[] = $e;
		} else {
			$this->elements[] = $e;
		}
	}

	/**
	 * Combine this element one the same row as the previous one
	 */
	public function stackElement($e, $value='') {
		if ($value !== '') {
			$e->setValue($value);
		}
		if ($e->type == 'hidden') {
			$elemList = $this->hidden;
		} else {
			$elemList = $this->elements;
		}

		$top = count($elemList);
		$last = $this->elements[$top-1];
		if (strtolower(get_class($last)) == 'form_element_bag') {
			$last->stackElement($e);
			$elemList[$top-1] = $last;
		} else {
			$bag = new Metroform_Form_Element_Bag();
			$bag->stackElement($last);
			$bag->stackElement($e);
			$elemList[$top-1] = $bag;
		}

		if ($e->type == 'hidden') {
			$this->hidden = $elemList;
		} else {
			$this->elements = $elemList;
		}
	}


	function toHtml($layout=NULL) {
/*
		if ($layout !== NULL) {
			return $layout->renderForm($this);
		}
*/
		if ($this->layout !== NULL) {
			return $this->layout->renderForm($this);
		}
		$layout = new Metroform_Form_Layout();
		return $layout->renderForm($this);
	}

	function setShowSubmit($show=TRUE,$labelSubmit='Save') {
		$this->showSubmit = $show;
		$this->labelSubmit = $labelSubmit;
	}

	function setShowCancel($show=TRUE,$labelCancel='Cancel',$actionCancel='javascript:history.go(-1);') {
		$this->showCancel = $show;
		$this->labelCancel = $labelCancel;
		$this->actionCancel = $actionCancel;
	}

	function setShowLabel($showLabel=true) {
		$this->showLabel = $showLabel;
	}

	function setShowTitle($showTitle=true) {
		$this->setShowLabel($showTitle);
	}

	/**
	 * Check that each required field is filled in
	 *
	 * @return Bool True if all required inputs have values, false otherwise
	 */
	function validate($values) {
		$validated = TRUE;
		foreach ($this->elements as $_k => $_v) {
			if ($_v->required) {
				if ( (!isset($values[$_v->name])) || $values[$_v->name] == '' ) {
					$validated = false;
					$this->validationErrors[$_v->name][] = 601;
				} else {
					if (!$_v->validate($values[$_v->name])) {
						$validated = false;
						$this->validationErrors[$_v->name][] = 601;
					}
				}
			}
		}
		return $validated;
	}
}


class Metroform_FormAdmin extends Metroform_Form {

	/**
	 * Use Fancy layout
	 */
	function toHtml($layout=NULL) {
		if ($layout !== NULL) {
			return $layout->renderForm($this);
		}
		if ($this->layout !== NULL) {
			return $this->layout->renderForm($this);
		}
		$layout = new Metroform_Form_LayoutFancy();
		return $layout->renderForm($this);
	}
}

class Metroform_FormAdminDelete extends Metroform_Form {

	/**
	 * Use Fancy Delete layout
	 */
	function toHtml($layout=NULL) {
		if ($layout !== NULL) {
			return $layout->renderForm($this);
		}
		if ($this->layout !== NULL) {
			return $this->layout->renderForm($this);
		}
		$layout = new Metroform_Form_LayoutFancyDelete();
		return $layout->renderForm($this);
	}
}


class Metroform_Form_Element {
	var $type;
	var $name;
	var $id;
	var $label;
	var $value;
	var $size;
	var $jsOnChange = '';
	var $attrs      = array();
	var $required   = false;

	function Metroform_Form_Element($name, $label=-1, $size=30) {
		$this->name = $name;
		$this->label = $label;
		if ($this->label == -1) {
			$this->label = ucfirst($this->name);
		}
		$this->size = $size;
	}

	/**
	 * Set the value for this element
	 */
	function setValue($v) {
		$this->value = $v;
	}

	public function toHtml() {
		if ($this->size) {
			$size = 'size="'.$this->size.'"';
		} else {
			$size = '';
		}
		$extra = '';
		foreach ($this->attrs as $_k => $_v) {
			$extra .= ' '.$_k.'="'.$_v.'"';
		}
		return '<input type="'.$this->type.'" name="'.$this->name.'" id="'.$this->name.'" '.$size.' value="'.htmlentities($this->value,ENT_QUOTES).'" '.$extra.'/>';
	}

	/**
	 * Add custom javascript for the onchange event.
	 */
	public function setJsOnChange($js) {
		$this->jsOnChange = $js;
	}

	/**
	 * Get custom javascript for the onchange event.
	 */
	public function getJsOnChange() {
		return $this->jsOnChange;
	}

	/**
	 * Return true if this element is required and the value is not empty.
	 */
	public function validate($value) {
		if ($this->required) {
			if ( empty($value) ) {
				return false;
			}
		}
		return true;
	}
}


class Metroform_Form_Element_Bag extends Metroform_Form_Element {
	public $elemList = array();
	public $type     = 'aggregate';

	function Metroform_Form_Element_Bag() {
	}

	/**
	 * Use the first element's label, name and size as this element's label, name and size
	 */
	public function stackElement($el) {
		if (!count($this->elemList)) {
			$this->label = $el->label;
			$this->name = $el->name;
			$this->size = $el->size;
		}

		$this->elemList[] = $el;
	}

	/**
	 * Return one html string representing both inputs
	 */
	public function toHtml() {
		$html = '';
		foreach ($this->elemList as $_el) {
			$html .= $_el->toHtml();
		}
		return $html;
	}
}

class Metroform_Form_ElementLabel extends Metroform_Form_Element {
	var $type = 'label';

	function Metroform_Form_ElementLabel($name, $label=-1,  $value= '') {
			$this->name = $name;
			$this->value = $value;
			$this->label = $label;
	}

	function toHtml() {
		return '<span name="'.$this->name.'" id="'.$this->name.'">'.htmlentities($this->value,ENT_QUOTES).'</span>';
	}
}

class Metroform_Form_ElementContentLine extends Metroform_Form_Element {
	var $type = 'contentLine';

	function Metroform_Form_ElementContentLine($value= '') {
			$this->value = $value;
	}

	function toHtml() {
		return $this->value;
	}
}

class Metroform_Form_ElementHidden extends Metroform_Form_Element {
	var $type = 'hidden';
}


class Metroform_Form_ElementInput extends Metroform_Form_Element {
	var $type = 'text';
}

class Metroform_Form_ElementFile extends Metroform_Form_Element {
	var $type = 'file';
}

class Metroform_Form_ElementText extends Metroform_Form_Element {
	var $type = 'textarea';
	var $rows;
	var $cols;

	function Metroform_Form_ElementText($name, $label=-1,$rows=15,$cols=65) {
		$this->name = $name;
		$this->label = $label;
		if ($this->label == -1) {
			$this->label = ucfirst($this->name);
		}
		$this->rows = $rows;
		$this->cols = $cols;
	}


	public function toHtml() {

		$html  = '';
		$html .= '<textarea class="form-input" name="'.$this->name.'" id="'.$this->name.'" rows="'.$this->rows.'" cols="'.$this->cols.'" >'.htmlentities($this->value,ENT_QUOTES).'</textarea>'."\n";
		return $html;
	}
}


class Metroform_Form_ElementPassword extends Metroform_Form_Element {
	var $type = 'password';
}


class Metroform_Form_ElementRadio extends Metroform_Form_Element {
	var $type = 'radio';
	var $choices = array();

	function addChoice($c, $v='', $selected=0) {
		$top = count($this->choices);
		$this->choices[$top]['title'] = $c;
		$this->choices[$top]['selected'] = $selected;
		$this->choices[$top]['value'] = $v;
		return count($this->choices)-1;
	}

	/**
	 * Sets the selected choices index
	 */
	function setValue($v) {
		foreach ($this->choices as $idx=>$c) {
			if ($c['value'] === $v) {
				$this->choices[$idx]['selected'] = true;
				break;
			}
		}
	}

	function toHtml() {
		$html = '';
		foreach ($this->choices as $cid => $c) {
			$selected = '';
			if ($c['value'] === '') {
				$value = sprintf('%02d', $cid+1);
			} else {
				$value = $c['value'];
			}
			if ($c['selected'] == 1) { $selected = ' CHECKED="CHECKED" '; }
		$html .= '<input type="radio" name="'.$this->name.'" id="'.$this->name.sprintf('%02d',$cid+1).'" value="'.$value.'"'.$selected.'/><label for="'.$this->name.sprintf('%02d',$cid+1).'">'.$c['title'].'</label><br/> ';
		}
		return $html;
	}

	public function validate($value) {
		foreach ($this->choices as $_k => $_v) {
			if ($_v['value'] == $value) {
				return true;
			}
		}
		return false;
	}
}

class Metroform_Form_ElementSelect extends Metroform_Form_Element {
	var $type = 'select';
	var $choices = array();
	var $size = 1;
	var $selectedVal = NULL;

	function Metroform_Form_ElementSelect($name,$label=-1, $size=7, $selectedVal = NULL) {
		parent::Metroform_Form_Element($name, $label, $size);
		$this->selectedVal = $selectedVal;
	}

	function addChoice($c, $v='', $selected=0) {
		$top = count($this->choices);

		if ($this->selectedVal == $v) {
			$selected = true;
		}

		$this->choices[$top]['title'] = $c;
		$this->choices[$top]['selected'] = $selected;
		$this->choices[$top]['value'] = $v;

		return count($this->choices)-1;
	}

	/**
	 * Sets the selected choices index
	 */
	function setValue($v) {
		foreach ($this->choices as $idx=>$c) {
			if ($c['value'] === $v) {
				$this->choices[$idx]['selected'] = true;
				break;
			}
		}
	}

	function toHtml() {
		$onchange = '';
		if ($this->jsOnChange !== '') {
			$onchange = ' onchange="'.$this->jsOnChange.'" ';
		}
		$html = '<select name="'.$this->name.'" id="'.$this->name.'" size="'.$this->size.'" '.$onchange.'>';
		foreach ($this->choices as $cid => $c) {
			$selected = '';
			if ($c['selected'] == 1) { $selected = ' SELECTED="SELECTED" '; }
			if ($c['value'] != '') { $value = ' value="'.htmlentities($c['value']).'" ';} else { $value = ''; }
		$html .= '<option id="'.$this->name.sprintf('%02d',$cid+1).'" '.$value.$selected.'>'.$c['title'].'</option> '."\n";
		}
		return $html."</select>\n";
	}


	public function validate($value) {
		foreach ($this->choices as $_k => $_v) {
			if ($_v['value'] == $value) {
				return true;
			}
		}
		return false;
	}
}


class Metroform_Form_ElementCheck extends Metroform_Form_Element {
	var $type = 'check';
	var $choices = array();

	function addChoice($c,$v='',$selected=0) {
		$top = count($this->choices);
		$this->choices[$top]['title'] = $c;
		if ($v == '') {
			$this->choices[$top]['value'] = sprintf('%02d',$top+1);
		} else {
			$this->choices[$top]['value'] = $v;
		}
		$this->choices[$top]['selected'] = $selected;
		return count($this->choices)-1;
	}

	/**
	 * If only one choice, don't add the array []
	 */
	function getName() {
		if ( count($this->choices) < 2) {
			return $this->name;
		} else {
			return $this->name.'[]';
		}
	}

	/**
	 * Set an array of 'VALUES' which should be "selected".
	 */
	function setValue($x) {
		$this->values = $x;
		if(is_array($x)) {
			foreach($this->values as $k=>$v) {
			}
		}
	}

	function toHtml() {
		$html = '';
		foreach ($this->choices as $cid => $c) {
			$selected = '';
			if ($c['selected'] == 1) { $selected = ' CHECKED="CHECKED" '; }
			if(is_array($this->values) && in_array($c['value'], $this->values)) { $selected = ' CHECKED="CHECKED" '; }
		$html .= '<input type="checkbox" name="'.$this->getName().'" id="'.$this->name.sprintf('%02d',$cid+1).'" value="'.$c['value'].'"'.$selected.'/><label for="'.$this->name.sprintf('%02d',$cid+1).'">'.$c['title'].'</label><br/> ';
		}
		return $html;
	}
}


class Metroform_Form_ElementDate extends Metroform_Form_Element {
	var $type = 'date';

	function Metroform_Form_ElementDate($name,$label=-1, $size=15) {
		$this->name = $name;
		$this->label = $label;
		if ($this->label == -1) {
			$this->label = ucfirst($this->name);
		}
		$this->size = $size;
	}

	function toHtml() {
		$html = '<input name="'.$this->name.'" id="'.$this->name.'" size="'.$this->size.'" value="'.$this->value.'" />';
		return $html."&nbsp;<input class=\"popup_cal\" type=\"button\" name=\"".$this->name."_btn\" value=\"Calendar\">\n";
	}
}

class Metroform_Form_Processor {
}


class Metroform_Form_Layout {

	function renderForm($form) {
		$html = '';
		$html .= '<div class="formContainer">'."\n";
		if ($form->showLabel && $form->label != '' ) {
			$html .= '<p class="form_header">'.$form->label.'</p>';
			$html .= "\n";
		}
		if ($form->formHeader != '' ) {
			$html .= '<p class="form_header_content">'.$form->formHeader.'</p>';
			$html .= "\n";
		}

//		$attribs = array('method'=>$form->method, 'name'=>$form->name, 'id'=>$form->id);
		$action = '';
		if ($form->action) {
			$action = ' action="'.$form->action.'" ';
		}
		$html .= '<form class="data_form" method="'.$form->method.'" name="'.$form->name.'" id="'.$form->name.'"'.$action;
		if ($form->enctype) {
			$html .= ' enctype="'.$form->enctype.'"';
		}
		$html .= $this->printStyle($form);
		$html .= '>';
		$html .= "\n";
		$html .= '<table class="form_table">'."\n";
		foreach ($form->elements as $e) {
			$html .= '<tr><td class="form_cell_label" valign="top">'."\n";
			$html .= $e->label.'</td><td class="form_cell_input" valign="top">'."\n";
			if ($e->type == 'textarea') {
				$html .= '<textarea name="'.$e->name.'" id="'.$e->name.'" rows="'.$e->rows.'" cols="'.$e->cols.'" >'.htmlentities($e->value,ENT_QUOTES).'</textarea>'."\n";
			} else if ($e->type == 'radio') {
				$html .= '<div class="checkbox">'.$e->toHtml().'</div>'."\n";
			} else if ($e->type != '') {
				$html .= $e->toHtml();
			} else {
				$html .= '<input type="'.$e->type.'" name="'.$e->name.'" id="'.$e->name.'" value="'.htmlentities($e->value,ENT_QUOTES).'" size="'.$e->size.'"/>'."\n";
			}
			$html .= '</td></tr>'."\n";
		}
		if ($form->formFooter != '') {
			$html .= '<tr><td class="form_footer_row" colspan="2">'."\n";
				$html .= '<P>'.$form->formFooter.'</P>'."\n";
			$html .= '</td></tr>'."\n";
		}
		$trailingHtml = '';
		if (count($form->hidden)) {
			foreach ($form->hidden as $e) {
				$trailingHtml .= '<input type="hidden" name="'.$e->name.'" id="'.$e->name.'"';
				$trailingHtml .= ' value="'.htmlentities($e->value,ENT_QUOTES).'"/>'."\n";
			}
		}

		if ($form->showSubmit || $form->showCancel) {
			$trailingHtml .= '<div class="form-button-container">'."\n";
			if ($form->showSubmit == TRUE) {
				$trailingHtml .= '<input type="submit" class="containerButtonSubmit" name="'.$form->name.'_submit" value="'.$form->labelSubmit.'"/>'."\n";
				$trailingHtml .= "\n";
			}
			if ($form->showCancel == TRUE) {
				$trailingHtml .= '<input type="button" class="containerButtonCancel" name="'
					// SCOTTCHANGE
					// .$form->name.'_cancel" onclick="javascript:history.go(-1);" value="'.$form->labelCancel.'"/>';
					.$form->name.'_cancel" onclick="'.$form->actionCancel.'" value="'.$form->labelCancel.'"/>';
				$trailingHtml .= "\n";
			}
			$trailingHtml .= '</div>'."\n";
		}
		if ($trailingHtml !== '') {
			$html .= '<tr><td class="form_last_row" colspan="2">'."\n";
			$html .= $trailingHtml."\n";
			$html .= '</td></tr>'."\n";
		}

		$html .= '</table>'."\n";
		$html .= '</form>'."\n";
		$html .= '</div>'."\n";

		return $html;
	}

	function printStyle($form) {
		if ( count ($form->style) < 1) { return ''; }
		$html  = '';
		$html .= ' style="';
		foreach ($form->style as $k=>$v) {
			$html .= "$k:$v;";
		}
		return $html.'" ';
	}
}


class Metroform_Form_LayoutFancy extends Metroform_Form_Layout {


	function renderForm($form) {
		$html = '<div style="padding:1px;background-color:#FFF;border:1px solid silver;width:'.$form->width.';">';
		$html .= '<div class="form" style="padding:5px;background-color:#EEE;">';
		if ($form->showLabel && $form->label != '' ) {
			$html .= '<h3 style="padding:0px 0px 13pt;">'.$form->label.'</h3>';
			$html .= "\n";
		}
		if ($form->formHeader != '' ) {
			$html .= '<P style="padding:0px 0px 3pt; text-align:justify;">'.$form->formHeader.'</P>';
			$html .= "\n";
		}
//		$attribs = array('method'=>$form->method, 'name'=>$form->name, 'id'=>$form->id);
		$action = '';
		if ($form->action) {
			$action = ' action="'.$form->action.'" ';
		}
		$html .= '<form class="data_form" method="'.$form->method.'" name="'.$form->name.'" id="'.$form->name.'"'.$action;
		if ($form->enctype) {
			$html .= ' enctype="'.$form->enctype.'"';
		}
		$html .= '>';
		$html .= "\n";
		$html .= '<table border="0" cellspacing="3" cellpadding="3">';
		foreach ($form->elements as $e) {
			$html .= '<tr><td valign="top" align="right" nowrap>';
			$html .= $e->label.'</td><td valign="top">';
			if ($e->type == 'textarea') {
				$html .= '<textarea class="forminput" name="'.$e->name.'" id="'.$e->name.'" rows="'.$e->rows.'" cols="'.$e->cols.'" >'.htmlentities($e->value,ENT_QUOTES).'</textarea>';
			} else if ($e->type == 'contentLine') {
				$html .= "<span style=\"text-align: justify;\">";
				$html .= $e->toHtml();
				$html .= "</span>";
			} else if ($e->type != '') {
				$html .= $e->toHtml();
			} else {
				$html .= '<input class="forminput" type="'.$e->type.'" name="'.$e->name.'" id="'.$e->name.'" value="'.htmlentities($e->value,ENT_QUOTES).'" size="'.$e->size.'"/>';
			}
			$html .= '</td></tr>';
		}
		$html .= '</table><br />';
		if ($form->formFooter != '' ) {
			$html .= '<P style="padding:0px 0px 3pt;text-align:justify;">'.$form->formFooter.'</P>';
			$html .= "\n";
		}

		if ($form->showSubmit || $form->showCancel) {
			$html .= '<div class="form-button-container">';
			$html .= "\n";
			if ($form->showSubmit == TRUE) {
				$html .= '<input class="form-button form-submit" type="submit" name="'.$form->name.'_submit" value="'.$form->labelSubmit.'"/>';
				$html .= '&nbsp;&nbsp;';
			}
			if ($form->showCancel == TRUE) {
				$html .= '<input class="form-button form-cancel" type="button" name="'.$form->name.'_cancel" onclick="javascript:history.go(-1);" value="'.$form->labelCancel.'"/>';
				$html .= "\n";
			}
			$html .= '</div>';
			$html .= "\n";
		}

		foreach ($form->hidden as $e) {
			$html .= '<input type="hidden" name="'.$e->name.'" id="'.$e->name.'" value="'.htmlentities($e->value,ENT_QUOTES).'"/>';
		}

		$html .= '</form>';
		$html .= '</div>';
		$html .= '</div>';
		$html .= "\n";

		return $html;
	}
}

/**
 * Layout a form with data definition lists
 */
class Metroform_Form_Layout_Dl extends Metroform_Form_Layout {


	function renderForm($form) {

		$html = '<div class="form-wrapper '.$form->name.'" style="width:'.$form->width.';">'."\n";

		if ($form->showLabel && $form->label != '' ) {
			$html .= '<span class="form-title">'.$form->label.'</span>';
			$html .= "\n";
		}

		$html .= '<div class="form-container '.$form->name.'">'."\n";
		if ($form->formHeader != '' ) {
			$html .= '<p class="form-header">'.$form->formHeader.'</p>';
			$html .= "\n";
		}

		$action = '';
		if ($form->action) {
			$action = ' action="'.$form->action.'" ';
		}
		$html .= '<form class="form-form" method="'.$form->method.'" name="'.$form->name.'" id="'.$form->name.'"'.$action;
		if ($form->enctype) {
			$html .= ' enctype="'.$form->enctype.'"';
		}
		$html .= ">\n";
		$html .= '<dl>';
		foreach ($form->elements as $idx => $e) {
			if ($idx == 0 ) {
				$dtcss   = 'class="first"';
			} else {
				$dtcss   = '';
			}
			if ($e->label !== '') {
				$html .= '<dt '.$dtcss.'><label for="'.$e->name.'">'.$e->label.'</label></dt>';
			}

			$html .= "\n\t<dd>";
			if ($e->type == 'contentLine') {
				$html .= "<span style=\"text-align: justify;\">";
				$html .= $e->toHtml();
				$html .= "</span>";
			} else if ($e->type != '') {
				$html .= $e->toHtml();
			} else {
				$html .= '<input class="forminput" type="'.$e->type.'" name="'.$e->name.'" id="'.$e->name.'" value="'.htmlentities($e->value,ENT_QUOTES).'" size="'.$e->size.'"/>';
			}
			$html .= "</dd>\n";
		}
		$html .= '</dl>';
		if ($form->formFooter != '' ) {
			$html .= '<P class="form-footer">'.$form->formFooter.'</P>';
			$html .= "\n";
		}

		if ($form->showSubmit || $form->showCancel) {
			$html .= '<div class="form-button-container">';
			$html .= "\n";
			if ($form->showSubmit == TRUE) {
				$html .= '<button class="form-button form-submit" type="submit" name="'.$form->name.'_submit">'.$form->labelSubmit.'</button>';
				$html .= '&nbsp;&nbsp;';
			}
			if ($form->showCancel == TRUE) {
				$html .= '<button class="form-button form-cancel" type="button" name="'.$form->name.'_cancel" onclick="javascript:history.go(-1);">'.$form->labelCancel.'</button>';
				$html .= "\n";
			}
			$html .= '</div>';
			$html .= "\n";
		}

		foreach ($form->hidden as $e) {
			$html .= '<input type="hidden" name="'.$e->name.'" id="'.$e->name.'" value="'.htmlentities($e->value,ENT_QUOTES).'"/>';
		}

		$html .= '</form>';
		$html .= '</div>';
		$html .= '</div>';
		$html .= "\n";

		return $html;
	}
}


?>
