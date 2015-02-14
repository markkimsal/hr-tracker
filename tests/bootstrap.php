<?php

#setup metrodb
include_once('local/metrodb/dataitem.php');
include_once('local/metrodb/connector.php');
include_once('local/metrodi/container.php');

$container = new Metrodi_Container('.', array('local', 'src'));
_didef('dataitem', 'Metrodb_Dataitem');

//Metrodb_Connector::setDsn('default', 'mysql://root:mysql@localhost/metrodb_test');
//Metrodb_Connector::setDsn('default', 'mysql://docker:mysql@172.17.0.3/metrodb_test');

