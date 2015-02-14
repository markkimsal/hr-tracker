<style type="text/css">
.box02_content table tr td {
border-bottom:1px solid black;
}
.box02_content table tr td.last {
border-bottom:none;
}

.box02_tab_on {
border-bottom: 2px solid #3C3; background-color:#7D8FA4;
}
.box02_tab {
border-bottom: 2px solid #000; background-color:#7D8FA4;
}
</style>
<?php
$t['searchCrit']['page'] = $t['searchPages']['first_page'];
$searchUrl = cgn_appurl('cportal','account','',$t['searchCrit']);
?>

<div class="box03">
	<div class="box03_content">
	<form method="POST" action="<?=$searchUrl;?>">
		Account Search
		<input type="text" value="<?=htmlentities($t['searchCrit']['terms']);?>" name="srch" size="30"/> <input type="submit" name="sbmt-btn" value="Go"/>
	</div>
	</form>
</div>
<br/>

<div class="box02">
<div class="box02_top">Accounts</div>
	<div class="box02_content">

		<div style="margin-top:-1em;margin-bottom:1em;text-align:right;width:100%;">
<?php
$t['searchCrit']['page'] = $t['searchPages']['first_page'];
$firstUrl = cgn_appurl('cportal','ticket','',$t['searchCrit']);

$t['searchCrit']['page'] = $t['searchPages']['prev_page'];
$prevUrl = cgn_appurl('cportal','ticket','',$t['searchCrit']);

$t['searchCrit']['page'] = $t['searchPages']['next_page'];
$nextUrl = cgn_appurl('cportal','ticket','',$t['searchCrit']);

$t['searchCrit']['page'] = $t['searchPages']['last_page'];
$lastUrl = cgn_appurl('cportal','ticket','',$t['searchCrit']);

unset($t['searchCrit']['page']);
$selectUrl = cgn_appurl('cportal','ticket','',$t['searchCrit']);
?>
			<a href="<?=$firstUrl;?>">&lt;&lt;Start</a> &nbsp; <a href="<?=$prevUrl;?>">Prev</a>  &nbsp; &nbsp; 

<!--
			page 1, 2, 3 ... 10
-->
		<form style="display:inline;" method="GET" action="<?= $selectUrl;?>">
				<select name="page" style="font-size:85%;border-width:1px;" onchange="this.form.submit();">
<?php
for($xp=0; $xp < $t['searchPages']['last_page']+1; $xp++) {
	if ($xp == $t['searchPages']['current_page']) {
		$selected = 'SELECTED="SELECTED"';
	} else {
		$selected = '';
	}
?>
	<option value="<?=$xp;?>" <?=$selected;?>><?=$xp+1;?></option>
<?php
}
?>
				</select>
				of <?=$t['searchPages']['last_page']+1;?> pages
			</form>

			&nbsp; &nbsp; <a href="<?=$nextUrl;?>">Fwd</a> &nbsp; <a href="<?=$lastUrl;?>">End&gt;&gt;</a>

<br/>
<!--
		<div style="margin-right:3em;">
		</div>
		-->
		</div>

		<table border="0" width="100%" cellspacing="0" cellpadding="0">
			<tr>
				<th>No.</th>
				<th>Name</th>
				<th>Date</th>
				<th>Time</th>
				<th>Type</th>
				<th>Status</th>
				<th>View</th>
				<th>Edit</th>
			</tr>
<?php
	$countTickets = count($t['newTickets']);
	$tdClass = '';
	$_ctr = 0;
	foreach($t['newTickets'] as $ticketObj) {
		if ($countTickets == ++$_ctr) { $tdClass = ' class="last"'; }
			echo "<tr>\n";
			echo "<td $tdClass>".$ticketObj->user_account_id."</td>";
//			echo "<td $tdClass>".$ticketObj->title."</td>";
//			echo "<td $tdClass>".$ticketObj->title."</td>";
			echo "<td $tdClass>".Cgn_Service_Cportal_Acct::formatName($ticketObj->firstname, $ticketObj->lastname)."</td>";
			echo "<td $tdClass>".Cgn_Service_Cportal_Acct::formatDate($ticketObj->created_on)."</td>";
			echo "<td $tdClass>".Cgn_Service_Cportal_Acct::formatTime($ticketObj->created_on)."</td>";
			echo "<td $tdClass style=\"line-height:1em;\"><font size=\"+1\" style=\"color:#".$t['types'][$ticketObj->csrv_ticket_type_id]->hex_color.";\">&bull;</font>".$t['types'][$ticketObj->csrv_ticket_type_id]->abbrv."</td>";

			//originator
			echo "<td $tdClass>";
//			echo  $ticketObj->contact_email.'&nbsp;';
			echo "</td>";

			//originator
			/*
			echo "<td $tdClass>";
			echo $ticketObj->username.'&nbsp;';
			if($ticketObj->is_locked) { echo '<img height="16" src="'; cgn_templateurl();echo 'media/lock_icon.png"/>'; }
			echo "</td>";
			*/

			echo "<td $tdClass><a href=\"".cgn_appurl('cportal','acct','view', array('id'=>$ticketObj->csrv_ticket_id))."\">View</a></td>";
			echo "<td $tdClass><a href=\"".cgn_appurl('cportal','acct','edit', array('id'=>$ticketObj->csrv_ticket_id))."\">Edit</a></td>";
			echo "</tr>\n";
	}
?>
		</table>
	</div>
</div>
<br/>

