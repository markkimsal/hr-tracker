<script type="text/Javascript">
dojo.require("dojox.data.QueryReadStore");
dojo.require("dijit.dijit"); 
dojo.require("dijit.form.FilteringSelect");
dojo.require("dojo.parser")
		function init() {
dojo.parser.parse(dojo.body());

			var testClass = dojo.getObject("dijit.form.FilteringSelect");
//			store = new dojo.data.ItemFileReadStore({url: "/amcane/emp.main.query?xhr=true"});
qstore = new dojox.data.QueryReadStore({url: "<?php echo m_appurl('emp/main/query',array('xhr'=>true));?>"});

			combo = new testClass({
				name:"emp_id",
				title: "firstname",
				autoComplete:false,
				clearOnClose:true,
				urlPreventCache:true,
				store: qstore,
				searchAttr:"displayname"
			}, dojo.byId("progCombo"));
			}
dojo.addOnLoad(init);
</script>

<style type="text/css">
.box02_tab_on {
border-bottom: 2px solid #3C3; background-color:#7D8FA4;
}
.box02_tab {
border-bottom: 2px solid #000; background-color:#7D8FA4;
}
</style>
<?php
$response->searchCrit['page'] = $response->searchPages['first_page'];
$searchUrl = m_appurl('emp/main/view');
?>

<div class="box03">
	<div class="box03_content">
	<form method="GET" action="<?=$searchUrl;?>"   class="tundra">
		View Employee
		 <input id="progCombo" name="srch"> <input type="submit" name="sbmt-btn" value="Go"/>
		
	</div>
	</form>
</div>
<br/>


<div class="action-wrapper">
Actions<hr style="margin:0;width:44em;"/>

<a href="<?php echo m_appurl('emp/main/create');?>" class="btn btn-primary" id="link-add-emp"><i class="icon-user"></i>Add Employee</a>
<a href="<?php echo m_appurl('emp/report/vacation');?>" class="btn btn-primary" id="report-vac-link" >Vacation Report</a>
</div>



<div class="box02">
<div class="box02_top">Employees</div>
	<div class="box02_content">

		<div style="margin-top:-1em;margin-bottom:0;text-align:right;width:100%;">
<?php
$response->searchCrit['page'] = $response->searchPages['first_page'];
$firstUrl = m_appurl('emp',$response->searchCrit);

$response->searchCrit['page'] = $response->searchPages['prev_page'];
$prevUrl = m_appurl('emp',$response->searchCrit);

$response->searchCrit['page'] = $response->searchPages['next_page'];
$nextUrl = m_appurl('emp',$response->searchCrit);

$response->searchCrit['page'] = $response->searchPages['last_page'];
$lastUrl = m_appurl('emp',$response->searchCrit);

unset($response->searchCrit['page']);
$selectUrl = m_appurl('emp',$response->searchCrit);
?>

<!--
<ul class="pagination">
	<li><a href="<?=$firstUrl;?>">&lt;&lt;&nbsp;Start</a><a href="<?=$prevUrl;?>">Prev</a></li>
<?php for($xp=0; $xp < $response->searchPages['last_page']+1; $xp++):

	if ($xp == $response->searchPages['current_page']) {
		$pageClass = 'active"';
		$pageSpan = '<span class="sr-only">(current)</span>';
	} else {
		$selected = '';
		$pageSpan = '';
	}
?>
	<li class="<?=$pageClass;?>"><a href="#"><?= ($xp+1)?><?= $pageSpan;?></a></li>
<?php endfor; ?>

	<li><a href="<?=$nextUrl;?>">Fwd</a><a href="<?=$lastUrl;?>">End&nbsp;&gt;&gt;</a>
</ul>
-->
		</div>

		<table border="0" width="100%" cellspacing="0" cellpadding="0" data-view-link="5" class="table table-striped table-hover">
			<thead>
			<tr>
				<th>Name</th>
				<th>Date</th>
				<th>Points</th>
				<th>V. Hr</th>
				<th>Status</th>
				<th>View</th>
				<th>Edit</th>
			</tr>
			</thead>
<?php
	$countTickets = count($response->newTickets);
	$tdClass = '';
	$_ctr = 0;
	foreach($response->newTickets as $ticketObj) {
		if ($countTickets == ++$_ctr) { $tdClass = ' class="last"'; }
			echo "<tr>\n";
//			echo "<td $tdClass>".$ticketObj->title."</td>";
//			echo "<td $tdClass>".$ticketObj->title."</td>";
		echo "<td $tdClass>
			
			<a href=\"".m_appurl('emp/main/view', array('emp_id'=>$ticketObj->employee_id))."\">".
			Emp_Main::formatName($ticketObj->firstname, $ticketObj->lastname)."</a></td>";
			//echo "<td $tdClass>".Emp_Main::formatDate($ticketObj->hire_date)."</td>";
			echo "<td $tdClass>".Emp_Main::formatDate($ticketObj->hire_date)."</td>";
			echo "<td $tdClass>".$ticketObj->points."</td>";
			echo "<td $tdClass>".$ticketObj->vac_hr."</td>";
//			echo "<td $tdClass>".Emp_Main::formatTime($ticketObj->created_on)."</td>";
//			echo "<td $tdClass style=\"line-height:1em;\"><font size=\"+1\" style=\"color:#".$response->types[$ticketObj->csrv_ticket_type_id]->hex_color.";\">&bull;</font>".$response->types[$ticketObj->csrv_ticket_type_id]->abbrv."</td>";

			//hire status (emp_status)
			echo "<td $tdClass>";
			echo $ticketObj->get('emp_status');
			echo "</td>";

			//originator
			/*
			echo "<td $tdClass>";
			echo $ticketObj->username.'&nbsp;';
			if($ticketObj->is_locked) { echo '<img height="16" src="'; t_url();echo 'media/lock_icon.png"/>'; }
			echo "</td>";
			*/

			echo "<td $tdClass><a href=\"".m_appurl('emp/main/view', array('emp_id'=>$ticketObj->employee_id))."\">View</a></td>";
			echo "<td $tdClass><a href=\"".m_appurl('emp/main/edit', array('id'=>$ticketObj->employee_id))."\">Edit</a></td>";
			echo "</tr>\n";
	}
?>
		</table>
	</div>
</div>
<br/>

