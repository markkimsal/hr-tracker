<?php
$attItem = $response->ticketObj->stageItem;
if ($attItem === NULL || $attItem->dataItem->_isNew) {
	echo "This item has been deleted.";
	return;
}
$idate = explode('-', $attItem->get('incident_date'));
$fdate = $idate[1].'/'.$idate[2].'/'.$idate[0];

?>

<style type="text/css">
	._att_edit_block 
	{
		width:30%;float:left;
		padding-right:5px;margin:3px;
		border-color:#EEE;
		border-style:solid;
		border-width:2px;
	}


	._att_edit_block p {
		margin-top:0px;
		margin-bottom:0px;
	}
</style>



<input type="hidden" name="details_obj_id" id="details_obj_id" value="<?php echo $response->ticketObj->getPrimaryKey(); ?>" />
<div class="_att_edit_block">
	<h3>Employee</h3>
	<p>
	<?php 
	$a = Metrodi_Container::getContainer();
	$a->tryFileLoading('emp/employee.php');
	$emp = new Emp_Employee( $attItem->get('emp_id') );
	printf("%s, %s", $emp->getLastname(), $emp->getFirstname());
	?>
	</p>
	<br style="clear:both;"/>
</div>

<div class="_att_edit_block">
	<h3>Incident Date</h3>
	<p>
	<?php echo $attItem->get('incident_date');?>
	</p>
	<br style="clear:both;"/>
</div>

<div class="_att_edit_block">
	<h3>Corrective Action</h3>
	<p>
	<?php echo $attItem->getDisplayName();?>
	</p>
	<br style="clear:both;"/>
</div>


	<br style="clear:both;"/>

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

<h3>Submitted On</h3>
<p>
<?php echo date('m/d/Y', $attItem->get('created_on'));?>
</p>

