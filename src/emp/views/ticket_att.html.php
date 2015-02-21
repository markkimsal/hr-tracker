<?php
$attItem = $response->ticketObj->stageItem;
if ($attItem === NULL || $attItem->dataItem->_isNew) {
	echo "This item has been deleted.";
	return;
}

$idate = explode('-', $attItem->get('incident_date'));
$fdate = $idate[1].'/'.$idate[2].'/'.$idate[0];

if ($t->editMode)  {
?>



<script type="text/Javascript"><!--
$(function() {

TicketModel.typeName('(<?php echo $attItem->get('code');?>) <?php echo $attItem->getTypeName();?>' || null);
TicketModel.incidentPoints('<?php echo $attItem->get('points');?>' || null);
TicketModel.vacHours('<?php echo $attItem->get('vac_hr');?>' || null);
TicketModel.description('<?php echo $attItem->get('description');?>' || null);
$.getJSON('<?php echo m_appurl('cpemp/attend/listTypes');?>', function(data) {
	TicketModel.typeList = data;
});
TicketModel.typeList
ko.applyBindings( TicketModel );
});
/*
dojo.require("dojo.data.ItemFileReadStore");
dojo.require("dijit.dijit"); 
dojo.require("dijit.form.TextBox");
dojo.require("dijit.form.DateTextBox");
dojo.require("dijit.form.ComboBox");
dojo.require("dijit.form.Select");
dojo.require("dijit.form.FilteringSelect");
dojo.require("dijit.InlineEditBox");


    var ieb;
    var iet;
    var ied;
    var iep;
    var iec;
	var att_types;

    dojo.addOnLoad(function() {
		var qstore = new dojo.data.ItemFileReadStore({url: "<?php echo m_appurl('cpemp', 'attend', 'listTypes', array('xhr'=>true));?>"});
		var cstore = new dojo.data.ItemFileReadStore({url: "<?php echo m_appurl('cpemp', 'attend', 'listCorrAtt', array('xhr'=>true));?>"});

		iet = new dijit.InlineEditBox({
            editor: "dijit.form.FilteringSelect",
			editorParams: {
				name:"cpemp_id",
				title: "firstname",
				autoComplete:false,
				clearOnClose:true,
				urlPreventCache:true,
				store:qstore,
				searchAttr:"label"
			},

            autoSave: false,
			updateUrl: "<?php echo m_appurl('cpemp', 'attend', 'updateType', array('xhr'=>true));?>"
		},
		"ieitype");

		iet._origSave = iet.save;
		iet.save = saveFSelectCode;

		iec = new dijit.InlineEditBox({
            editor: "dijit.form.FilteringSelect",
			editorParams: {
				name:"cpemp_id",
				title: "firstname",
				autoComplete:false,
				clearOnClose:true,
				urlPreventCache:true,
				store:cstore,
				searchAttr:"label"
			},

            autoSave: false,
			updateUrl: "<?php echo m_appurl('cpemp', 'attend', 'updateCorrAtt', array('xhr'=>true));?>"
		},
		"ieicorr");

		iec._origSave = iec.save;
		iec.save = saveIec;

        ieb = new dijit.InlineEditBox({
            editor: "dijit.form.DateTextBox",
			editorParams: {constraints: {datePattern: 'MM/dd/yyyy'}},
            autoSave: false
        },
        "ieidate");

		ieb._origSave = ieb.save;
		ieb.save = saveIeb;

		iep = new dijit.InlineEditBox({
			editor: "dijit.form.TextBox",
			autoSave: false
		},
		"ieipoint");

		iep._origSave = iep.save;
		iep.save = saveIep;

		ieh = new dijit.InlineEditBox({
			editor: "dijit.form.TextBox",
			autoSave: false
		},
		"ieihr");

		ieh._origSave = ieh.save;
		ieh.save = saveIeh;

    });

	function saveIeh(_9) {
		if(this.disabled||!this.editing){
		return;
		}
		ieh.newval = ieh.wrapperWidget.editWidget.attr('value');

		dojo.xhrPost({
			url: "<?php echo m_appurl('cpemp', 'attend', 'updateField', array('xhr'=>true));?>",
			postData: "csrv_ticket_id="+dojo.byId('csrv_ticket_id').value+"&fd=Vacation Hours&f=vac_hr&float="+ieh.newval,
			//handleAs:"text",
			handleAs:"json",
			load: function(data) {
				if (data.result == "good") {
					ieh._origSave(_9);
				}
			}
		});
	}

	function saveIeb(_9) {
		if(this.disabled||!this.editing){
		return;
		}
		ieb.newval = ieb.wrapperWidget.editWidget.attr('value');
		ieb.newval = dojo.date.locale.format(ieb.newval, {datePattern: 'EEE MMM d y', timePattern: 'HH:mm:ss ZZZZ'});

		dojo.xhrPost({
			url: "<?php echo m_appurl('cpemp', 'attend', 'updateDate', array('xhr'=>true));?>",
			postData: "csrv_ticket_id="+dojo.byId('csrv_ticket_id').value+"&date="+ieb.newval,
			//handleAs:"text",
			handleAs:"json",
			load: function(data) {
				if (data.result == "good") {
					ieb._origSave(_9);
				}

				if (data.result == "bad") {
					alert("Cannot parse date: "+ data.badDate);
				}
			}
		});
	}

	function saveIep(_9) {
		if(this.disabled||!this.editing){
		return;
		}
		iep.newval = iep.wrapperWidget.editWidget.attr('value');

		dojo.xhrPost({
			url: "<?php echo m_appurl('cpemp', 'attend', 'updateField', array('xhr'=>true));?>",
			postData: "csrv_ticket_id="+dojo.byId('csrv_ticket_id').value+"&fd=Points&f=points&float="+iep.newval,
			//handleAs:"text",
			handleAs:"json",
			load: function(data) {
				if (data.result == "good") {
					iep._origSave(_9);
				}
			}
		});
	}

	function saveFSelectCode(_9) {
		if(this.disabled||!this.editing){
		return;
		}
		this.newval = -1;
		var ww=this.wrapperWidget;
		var v=ww.getValue();

		var ew = ww.editWidget;
		this.newval = ew.item.code[0];

		dojo.xhrPost({
			url: this.updateUrl,
			postData: "csrv_ticket_id="+dojo.byId('csrv_ticket_id').value+"&type="+iet.newval,
			//handleAs:"text",
			handleAs:"json",
			load: function(data) {
				if (data.result == "good") {
					iet._origSave(_9);
				}
			}
		});
	}

	function saveIec(_9) {
		if(this.disabled||!this.editing){
		return;
		}
		this.newval = -1;
		var ww=this.wrapperWidget;
		var v=ww.getValue();

		var ew = ww.editWidget;
		this.newval = ew.item.code[0];

		dojo.xhrPost({
			url: this.updateUrl,
			postData: "csrv_ticket_id="+dojo.byId('csrv_ticket_id').value+"&corr_act="+iec.newval,
			//handleAs:"text",
			handleAs:"json",
			load: function(data) {
				if (data.result == "good") {
					iec._origSave(_9);
				}
			}
		});
	}
-->
*/
</script>
<?php } ?>

<input type="hidden" name="csrv_ticket_id" id="csrv_ticket_id" value="<?php echo $attItem->getPrimaryKey(); ?>" />


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
	<div id="ieidate">
	<?php echo $fdate;?>
	</div>
	<br style="clear:both;"/>
</div>

<div class="_att_edit_block">
	<h3>Incident Type</h3>
	<p>
	<div id="ieitype" data-updateaction="attend/updateType" data-property="code" data-bind="jeditable:typeName, jeditableOptions: {loadurl: '<?php echo m_appurl('attendance/main/listTypes');?>', type:'select' }"> <?php echo '('.$attItem->get('code').') '.$attItem->getTypeName();?> </div>

	</p>
	<br style="clear:both;"/>
</div>

<br style="clear:both;"/>

<div class="_att_edit_block" >
	<h3>Points</h3>
	<p>

	<div id="ieipoint" data-propdesc="Points" data-updateaction="attend/updatePoints" data-property="points" data-bind="jeditable:incidentPoints, jeditableOptions: {onblur:'cancel'}">
		<?php echo $attItem->get('points');?>
    </div>
	</p>
	<br style="clear:both;"/>
</div>

<div class="_att_edit_block">
	<h3>Corrective Action</h3>
	<p>
	<div id="ieicorr">
	<?php echo $attItem->getCorrectiveName();?>
	</div>
	</p>
	<br style="clear:both;"/>
</div>

<div class="_att_edit_block">
	<h3>Vacation Hours</h3>
	<p>
	<div id="ieihr" data-propdesc="Vacation Hours" data-updateaction="attend/updateField" data-property="vac_hr" data-bind="jeditable:vacHours, jeditableOptions: {onblur:'cancel'}">
	<?php echo $attItem->get('vac_hr') ? $attItem->get('vac_hr'): 'none';?>
	</div>
	</p>
	<br style="clear:both;"/>
</div>

<br style="clear:both;"/>

<h3>Description</h3>
<div id="ieidesc" data-propdesc="Description" data-updateaction="attend/updateDesc" data-property="description" data-bind="jeditable:description, jeditableOptions: {submit:'OK', cancel:'Cancel', onblur:'cancel'}">
<p>
<?php echo nl2br($attItem->get('description')); ?>
</p>
</div>


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

