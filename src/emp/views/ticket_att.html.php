<?php
$attItem = $response->ticketObj->stageItem;
if ($attItem === NULL || $attItem->dataItem->_isNew) {
	echo "This item has been deleted.";
	return;
}

$idate = explode('-', $attItem->get('incident_date'));
$fdate = $idate[1].'/'.$idate[2].'/'.$idate[0];
?>

<input type="hidden" name="details_obj_id" id="details_obj_id" value="<?php echo $response->ticketObj->getPrimaryKey(); ?>" />


<div class="_att_edit_block">
	<h3>Employee</h3>
	<p>
	<a href="<?php echo m_appurl('emp/main/view', array('emp_id'=>$attItem->get('emp_id')));?>">
	<?php 
	$a = Metrodi_Container::getContainer();
	$a->tryFileLoading('emp/employee.php');
	$emp = new Emp_Employee( $attItem->get('emp_id') );
	printf("%s, %s", $emp->getLastname(), $emp->getFirstname());
	?></a>
	</p>
	<br style="clear:both;"/>
</div>

<div class="_att_edit_block">
	<h3>Incident Date</h3>
	<div id="ieidate" class="iedit_date" data-updateaction="emp/attend/updateDate" data-date="<?php echo $fdate;?>" data-name="idate"><?php echo $fdate;?></div>
	<br style="clear:both;"/>
</div>

<div class="_att_edit_block">
	<h3>Incident Type</h3>
	<p>
	<div id="ieitype" class="iedit_select" data-updateaction="emp/attend/updateType" data-sourceurl="emp/attend/listTypes" data-name="code"><?php echo '('.$attItem->get('code').') '.$attItem->getTypeName();?></div>
	</p>
	<br style="clear:both;"/>
</div>

<br style="clear:both;"/>

<div class="_att_edit_block" >
	<h3>Points</h3>
	<p>

	<div id="ieipoint" class="iedit_text" data-propdesc="Points" data-updateaction="emp/attend/updatePoints" data-name="points">
		<?php echo $attItem->get('points');?>
    </div>
	</p>
	<br style="clear:both;"/>
</div>

<div class="_att_edit_block">
	<h3>Corrective Action</h3>
	<p>
	<div id="ieicorr" class="iedit_select" data-updateaction="emp/attend/updateCorrAtt" data-sourceurl="emp/attend/listCorrAtt" data-name="corr_act"><?php echo $attItem->getCorrectiveName();?></div>
	</p>
	<br style="clear:both;"/>
</div>

<div class="_att_edit_block">
	<h3>Vacation Hours</h3>
	<p>
	<div id="ieihr" class="iedit_text" data-propdesc="Vacation Hours" data-updateaction="emp/attend/updateField" data-name="vac_hr">
	<?php echo $attItem->get('vac_hr') ? $attItem->get('vac_hr'): 'none';?>
	</div>
	</p>
	<br style="clear:both;"/>
</div>

<br style="clear:both;"/>

<h3>Description</h3>
<div id="ieidesc" class="iedit_area" data-propdesc="Description" data-updateaction="emp/attend/updateDesc" data-name="description"><p><?php echo nl2br($attItem->get('description')); ?></p></div>


<h3>Approved</h3>
<p>
<?php echo $attItem->get('approved');?>
</p>

<h3>Submitted By</h3>
<p>
<?php 
$owner = Metrou_User::load($attItem->get('owner_id'));
if ($owner) {
	echo $owner->getDisplayName();
}
?>
</p>

