
<script src="<?=m_url();?>src/cpemp/views/emp.js"></script>

<?php
$cpempid = $response->emp->get('cpemp_id');
?>

<div class="widget-box pull-left">
<div class="widget-header">
<h5><i class="icon-user"></i>Employee Info</h5>
</div>
<div class="widget-body">

	<div style="float:left;border:1px solid #CCC;text-align:center;width:9em;"><span style="font-size:90%">Status</span><br/><span style="font-size:100%"><?=str_replace(' ', '&nbsp;', $response->emp->getEmploymentStatusName());?>&nbsp;</span></div>

	<div style="float:left;border:1px solid #CCC;width:10em;text-align:center;"><span style="font-size:90%">Hire Date</span><br/><span style="font-size:100%"><?= date('m/d/Y', strtotime($response->emp->get('hire_date')));?></span></div>

	<div style="float:left;border:1px solid #CCC;width:10em;text-align:center;"><span style="font-size:90%">Attendance Points</span><br/><span style="font-size:100%"><?= $response->attPoints;?></span></div>

	<div style="float:left;border:1px solid #CCC;width:10em;text-align:center;"><span style="font-size:90%">Vacation Hours Used</span><br/><span style="font-size:100%"><?= $response->vacHours;?></span></div>

</div>
</div>

<div style="clear:left"></div> 

<br style="float:left;"/>

<!-- stupid spacer for CSS -->
<div style="clear:right;border:0;width:100%;"></div>

<?php
if ($response->viewonly == false) { ?>
	<script language="javascript">
	//jquery scrolling in the main template stops basic javasript.

	//basic javascript, no jquery
	function showAction(divname) {
		if (jQuery(document)) { return false; }
		document.getElementById('addanote').style.display='none';
		document.getElementById('finalizenote').style.display='none';
		document.getElementById('changestatus').style.display='none';
		document.getElementById(divname).style.display='block';

	}

	function addTicketStamp(comments) {
		var timestamp = '* <?=date('F-d-Y');?> ';
		var timestamp = timestamp + "\n<?=str_repeat('-',80);?>\n";
		document.getElementById('comment_ticket').value = 
			timestamp + comments + "\n\n" + document.getElementById('comment_ticket').value;
	}

	jQuery(document).ready(function() {
		//only for ticket edit actions
		$("#wpi-link").click(function(e) { e.preventDefault();
			$(".action-box").slideUp();$("#wpi").slideToggle();});
		$("#safetyrec-link").click(function(e) { e.preventDefault(); 
			$(".action-box").slideUp();$("#safetyrec").slideToggle();});
		$("#attendrec-link").click(function(e) { e.preventDefault(); 
			$(".action-box").slideUp();$("#attendrec").slideToggle();});
	});
</script>

<!--
<fieldset style="width:auto;margin-right:1em;"><legend>Actions</legend>
-->


<div class="action-wrapper">
Actions<hr style="margin:0;width:44em;"/>
<?php
	foreach($response->forms as $fid => $_f):
		$formTitle = $_f->label;
?>

	<button class="btn btn-primary btn-<?=$fid;?>" data-toggle="modal" data-target="#dialog-<?= $fid;?>" id="link-<?= $fid;?>"><i class="fa fa-<?=$fid;?>"></i> <?= htmlspecialchars($formTitle);?></button>
<?php endforeach; ?>

</div>

<?php
	foreach($response->forms as $fid => $_f):
		$formTitle = $_f->label;
?>
<div id="dialog-<?=$fid;?>" class="modal fade" aria-hidden="true" role="dialog">
<div class="modal-dialog">
<div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="modal-title" id="myModalLabel"><?= $_f->label;?></h4>
      </div>

	<div class="modal-body">
	<?php echo $_f->toHtml(); ?>
	</div>
    <div class="modal-footer">
		<button type="submit" class="btn btn-default" onclick="$('#<?=$fid;?>').submit();" value="Save">Save</button>
		<input type="hidden" name="id" value=""/>
	</div>
</div>
</div>
</div>

<?php endforeach; ?>


<!--
</fieldset>
-->
<?php
}
?>

<div class="ticket-body-wrapper">
<div class="ticket-body-main">
	<h3>Attendance History</h3>

	<?php
	echo $response->attendanceTable->toHtml();
	?>

	<h3>Vacation History</h3>

	<?php
	echo $response->vacationTable->toHtml();
	?>


	<h3>Performance Incident History</h3>

	<?php
	echo $response->incidentTable->toHtml();
	?>


	<h3>Training History</h3>

	<?php
	echo $response->trainingTable->toHtml();
	?>

</div>
</div>

<p>&nbsp;</p>
<style type="text/css">
#ticket-log li {
border-bottom: 1px solid black;
margin-bottom: 7px;
padding-bottom: 3px;
}
</style>



<script type="text/Javascript" defer="defer">
/*
dojo.require("dijit.dijit"); 
dojo.require("dijit.form.DateTextBox");
dojo.require("dojo.parser");
    dojo.addOnLoad(function() {
		var theme = 'tundra';
		var adate = dojo.byId('att_date').value.split("-");
		var wdate = dojo.byId('wpi_date').value.split("-");
		var sdate = dojo.byId('safety_date').value.split("-");

		if(!dojo.hasClass(dojo.body(),theme))
		{
			        dojo.addClass(dojo.body(),theme);
		}
		dojo.parser.parse(dojo.body());

        var myDate = new dijit.form.DateTextBox({
			id: "att_date",
			name: "att_date",
			value: new Date(adate[0], adate[1]-1, adate[2])
       },"att_date");
        var myDate2 = new dijit.form.DateTextBox({
			id: "wpi_date",
			name: "wpi_date",
			value: new Date(wdate[0], wdate[1]-1, wdate[2])
        },
        "wpi_date");
        var myDate3 = new dijit.form.DateTextBox({
			id: "safety_date",
			name: "safety_date",
			value: new Date(sdate[0], sdate[1]-1, sdate[2])
        },
        "safety_date");

    });
*/
</script>
