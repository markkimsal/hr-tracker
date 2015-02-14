<?php

define('ZF_LIB_PATH', 'src/search/lib');

associate_loadFile('search/lib/lib_cgn_search_index.php');
associate_loadFile('search/lib/Zend/Search/Lucene.php');
associate_loadFile('search/lib/Zend/Search/Lucene/Analysis/Analyzer.php');
associate_loadFile('search/lib/Zend/Search/Lucene/Analysis/Analyzer/Common.php');
associate_loadFile('search/lib/Zend/Search/Lucene/Analysis/Analyzer/Common/TextNum.php');
associate_loadFile('search/lib/Zend/Search/Lucene/Exception.php');
require_once('src/search/lib/Zend/Search/Lucene/Exception.php');


class Cportal_Searchlistener {

	/**
	 * Save ticket to lucene
	 */
	public function handle($event, $args) {
		if (!$event->source->useSearch) {
			return TRUE;
		}

		$this->indexInSearch($event->source);
		return TRUE;
	}

	/**
	 *
	 */
	function indexInSearch($item, $indexName = 'tickets') {
		static $cxx;

		$cxx++;
		require_once('src/search/lib/lib_cgn_search_index.php');
		$index = new Cgn_Search_Index($indexName);
		if ($index->isClosed) {
			return FALSE;
		}
		//find and delete old database_id and table_name from index
		$this->foobarOldDoc($index, $item->tableName, $item->dataItem->getPrimaryKey());

		$index->createDoc();
		$index->currentDoc->addField(Zend_Search_Lucene_Field::Keyword('database_id', $item->dataItem->getPrimaryKey())); 
		$index->currentDoc->addField(Zend_Search_Lucene_Field::Keyword('table_name', $item->tableName)); 

		if (is_callable(array($item, '_collectSearchFields')) ) {
			$fields = $item->_collectSearchFields();
		} else {
			$fields = $this->_collectSearchFields($item);
		}

		foreach ($fields as $_key => $_struct) {
			if ($_struct['type'] == 'keyword') 
				$index->currentDoc->addField(Zend_Search_Lucene_Field::Keyword($_key, $_struct['value'])); 

			if ($_struct['type'] == 'text') 
				$index->currentDoc->addField(Zend_Search_Lucene_Field::Text($_key, $_struct['value'])); 

			if ($_struct['type'] == 'unstored') 
				$index->currentDoc->addField(Zend_Search_Lucene_Field::Unstored($_key, $_struct['value'])); 

			if ($_struct['type'] == '') 
				$index->currentDoc->addField(Zend_Search_Lucene_Field::Unstored($_key, $_struct['value'])); 
		}
		$index->saveDoc();
	}

	public function _collectSearchFields($item) {
		$vals = $item->dataItem->valuesAsArray();
		$fields = array();
		foreach ($vals as $k =>$v) {
			//exclude the pkey
			if ($k == $item->dataItem->_pkey) {
				continue;
			}
			//store title, name, display_name, or link_text as separate fields
			if ($k == 'title' ||
				$k == 'name' ||
				$k == 'display_name' ||
				$k == 'link_text' ) {
					$fields[$k] = array('type'=>'unstored', 'value'=>$v);
			} else {
				if (isset($fields['_search_data'])) {
					$fields['_search_data']['value'] .= ' '.$v;
				} else {
					$fields['_search_data'] = array('type'=>'unstored', 'value'=>$v);
				}
			}
		}
		return $fields;
	}

	/**
	 * load the old record and delete it
	 */
	function foobarOldDoc(&$index, $tableName, $pkey) {
		$query = new Zend_Search_Lucene_Search_Query_MultiTerm();
		$dbTerm  = new Zend_Search_Lucene_Index_Term($pkey, 'database_id');
		$tblTerm = new Zend_Search_Lucene_Index_Term($tableName, 'table_name');

	    $query->addTerm($dbTerm, TRUE);
	    $query->addTerm($tblTerm, TRUE);

		$hits = $index->find($query);
		foreach ($hits as $h) {
			$index->currentIndex->delete($h->id);
			$index->currentIndex->commit();
			$index->currentIndex->optimize();
		}
	}
}
