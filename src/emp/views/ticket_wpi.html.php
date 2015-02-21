<?php
$attItem = $this->stageItem;
if ($attItem === NULL || $attItem->dataItem->_isNew) {
	echo "This item has been deleted.";
	return;
}
$idate = explode(' ', $attItem->get('incident_date'));
$idate = explode('-', $idate[0]);
$fdate = $idate[1].'/'.$idate[2].'/'.$idate[0];

?>

<link rel="stylesheet" href="<?php m_turl();?>media/cserv-cpemp-screen.css" />

<script type="text/Javascript"><!--
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

		var cstore = new dojo.data.ItemFileReadStore({url: "<?php echo m_appurl('cpemp/attend/listCorrAtt', array('xhr'=>true));?>"});
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
			updateUrl: "<?php echo m_appurl('cpemp/attend/updateCorrAtt', array('xhr'=>true));?>"
		},
		"ieicorr");

		iec._origSave = iec.save;
		iec.save = saveIec;

        ieb = new dijit.InlineEditBox({
            editor: "dijit.form.DateTextBox",
			editorParams: {constraints: {datePattern: 'MM/dd/yyyy'}},
            autoSave: false,
			updateUrl: "<?php echo m_appurl('cpemp/attend/updateDate', array('xhr'=>true));?>"
        },
        "ieidate");

		ieb._origSave = ieb.save;
		ieb.save = saveIeb;

    });



	function saveIeb(_9) {
		if(this.disabled||!this.editing){
		return;
		}
		ieb.newval = ieb.wrapperWidget.editWidget.attr('value');
		ieb.newval = dojo.date.locale.format(ieb.newval, {datePattern: 'EEE MMM d y', timePattern: 'HH:mm:ss ZZZZ'});

		dojo.xhr('POST',{
			url: this.updateUrl,
			postData: "csrv_ticket_id="+dojo.byId('csrv_ticket_id').value+"&date="+ieb.newval,
			//handleAs:"text",
			handleAs:"json",
			load: function(data) {
				if (data.result == "good") {
					ieb._origSave(_9);
				} else {
					alert(data.result);
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
			url: "<?php echo m_appurl('cpemp/attend/updateField', array('xhr'=>true));?>",
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
</script>

<input type="hidden" name="csrv_ticket_id" id="csrv_ticket_id" value="<?php echo $this->getPrimaryKey(); ?>" />

<div class="_att_edit_block">
	<h3>Employee</h3>
	<p>
	<?php 
	$a = Metrodi_Container::getContainer();
	$a->tryFileLoading('emp/employee.php');
	$emp = new Emp_Employee( $attItem->get('emp_id') );

//	associate_loadFile('cpemp/lib/Cpemp_Employee_Model.php');
//	$emp = new Cpemp_Employee_Model($attItem->get('cpemp_id'));
	printf("%s, %s", $emp->getLastname(), $emp->getFirstname());
	?>
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
	<h3>Corrective Action</h3>
	<div id="ieicorr">
	<?php echo $attItem->getCorrectiveName();?>
	</div>
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
var_dump(
$attItem->get('owner_id')
);
exit();


$owner = Metrou_User::load($attItem->get('owner_id'));
if ($ower) {
	//failure to load means null object
	echo $owner->getDisplayName();
}
?>
</p>

<h3>Submitted On</h3>
<p>
<?php echo date('m/d/Y', $attItem->get('created_on'));?>
</p>

<h3>Description</h3>
<p>
<?php echo nl2br($attItem->get('description')); ?>
</p>

