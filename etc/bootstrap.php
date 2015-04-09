<?php
$env = '';
if (array_key_exists('APP_ENV', $_SERVER)) {
	$env = $_SERVER['APP_ENV'];
}
if ($env == '') {
	$env = getenv('APP_ENV');
}
if ($env == '') {
	$env = 'local';
}
_set('env', $env);

_iCanHandle('analyze',   'metrofw/analyzer.php');
_iCanHandle('analyze',   'metrofw/router.php', 3);
_iCanHandle('resources', 'metrofw/output.php');
_iCanHandle('output',    'metrofw/output.php');
//will be removed if output.php doesn't think we need HTML output
_iCanHandle('output',    'metrofw/template.php', 3);
//_iCanHandle('template.main',    'template/rain.php::templateMain', 3);
//_iCanHandle('template.main',    'template/rain.php::template', 3);

_iCanHandle('exception', 'metrofw/exdump.php::onException');
_iCanHandle('hangup',    'metrofw/output.php');

_didef('request',        'metrofw/request.php');
_didef('response',       'metrofw/response.php');
_didef('router',         'metrofw/router.php');
_didef('foobar',         (object)array());
_didef('form',           'metroform/form.php');
_didef('Cpemp_Att_Ticket',           'emp/attendancemodel.php');

_didef('ticket_model',   'workflow/ticketmodel.php');
_didef('employee_model', 'emp/employee.php');
_didef('attendance_model', 'emp/attendancemodel.php');
_didef('wpi_model', 'emp/wpimodel.php');
_didef('safety_model', 'emp/safetymodel.php');


// signals and slots
_iCanHandle('workflow.ticket.save.after', 'workflow/luceneindexer.php::index');
_iCanHandle('csrv_ticket_closed_approv',  'workflow/finalize.php::approveTicket');
_iCanHandle('csrv_ticket_closed_rej',     'workflow/finalize.php::rejectTicket');
//_iCanHandle('workflow.ticket.save.after', 'workflow/esindexer.php::index');
//_iCanHandle('workflow.ticket.save.after', 'workflow/solrindexer.php::index');

_didef('loggerService',  (object)array());

//metrou
_iCanHandle('authenticate', 'metrou/authenticator.php');
_iCanHandle('authorize',    'metrou/authorizer.php::requireLogin');

//events
_iCanHandle('access.denied',        'metrou/login.php::accessDenied');
_iCanHandle('authenticate.success', 'metrou/login.php::authSuccess');
_iCanHandle('authenticate.failure', 'metrou/login.php::authFailure');

//things
_didef('user',           'metrou/user.php');
_didef('session',        'metrou/sessiondb.php');
//end metrou

//metrodb
_didef('dataitem', 'metrodb/dataitem.php');
include_once('etc/dsn.'.$env.'.php');
if (isset($dsnList)) {
	foreach ($dsnList as $name => $dsn) {
		_set($name, $dsn);
	}
}
//end metrodb

_set('template_basedir', 'templates/');
_set('template_baseuri', 'templates/');
_set('template_name',    'adminlte');
_set('site.title',       'HR Tracker');
_set('site.name',        'HR Tracker');

_set('route_rules',  array() );

_set('route_rules',
	array_merge(array('/:appName'=>array( 'modName'=>'main', 'actName'=>'main' )),
	_get('route_rules')));

_set('route_rules',
	array_merge(array('/:appName/:modName'=>array( 'actName'=>'main' )),
	_get('route_rules')));

_set('route_rules',
	array_merge(array('/:appName/:modName/:actName'=>array(  )),
	_get('route_rules')));

_set('route_rules',
	array_merge(array('/:appName/:modName/:actName/:arg'=>array(  )),
	_get('route_rules')));

