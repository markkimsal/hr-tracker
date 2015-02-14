<?php
//var_dump($t['ticketObj']);
$typeId = $t['ticketObj']->getTypeId();
$statusId = $t['ticketObj']->getStatusId();
?>


<div class="box01" style="width:auto;float:left;">
<div class="box01_top" style="float:left;">
	<div style="float:left;border:1px solid #CCC;text-align:center;width:9em;"><span style="font-size:90%">Status</span><br/><span style="font-size:100%"><?=str_replace(' ', '&nbsp;', $t['status'][$statusId]->display_name);?></span></div>
	<div style="float:left;border:1px solid #CCC;width:10em;text-align:center;"><span style="font-size:90%">Ticket Type</span><br/><span style="font-size:100%"><?= $t['types'][$typeId]->display_name;?></span></div>
	<div style="float:left;border:1px solid #CCC;width:9em;text-align:center;"><span style="font-size:90%">Received</span><br/> 
				 <span style="font-size:90%"><?= date('M jS',$t['ticketObj']->dataItem->created_on);?></span>
                  <span style="font-size:100%"><?= date('G:i',$t['ticketObj']->dataItem->created_on);?></span></div>
	<div style="float:left;border:1px solid #CCC;width:6em;text-align:center;"><span style="font-size:90%">No.</span><br/><span style="font-size:100%"><?= $t['ticketObj']->getId();?></span></div>
</div>
</div>



<?php

if ($t['viewonly'] == false) { ?>
<div class="box03" style="float:right;width:220px;">
	<div class="box03_top">Unlock Ticket</div>
	<div class="box03_content">
	Done with this ticket? <a href="<?=cgn_appurl('cportal','ticket','unlock', array('id'=>$t['ticketObj']->getId()));?>">Unlock it.</a>
	</div>
</div>

<?php
} else {
?>
<div class="box03" style="float:right">
	<div class="box03_top">Lock Ticket</div>
	<div class="box03_content">
	To make changes <a href="<?=cgn_appurl('cportal','ticket','edit', array('id'=>$t['ticketObj']->getId()));?>">lock this ticket.</a>
	<br/>
	Go back home. <a href="<?=cgn_appurl('cportal','main');?>">Cancel.</a>
	</div>
</div>


<?php
}
?>


<!-- stupid spacer for CSS -->
<div style="clear:right;border:0;width:100%;"></div>

<?php
if ($t['viewonly'] == false) { ?>
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

<!--
<fieldset style="width:auto;margin-right:1em;"><legend>Actions</legend>
-->
<div style="padding-left:1em;border:0;border-left:7px;border-color:#CCC; border-style:solid; margin-bottom:1em;">
Actions<hr style="margin:0;width:44em;"/>
<a href="#" id="addanote-link" onclick="showAction('addanote'); return false;">Add a Note</a>
&nbsp;
&nbsp;
&nbsp;
<a href="#" id="tkstatus-link" onclick="showAction('changestatus'); return false;">Change Status</a>
&nbsp;
&nbsp;
&nbsp;
<a href="#" id="tkfinal-link" onclick="showAction('finalizenote'); return false;">Finalize</a>

<div id="addanote" style="display:none;">
<br/>
<div class="box01">
	<div class="box01_top">Add a Note &nbsp; <font size="-2"><a href="#" onclick="document.getElementById('addanote').style.display='none';return false;">[Cancel]</a></font></div>
	<div class="box01_content">
		<font size="-1">Communication quick-buttons (optional)</font><br/>
			<input type="button" value="I called..." onclick="addTicketStamp('I called the user about this ticket.');"/>
			&nbsp;
			<input type="button" value="I sent an e-mail..." onclick="addTicketStamp('I sent an e-mail to the user about this ticket.');"/>
			&nbsp;
			<input type="button" value="I left voice-mail..." onclick="addTicketStamp('I left a voice mail message with about this ticket.');"/>
			&nbsp;
			<input type="button" value="I left a message..." onclick="addTicketStamp('I left a message with NAME about this ticket.');"/>
		<form method="POST" action="<?= cgn_appurl('cportal','ticket','comment');?>">
		<textarea style="margin:2px;border:1px ridge grey;" id="comment_ticket" name="comment" cols="85" rows="10"></textarea>
		<br/>
		<input type="submit" value="Save Note"/>
		<input type="hidden" name="id" value="<?=$t['ticketObj']->getId();?>"/>
		</form>
	</div>
</div>
</div>

<div id="changestatus" style="display:none;">
<br/>
<div class="box01" style="width:97%">
	<div class="box01_top">Change Status &nbsp; <font size="-2"><a href="#" onclick="document.getElementById('changestatus').style.display='none';return false;">[Cancel]</a></font></div>
	<div class="box01_content">
		<form  method="POST" action="<?= cgn_appurl('cportal','ticket','status');?>">
		<select name="status_id">
		<option>[Change status]</option>
		<?php
		foreach ($t['status'] as $_stid => $_st) { if ($_st->is_initial) { continue;} if ($_st->is_terminal) {continue;} ?>
		<option value="<?=$_stid;?>"><?=$_st->display_name;?></option>
		<?php
		} ?>
		</select>

		<input type="hidden" name="id" value="<?=$t['ticketObj']->getId();?>"/>
		<input type="submit" name="submit_btn" value="change status"/>
		</form>
	</div>
	</div>
</div>

<div id="finalizenote" style="display:none;">
<br/>
<div class="box01" style="width:97%">
	<div class="box01_top">Finalize Ticket &nbsp; <font size="-2"><a href="#" onclick="document.getElementById('finalizenote').style.display='none';return false;">[Cancel]</a></font></div>
	<div class="box01_content">
		<form  method="GET" action="<?= cgn_appurl('cportal','ticket','finalize');?>">
		<select name="status_id">
		<option>[Change status]</option>
		<?php
		foreach ($t['final'] as $_stid => $_st) { ?>
		<option value="<?=$_stid;?>"><?=$_st->display_name;?></option>
		<?php
		} ?>
		</select>

		<input type="hidden" name="id" value="<?=$t['ticketObj']->getId();?>"/>
		<input type="submit" value="finalize ticket"/>
		</form>
	</div>
	</div>
</div>


</div>
<!--
</fieldset>
-->
<?php
}
?>

<div style="background-color:#FCFCE0;margin-top:2em;padding:.5em;border:2px;border-left:2px;border-color:#CCC; border-style:solid;width:98%">
<div style="background-color:#FFF;padding:.5em;padding-left:1em;">
<!--
<div style="background-color:#FCFCF3;margin-top:2em;padding:.5em;padding-left:1em;border:2px;border-left:7px;border-color:#CCC; border-style:solid;">
-->
<?= $t['ticketObj']->getDescription($t); ?>
</div>
</div>

<script type="text/javascript">
jQuery(document).ready(function() {
	$("#log-tab-1").bind("click", {t: "both", onSpan: "#log-tab-1", offSpan1: "#log-tab-2", offSpan2: "#log-tab-3"}, doLogUpdate);
	$("#log-tab-2").bind("click", {t: "comments", onSpan: "#log-tab-2", offSpan1: "#log-tab-1", offSpan2: "#log-tab-3"}, doLogUpdate);
	$("#log-tab-3").bind("click", {t: "status", onSpan: "#log-tab-3", offSpan1: "#log-tab-1", offSpan2: "#log-tab-2"}, doLogUpdate);
});

	function doLogUpdate(event) {
			$(event.data.onSpan).attr({class:"box02_tab_on"});
			$(event.data.offSpan1).attr({class:"box02_tab"});
			$(event.data.offSpan2).attr({class:"box02_tab"});
			$.ajax({
				url: '<?= cgn_appurl('cportal','ticket','log',array('xhr'=>'1','id'=>$t['ticketObj']->getId()));?>t='+event.data.t,
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
					$("#ticket-log").empty();
					$("#ticket-log").html(xml);
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
			event.stopPropagation();
			event = null;
			return false;
	}
</script>

<p>&nbsp;</p>
<style type="text/css">
#ticket-log li {
border-bottom: 1px solid black;
margin-bottom: 7px;
padding-bottom: 3px;
}
</style>
<a name="tab-lob"></a>

<div class="box02">
	<div class="box02_top">Ticket Log</div>
	<div style="margin-left:2em;" class="box02_tabs">
	<?php if ($t['tabOn'] == 1 ) { $cssClass='box02_tab_on'; } else { $cssClass='box02_tab';} ?>
		<span class="<?=$cssClass;?>" id="log-tab-1">
			<a href="#tab-log" style="text-decoration:none;color:#FFF;padding:.5em;">All</a>
		</span>&nbsp;
	<?php if ($t['tabOn'] == 0 ) { $cssClass='box02_tab_on'; } else { $cssClass='box02_tab';} ?>
		<span class="<?=$cssClass;?>" id="log-tab-2">
			<a href="#tab-log" style="text-decoration:none;color:#FFF;padding:.5em;">Comments</a>
		</span>&nbsp;
	<?php if ($t['tabOn'] == 2 ) { $cssClass='box02_tab_on'; } else { $cssClass='box02_tab';} ?>
		<span class="<?=$cssClass;?>" id="log-tab-3">
			<a href="#tab-log" style="text-decoration:none;color:#FFF;padding:.5em;">Status Changes</a>
		</span>&nbsp;
	</div>

	<div class="box02_content">
		<ol id="ticket-log">
<?php
			foreach ($t['comments'] as $_cobj) { ?>
			<li>On <?= date('M jS @G:i', $_cobj->created_on);?> user <i><?= $_cobj->author;?></i> wrote:
			<br/>
			<?= nl2br($_cobj->message);?>
			</li>
<?php
			}
?>
		</ol>
	</div>
</div>

<div id="example" class="flora" title="Part Number Look-up Tool">I'm in a dialog!</div>
  <script>
  $(document).ready(function(){
    $("#example").dialog({autoOpen:false, width:600, height:300, draggable:true});
  });

function loadPartData($orderId, $itemId) {

    $("#example").html("Loading...");
	$("#example").load("<?=cgn_appurl('cportal','partnum');?>?isAjax=true&oid="+$orderId+"&iid="+$itemId);
}
  </script>

