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
$response->searchCrit['page'] = $response->searchPages['first_page'];
$tempSearch = $response->searchCrit;
unset($tempSearch['terms']);
$searchUrl = m_appurl('cportal/ticket/main',$tempSearch);
?>

<div class="box03">
	<div class="box03_content">
	<form method="POST" action="<?=$searchUrl;?>">
		Ticket Search
		<input type="text" value="<?=htmlentities($response->searchCrit['terms']);?>" name="srch" size="30"/> <input type="submit" name="sbmt-btn" value="Go"/>
	<input type="checkbox" name="incl-old"  id="incl-old" /><label for="incl-old">Include closed tickets</label>

	</div>
	</form>
</div>
<br/>


<div class="tabbable">
											<ul class="nav nav-tabs" id="myTab">
												<li class="active">
													<a data-toggle="tab" href="#home">
														<i class="green ace-icon fa fa-home bigger-120"></i>
													All
													</a>
												</li>

												<li>
													<a data-toggle="tab" href="#messages">
														Work Performance
													</a>
												</li>

												<li>
													<a data-toggle="tab" href="#messages">
														Attendance
													</a>
												</li>


												<li>
													<a data-toggle="tab" href="#messages">
														Safety
													</a>
												</li>
											</ul>

											<div class="tab-content">
												<div id="home" class="tab-pane fade in active">


		<div style="margin-top:-1em;margin-bottom:1em;text-align:right;width:100%;">
<?php
$response->searchCrit['page'] = $response->searchPages['first_page'];
$firstUrl = m_appurl('cportal/ticket',$response->searchCrit);

$response->searchCrit['page'] = $response->searchPages['prev_page'];
$prevUrl = m_appurl('cportal/ticket',$response->searchCrit);

$response->searchCrit['page'] = $response->searchPages['next_page'];
$nextUrl = m_appurl('cportal/ticket',$response->searchCrit);

$response->searchCrit['page'] = $response->searchPages['last_page'];
$lastUrl = m_appurl('cportal/ticket',$response->searchCrit);

unset($response->searchCrit['page']);
$selectUrl = m_appurl('cportal/ticket',$response->searchCrit);
?>
			<a href="<?=$firstUrl;?>">&lt;&lt;Start</a> &nbsp; <a href="<?=$prevUrl;?>">Prev</a>  &nbsp; &nbsp; 

		<form style="display:inline;" method="GET" action="<?= $selectUrl;?>">
				<select name="page" style="font-size:85%;border-width:1px;" onchange="this.form.submit();">
<?php
for($xp=0; $xp < $response->searchPages['last_page']+1; $xp++) {
	if ($xp == $response->searchPages['current_page']) {
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
				of <?=$response->searchPages['last_page']+1;?> pages
			</form>

			&nbsp; &nbsp; <a href="<?=$nextUrl;?>">Fwd</a> &nbsp; <a href="<?=$lastUrl;?>">End&gt;&gt;</a>

		</div>



<div class="dataTables_wrapper">

<!--
<div class="row"><div class="col-sm-6"><div id="sample-table-2_length" class="dataTables_length"><label>Display <select size="1" name="sample-table-2_length" aria-controls="sample-table-2"><option value="10" selected="selected">10</option><option value="25">25</option><option value="50">50</option><option value="100">100</option></select> records</label></div></div><div class="col-sm-6"><div class="dataTables_filter" id="sample-table-2_filter"><label>Search: <input type="text" aria-controls="sample-table-2"></label></div></div></div>

-->

<table id="data-table-01" class="table table-striped table-bordered table-hover dataTable" aria-describedby="data-table-01_info">
	<thead>
		<tr role="row">
			<th class="center sorting_disabled" role="columnheader" rowspan="1" colspan="1" style="width: 53px; " aria-label="">
				<label>
					ID
					<span class="lbl"></span>
				</label>
			</th>
			<th class="sorting" role="columnheader" tabindex="0" aria-controls="data-table-01" rowspan="1" colspan="1" style="width: 150px; " aria-label="Domain: activate to sort column ascending">Date</th>
			<th class="sorting" role="columnheader" tabindex="0" aria-controls="data-table-01" rowspan="1" colspan="1" style="width: 97px; " aria-label="Price: activate to sort column ascending">Time</th>
			<th class="hidden-480 sorting" role="columnheader" tabindex="0" aria-controls="data-table-01" rowspan="1" colspan="1" style="width: 106px; " aria-label="Clicks: activate to sort column ascending">Type</th>
			<th class="sorting" role="columnheader" tabindex="0" aria-controls="data-table-01" rowspan="1" colspan="1" style="width: 163px; " aria-label="Update : activate to sort column ascending">
				<i class="bigger-110 hidden-480"></i>Status</th>
			<th class="hidden-480 sorting" role="columnheader" tabindex="0" aria-controls="data-table-01" rowspan="1" colspan="1" style="width: 142px; " aria-label="Status: activate to sort column ascending">Account</th>
			<th class="sorting_disabled" role="columnheader" rowspan="1" colspan="1" style="width: 156px; " aria-label=""></th>
		</tr>
	</thead>
	
<tbody role="alert" aria-live="polite" aria-relevant="all">

<?php
	$tdClass = '';
	$_ctr = 0;
	foreach($response->newTickets as $ticketObj) {
		if ($countTickets == ++$_ctr) { $tdClass = ' class="last"'; }
		if (!($_ctr % 2)) { $trClass = ' class="even"'; } else { $trClass = ' class="odd"';}
	 		echo '<tr '.$trClass.'>'.PHP_EOL;
			echo "<td>".$ticketObj->csrv_ticket_id."</td>";
			echo "<td>".Cportal_Main::formatDate($ticketObj->created_on)."</td>";
			echo "<td>".Cportal_Main::formatTime($ticketObj->created_on)."</td>";
			echo "<td style=\"line-height:1em;\"><font size=\"+1\" style=\"color:#".$response->types[$ticketObj->csrv_ticket_type_id]->hex_color.";\">&bull;</font>".$response->types[$ticketObj->csrv_ticket_type_id]->abbrv."</td>";
			echo '<td><span class="label label-sm label-'.$response->status[$ticketObj->csrv_ticket_status_id]->style_name.' arrowed-in">'.$response->status[$ticketObj->csrv_ticket_status_id]->display_name.'</span></td>';
			echo "<td>";

			if ($ticketObj->contact_email != '') {
				echo $ticketObj->contact_email.'&nbsp;';
			} else {
				echo $ticketObj->get('lastname').',&nbsp;'.$ticketObj->get('firstname');
			}
			/*
			echo $ticketObj->username.'&nbsp;';
			if($ticketObj->is_locked) { echo '<img height="16" src="'; cgn_templateurl();echo 'media/lock_icon.png"/>'; }
			 */
			echo "</td>";
			echo '<td $tdClass>
			<div class="visible-md visible-lg hidden-sm hidden-xs action-buttons">
				<a href="'.m_appurl('cportal/ticket/view',array('id'=>$ticketObj->csrv_ticket_id)).'" class="blue">
					<i class="icon-zoom-in bigger-130"></i>
				</a>

				<a href="'.m_appurl('cportal/ticket/edit',array('id'=>$ticketObj->csrv_ticket_id)).'" class="green">
					<i class="icon-pencil bigger-130"></i>
				</a>
			</div>

			<div class="visible-xs visible-sm hidden-md hidden-lg">
				<div class="inline position-relative">
					<button class="btn btn-minier btn-yellow dropdown-toggle" data-toggle="dropdown">
						<i class="icon-caret-down icon-only bigger-120"></i>
					</button>

					<ul class="dropdown-menu dropdown-only-icon dropdown-yellow pull-right dropdown-caret dropdown-close">
						<li>
							<a href="'.m_appurl('cportal/ticket/view',array('id'=>$ticketObj->csrv_ticket_id)).'" class="tooltip-info" data-rel="tooltip" title="" data-original-title="View">
								<span class="blue">
									<i class="icon-zoom-in bigger-120"></i>
								</span>
							</a>
						</li>

						<li>

							<a href="'.m_appurl('cportal/ticket/edit',array('id'=>$ticketObj->csrv_ticket_id)).'" class="tooltip-success" data-rel="tooltip" title="" data-original-title="Edit">
								<span class="green">
									<i class="icon-edit bigger-120"></i>
								</span>
							</a>
						</li>
					</ul>
				</div>
			</div></td>';


//<a href=\"".m_appurl('cportal','ticket','view', array('id'=>$ticketObj->csrv_ticket_id))."\">View</a></td>";
//			echo "<td $tdClass><a href=\"".m_appurl('cportal','ticket','edit', array('id'=>$ticketObj->csrv_ticket_id))."\">Edit</a></td>";
			echo "</tr>\n";
	}
?>
</tbody></table>

	<div class="row"><div class="col-sm-6"><div class="dataTables_info" id="sample-table-2_info">Showing 1 to 10 of 23 entries</div></div><div class="col-sm-6"><div class="dataTables_paginate paging_bootstrap"><ul class="pagination"><li class="prev disabled"><a href="#"><i class="icon-double-angle-left"></i></a></li><li class="active"><a href="#">1</a></li><li><a href="#">2</a></li><li><a href="#">3</a></li><li class="next"><a href="#"><i class="icon-double-angle-right"></i></a></li></ul></div></div></div>

</div>



												</div>
											</div>
										</div>

<!--
<div class="box02">
<div class="box02_top"></div>
	<div style="margin-left:2em;" class="box02_tabs">

<?php if ($response->tabOn == 0 ) { $cssClass='box02_tab_on'; } else { $cssClass='box02_tab';} ?>
		<span class="<?=$cssClass;?>" >
			<a href="<?=m_appurl('cportal/ticket/main/', array_merge($response->searchCrit, array('type'=>0)));?>" style="text-decoration:none;color:#FFF;padding:0em .5em;">All</a>
		</span>&nbsp;

<?php
foreach ($response->types as $_type) { 
$typeId = $_type->get('csrv_ticket_type_id');
?>

	<?php if ($response->tabOn == $typeId ) { $cssClass='box02_tab_on'; } else { $cssClass='box02_tab';} ?>
		<span class="<?=$cssClass;?>" >
		<a href="<?=m_appurl('cportal/ticket/main', array_merge($response->searchCrit, array('type'=>$typeId)));?>" style="text-decoration:none;color:#FFF;padding:0em .5em;"><?php echo $_type->get('display_name');?></a>
		</span>&nbsp;
<?php } ?>

	<div class="box02_content">

	</div>
</div>
-->

