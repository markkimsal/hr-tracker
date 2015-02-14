<style type="text/css">
.box02_content table tr td {
border-bottom:1px solid black;
}
.box02_content table tr td.last {
border-bottom:none;
}

</style>


<div class="row">

	<div class="col-sm-6">
		<div class="widget-box">
			<div class="widget-header">
				<h4 class="smaller">
					Ticket Overview
				</h4>
			</div>

			<div class="widget-body">
				<div class="widget-main">
		<table border="0" class="table table-bordered">
<?php
	foreach($response->tickets as $statusId => $ticketList) {
		echo '<tr><td width="100%">'.$response->status[$statusId]->display_name."</td>";
		foreach ($ticketList as $ticketObj) {
			echo '<td><span class="badge badge-'.$response->status[$statusId]->style_name.'">'.$ticketObj->total."</span></td>\n";
		}
		echo "</tr>\n";
	}
?>
		</table>
				</div>
			</div>
		</div>
	</div>

	<div class="col-sm-6">
		<div class="widget-box">
			<div class="widget-header">
				<h4 class="smaller">
					My Tickets
					<small>by status</small>
				</h4>
			</div>

			<div class="widget-body">
				<div class="widget-main">
		<table border="0" class="table table-bordered">
<?php
	foreach($response->userTickets as $statusId => $ticketList) {
		echo "<tr><td>".$response->status[$statusId]->display_name."</td><td>";
		foreach ($ticketList as $ticketObj) {
			echo "<a href=\"".m_appurl('cportal/ticket/edit',array('id'=>$ticketObj->csrv_ticket_id))."\">#".sprintf('%04d',$ticketObj->csrv_ticket_id)."</a>, ";
		}
		echo "</td></tr>\n";
	}
?>
		</table>

				</div>
			</div>
		</div>
	</div>

</div> <!-- /.row -->

<hr/>


<div class="row">
	<div class="col-sm-12">
	<div class="box">

<div class="table-header"><h4 class="smaller">New Tickets</h4></div>

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
	$countTickets = count($response->recentTickets);
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
			echo '<td '.$tdClass.'>
			<div class="visible-md visible-lg hidden-sm hidden-xs action-buttons">
				<a href="'.m_appurl('cportal/ticket/view',array('id'=>$ticketObj->csrv_ticket_id)).'" class="blue">
					<i class="fa fa-search-plus fa-lg"></i>
				</a>

				<a href="'.m_appurl('cportal/ticket/edit',array('id'=>$ticketObj->csrv_ticket_id)).'" class="green">
					<i class="fa fa-edit fa-lg"></i>
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
									<i class="fa fa-search-plus bigger-120"></i>
								</span>
							</a>
						</li>

						<li>

							<a href="'.m_appurl('cportal/ticket/edit',array('id'=>$ticketObj->csrv_ticket_id)).'" class="tooltip-success" data-rel="tooltip" title="" data-original-title="Edit">
								<span class="green">
									<i class="fa fa-edit bigger-120"></i>
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

	</div> <!-- /.box -->
	</div> <!-- /.col-sm-12 -->
</div> <!-- /.row -->

<hr/>

<div class="row">
	<div class="col-sm-12">
	<div class="box">

<div class="table-header"><h4 class="smaller">Recent Tickets</h4></div>

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
	$countTickets = count($response->recentTickets);
	$tdClass = '';
	$_ctr = 0;
	foreach($response->recentTickets as $ticketObj) {
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
			echo '<td '.$tdClass.'>
			<div class="visible-md visible-lg hidden-sm hidden-xs action-buttons">
				<a href="'.m_appurl('cportal/ticket/view',array('id'=>$ticketObj->csrv_ticket_id)).'" class="blue">
					<i class="fa fa-search-plus fa-lg"></i>
				</a>
			';
			if($ticketObj->is_locked) {
			echo '
				<a href="'.m_appurl('cportal/ticket/edit',array('id'=>$ticketObj->csrv_ticket_id)).'" class="red">
				<i class="fa fa-lock fa-lg"></i>
				</a>';
			} else {

			echo '
				<a href="'.m_appurl('cportal/ticket/edit',array('id'=>$ticketObj->csrv_ticket_id)).'" class="green">
					<i class="fa fa-edit fa-lg"></i>
				</a>';
			}
			echo '
			
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

	</div> <!-- /.box -->
	</div> <!-- /.col-sm-12 -->
</div> <!-- /.row -->
