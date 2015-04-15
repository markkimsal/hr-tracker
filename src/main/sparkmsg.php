<?php

class Main_Sparkmsg {

	public function template($request, $response, $section) {
		$html = '';

		foreach ((array)$response->sparkmsg as $_sctMsg) {
			if (!is_array($_sctMsg)) {
				$_msg = $_sctMsg;
				$className = 'success';
				$color     = 'green';
				$icon      = 'ok';
			} else {
				$_msg      = $_sctMsg['msg'];
				$type      = $_sctMsg['type'];
				$className = $this->getClassName($type);
				$color     = $this->getColor($type);
				$icon      = $this->getIcon($type);
			}
$html .= <<<EOD
		<div class="alert alert-block alert-$className" role="alert">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			<i class="icon-$icon $color"></i>
			$_msg
		</div>
EOD;
		}
		echo $html;
	}

	public function getClassName($type) {
		switch($type) {
			case 'error':
				return 'danger';
			case 'warn':
				return 'warning';
			case 'info':
				return 'info';
			default:
				return 'success';
		}
	}

	public function getIcon($type) {
		switch($type) {
			case 'error':
			case 'warn':
				return 'warning-sign';
			case 'info':
				return 'ok';
			default:
				return '';
		}
	}

	public function getColor($type) {
		switch($type) {
			case 'error':
				return 'red';
			case 'warn':
				return 'yellow';
			case 'info':
				return 'blue';
			default:
				return 'green';
		}
	}

}
