<?php

class Workflow_Eventemitter {

	public function emit($signal, $source) {
		return Metrofw_Kernel::emit($signal, $source);
	}
}
