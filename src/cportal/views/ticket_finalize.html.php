
<?php
//var_dump($response->ticketObj);
$typeId   = $response->ticketObj->getTypeId();
$statusId = $response->ticketObj->getStatusId();
$pkey     = $response->ticketObj->getPrimaryKey();

$editClass = ($response->viewOnly == FALSE) ? 'details_edit' : 'details_view';
?>


<div class="widget-box pull-left">
<div class="widget-header">
<h5><i class="fa fa-pushpin"></i>Ticket Info &mdash; #<?= $response->ticketObj->getId();?></h5>
</div>
<div class="widget-body">
	<div class="well pull-left">
		<h5>Status</h5>
		<?=str_replace(' ', '&nbsp;', $response->status[$statusId]->display_name);?>
	</div>
	<div class="well pull-left">
		<h5>Ticket Type</h5>
		<?= $response->types[$typeId]->display_name;?>
	</div>
	<div class="well pull-left">
		<h5>Received</h5> 
		<?= date('M jS',$response->ticketObj->dataItem->created_on);?>
		<?= date('G:i',$response->ticketObj->dataItem->created_on);?>
	</div>

</div>
</div>



<form style="display:inline;" method="POST" action="<?= m_appurl('cportal/ticket/close');?>">


<div class="box01" style="width:auto">
<div class="box01_top"><h3>Resolution: <?=$response->status[$response->finalStatusId]->display_name;?></h3></div>
</div>



<p></p>

<div id="addanote" style="display:block;">
<br/>
<div class="box01">
	<div class="box01_top">Enter an optional comment to explain why you are closing this ticket.</div>
	<div class="box01_content">
		<div class="row">
		<div class="container">
		<font size="-1">Communication quick-buttons (optional)</font><br/>
			<input type="button" value="I called..." onclick="addTicketStamp('I called the user about this ticket.');"/>
			&nbsp;
			<input type="button" value="I sent an e-mail..." onclick="addTicketStamp('I sent an e-mail to the user about this ticket.');"/>
			&nbsp;
			<input type="button" value="I left voice-mail..." onclick="addTicketStamp('I left a voice mail message with about this ticket.');"/>
			&nbsp;
			<input type="button" value="I left a message..." onclick="addTicketStamp('I left a message with NAME about this ticket.');"/>
		</div>
		</div>
		<div class="row">
		<div class="container">
		<textarea style="margin:2px;border:1px ridge grey;" id="comment_ticket" name="comment" cols="85" rows="10"></textarea>
		</div>
		</div>
	</div>
</div>
</div>


<input type="hidden" name="id" value="<?=$response->ticketObj->getId();?>"/>
<input type="hidden" name="status_id" value="<?=$response->finalStatusId;?>"/>
<input type="submit" name="submit_btn" value="close ticket"/>
</form>

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
	</script>

