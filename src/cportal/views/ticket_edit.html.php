
<script src="<?=m_turl();?>scripts/bootbox.min.js"></script>
<script src="<?=m_url();?>src/cportal/views/jquery.jeditable.js"></script>
<script src="<?=m_url();?>src/cportal/views/ticket.js"></script>

<?php
//var_dump($response->ticketObj);
$typeId = $response->ticketObj->getTypeId();
$statusId = $response->ticketObj->getStatusId();
?>


<div class="widget-box pull-left">
<div class="widget-header">
<h5><i class="icon-pushpin"></i>Ticket Info</h5>
</div>
<div class="widget-body">
	<div style="float:left;border:1px solid #CCC;text-align:center;width:9em;"><span style="font-size:90%">Status</span><br/><span style="font-size:100%"><?=str_replace(' ', '&nbsp;', $response->status[$statusId]->display_name);?></span></div>
	<div style="float:left;border:1px solid #CCC;width:10em;text-align:center;"><span style="font-size:90%">Ticket Type</span><br/><span style="font-size:100%"><?= $response->types[$typeId]->display_name;?></span></div>
	<div style="float:left;border:1px solid #CCC;width:9em;text-align:center;"><span style="font-size:90%">Received</span><br/> 
				 <span style="font-size:90%"><?= date('M jS',$response->ticketObj->dataItem->created_on);?></span>
                  <span style="font-size:100%"><?= date('G:i',$response->ticketObj->dataItem->created_on);?></span></div>
	<div style="float:left;border:1px solid #CCC;width:6em;text-align:center;"><span style="font-size:90%">No.</span><br/><span style="font-size:100%"><?= $response->ticketObj->getId();?></span></div>
</div>
</div>



<?php
if ($response->viewonly == false) { ?>
<div class="col-sm-4 pull-left">
<div style="padding-left:1em;border:0;border-left:7px;border-color:#CCC; border-style:solid; margin-bottom:1em;">
Actions<hr style="margin:0;width:44em;"/>
<p>
	<button class="btn btn-info" data-toggle="modal" data-target="#dialog-addanote" id="link-addanote">Add a Note</button>
	<button class="btn btn-info" data-toggle="modal" data-target="#dialog-changestatus" id="bootbox-status">Change Status</button>
	<button class="btn btn-success" data-toggle="modal" data-target="#dialog-finalize" id="bootbox-final">Finalize</button>
</p>
</div>
</div>
<?php
}
?>



<?php

if ($response->viewonly == false) { ?>
<div class="well pull-right">
	<h4>Unlock Ticket <i class="icon icon-unlock bigger-130"></i></h4>
	<div class="box03_content">
	
	Done with this ticket? <a href="<?=m_appurl('cportal/ticket/unlock', array('id'=>$response->ticketObj->getId()));?>">Unlock it.</a>
	</div>
</div>

<?php
} else {
?>
<div class="well pull-right">
	<h4>Lock Ticket <i class="fa fa-lock fa-lg"></i></h4>
	<div class="box03_content">
	To make changes <a href="<?=m_appurl('cportal/ticket/edit', array('id'=>$response->ticketObj->getId()));?>">lock this ticket.</a>
	<br/>
	Go back home. <a href="<?=m_appurl('cportal/main');?>">Cancel.</a>
	</div>
</div>


<?php
}
?>


<!-- stupid spacer for CSS -->
<div style="clear:right;border:0;width:100%;"></div>

<?php
if ($response->viewonly == false) { ?>
	<script language="javascript">
	//jquery scrolling in the main template stops basic javasript.

	//basic javascript, no jquery
	function showAction(item, evt) {
		var divname = $(evt.target).attr('data-target');
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

<?php
}
?>

<div style="background-color:#FCFCE0;margin-top:2em;padding:.5em;border:2px;border-left:2px;border-color:#CCC; border-style:solid;width:98%">
<div style="background-color:#FFF;padding:.5em;padding-left:1em;">
<!--
<div style="background-color:#FCFCF3;margin-top:2em;padding:.5em;padding-left:1em;border:2px;border-left:7px;border-color:#CCC; border-style:solid;">
-->
<?php Metrofw_Template::parseSection('ticket_edit'); ?>
<?php // echo $response->ticketObj->getDescription($response); ?>
</div>
</div>

<script type="text/javascript">
jQuery(document).ready(function() {
	$("#log-tab-1").bind("click", {t: "both", onSpan: "#log-tab-1", offSpan1: "#log-tab-2", offSpan2: "#log-tab-3"}, doLogUpdate);
	$("#log-tab-2").bind("click", {t: "comments", onSpan: "#log-tab-2", offSpan1: "#log-tab-1", offSpan2: "#log-tab-3"}, doLogUpdate);
	$("#log-tab-3").bind("click", {t: "status", onSpan: "#log-tab-3", offSpan1: "#log-tab-1", offSpan2: "#log-tab-2"}, doLogUpdate);
});

	function doLogUpdate(evt, typ) {
			$.ajax({
				url: '<?= m_appurl('cportal/ticket/log',array('id'=>$response->ticketObj->getId()));?>'+typ,
				type: 'GET',
				dataType: 'html',
				timeout: 1000,
				error: function(xml, desc){
					alert('Error loading XML document: ' + desc);
					event.stopPropagation(); 
					event = null;
					return false;
				},
				success: function(xml){
					$("#ticket-item-log").empty();
					$("#ticket-item-log").html(xml);
					/*
					$(xml).find('item').each(function(){
						var item_text = $(this).text();

						$('<li></li>')
							.html(item_text)
							.appendTo('ol');
					});
					 */
					// do something with xml
				}
			});
			return true;
	}
</script>

<p>&nbsp;</p>
<style type="text/css">
#ticket-item-log li {
border-bottom: 1px solid black;
margin-bottom: 7px;
padding-bottom: 3px;
}
</style>


<a name="tab-log"></a>
<div class="col-sm-12">
	<div class="tabbable">
		<ul class="nav nav-tabs padding-12 tab-color-blue background-blue">
			<li class="active">
				<a data-toggle="tab" data-bind="click:function (data, evt) {doLogUpdate(evt, 'comments')}" href="#tab-comments">Comments</a>
			</li>

			<li class="">
				<a data-toggle="tab" data-bind="click:function (data, evt) {doLogUpdate(evt, 'status')}" href="#tab-comments">Changes</a>
			</li>

			<li class="">
				<a data-toggle="tab" data-bind="click:function (data, evt) {doLogUpdate(evt, 'both')}" href="#tab-comments">Both</a>
			</li>
		</ul>

		<div class="tab-content">
			<div id="tab-comments" class="tab-pane active">
		<ol id="ticket-item-log">
<?php foreach ($response->comments as $_cobj): ?>
			<li>On <?= date('M jS @G:i', $_cobj->created_on);?> user <i><?= $_cobj->author;?></i> wrote:
			<br/>
			<?= nl2br($_cobj->message);?>
			</li>
<?php endforeach; ?>
		</ol>

			</div>
		</div>
	</div>
</div>

<div id="dialog-addanote" class="modal fade" aria-hidden="true" role="dialog">
<div class="modal-dialog">
<div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="modal-title" id="myModalLabel">Add a Note</h4>
      </div>

	<div class="modal-body">
		<font size="-1">Communication quick-buttons (optional)</font><br/>
			<input class="btn btn-sm" type="button" value="I called..." onclick="addTicketStamp('I called the user about this ticket.');"/>
			&nbsp;
			<input class="btn btn-sm" type="button" value="I sent an e-mail..." onclick="addTicketStamp('I sent an e-mail to the user about this ticket.');"/>
			&nbsp;
			<input class="btn btn-sm" type="button" value="I left voice-mail..." onclick="addTicketStamp('I left a voice mail message with about this ticket.');"/>
			&nbsp;
			<input class="btn btn-sm" type="button" value="I left a message..." onclick="addTicketStamp('I left a message with NAME about this ticket.');"/>
		<form method="POST" action="<?= m_appurl('cportal/ticket/comment');?>">
		<textarea style="width:100%;margin:2px;border:1px ridge grey;" id="comment_ticket" name="comment" rows="10"></textarea>

	</div>
    <div class="modal-footer">
		<button type="submit" class="btn btn-default" value="Save Note">Save Note</button>
		<input type="hidden" name="id" value="<?=$response->ticketObj->getId();?>"/>
		</form>
	</div>
</div>
</div>
</div>



<div id="dialog-changestatus" class="modal fade" aria-hidden="true" role="dialog">
<div class="modal-dialog">
<div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="modal-title" id="myModalLabel">Change Status</h4>
      </div>

	<div class="modal-body">
		<form  method="POST" action="<?= m_appurl('cportal/ticket/status');?>">
		<select name="status_id">
		<option>[Change status]</option>
		<?php
		foreach ($response->status as $_stid => $_st) { if ($_st->is_initial) { continue;} if ($_st->is_terminal) {continue;} ?>
		<option value="<?=$_stid;?>"><?=$_st->display_name;?></option>
		<?php
		} ?>
		</select>

	</div>
    <div class="modal-footer">
		<input type="hidden" name="id" value="<?=$response->ticketObj->getId();?>"/>
		<input class="btn" type="submit" name="submit_btn" value="change status"/>
		</form>
	</div>
</div>
</div>
</div>


<div id="dialog-finalize" class="modal fade" aria-hidden="true" role="dialog">
<div class="modal-dialog">
<div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="modal-title" id="myModalLabel">Finalize Ticket</h4>
      </div>

	<div class="modal-body">
		<form  method="GET" action="<?= m_appurl('cportal/ticket/finalize');?>">
		<select name="status_id">
		<option>[Change status]</option>
		<?php
		foreach ($response->final as $_stid => $_st) { ?>
		<option value="<?=$_stid;?>"><?=$_st->display_name;?></option>
		<?php
		} ?>
		</select>

	</div>
    <div class="modal-footer">
		<input type="hidden" name="id" value="<?=$response->ticketObj->getId();?>"/>
		<input class="btn" type="submit" value="finalize ticket"/>
		</form>
	</div>
</div>
</div>
</div>


