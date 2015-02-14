<?php

include_once(CGN_LIB_PATH.'/Zend/Search/Lucene.php');
include_once(CGN_LIB_PATH.'/Zend/Search/Lucene/Analysis/Analyzer.php');


class Cgn_Lucene_Search { 

    var $controller = true; 
    var $index = null; 
     
    function startup(&$controller) { 
    }     
 
    // Get the index object 
    function &getIndex($force=FALSE) { 

		$indexPath = BASE_DIR .'/var/search_cache/tickets';
		if (!file_exists($indexPath) || $force===TRUE) {
            $this->index = new Zend_Search_Lucene($indexPath,true); 
			$this->rebuildIndex();
		}

        if(!$this->index) { 
            $this->index = new Zend_Search_Lucene($indexPath); 
        } 
        return $this->index; 
    }

	function rebuildIndex() {
		$ticketLoader = new Cgn_DataItem('csrv_ticket');
		$tickets = $ticketLoader->find();
		$x = 0;

		foreach ($tickets as $tk) {
			$ticketFull = Cportal_Ticket::ticketFactory($tk);
			$ticketFull->loadAccount();
			$ticketFull->loadStage();
			$ticketFull->indexInSearch();
			/*
			if ($ticketFull->getTypeId() == 1) {
				$ticketFull->loadAddresses();
			}
			 */

		}
	}
     
    // Executes a query to the index and returns the results 
    function query($query) { 
         
        $index =& $this->getIndex(); 
        $results = $index->find($query); 
        return $results; 
    } 
} 

