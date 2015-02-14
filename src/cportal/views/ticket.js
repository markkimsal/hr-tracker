	var TicketModel = {
		typeName: ko.observable(),
		incidentDate: ko.observable(),
		incidentPoints: ko.observable(),
		corrAction: ko.observable(),
		vacHours: ko.observable(),
		approved: ko.observable(),
		description: ko.observable(),
		typeList: {},

		showAction: function(item, evt) {
			var divname = $(evt.target).attr('data-target');
			document.getElementById('addanote').style.display='none';
			document.getElementById('finalizenote').style.display='none';
			document.getElementById('changestatus').style.display='none';
			document.getElementById(divname).style.display='block';
			return false;
		},

		saveAttr: function(attr, newvalue, accessor, actName, propDesc) {
			var thiz = this;
			$.ajax({
				url: baseUrl + 'cpemp/'+ actName,
				data: {'f': attr, 'fd':propDesc, 'newvalue': newvalue, 'csrv_ticket_id': $('#csrv_ticket_id').val()},
				type: 'POST',
				dataType: 'json',
				timeout: 30000,
				error: function(xml, desc){

					alert('Error saving property: ' + attr);
					event.stopPropagation(); 
					event = null;
					return false;
				},
				success: function(json){
					var currentVal = accessor()();
					if (thiz.typeList[newvalue])
					accessor()(thiz.typeList[newvalue]);
					else
					accessor()(newvalue);
				}
			});
			return true;

		}
	}

$(function () {

ko.bindingHandlers.jeditable = {
     init: function(element, valueAccessor, allBindingsAccessor) {
         // get the options that were passed in
         var options = allBindingsAccessor().jeditableOptions || {};
          
         // "submit" should be the default onblur action like regular ko controls
         if (!options.onblur) {
          options.onblur = 'submit';
         }
          
/*
		if (options.type == 'select') {
			options.callback = function (value, settings) { 
				  $(this).html(settings.data[value]);
			 };
		}
*/
         // set the value on submit and pass the editable the options
         $(element).editable(function(value, params) {
//          valueAccessor()(value);
		  var ctx = ko.contextFor(element);
          ctx.$root.saveAttr($(this).data('property'), value, valueAccessor, $(this).data('updateaction'), $(this).data('propdesc'));
//		  return ko.unwrap(valueAccessor());
         }, options);
 
         //handle disposal (if KO removes by the template binding)
         ko.utils.domNodeDisposal.addDisposeCallback(element, function() {
             $(element).editable("destroy");
         });
 
     },
      
	//update the control when the view model changes
	update: function(element, valueAccessor) {
		var value = ko.utils.unwrapObservable(valueAccessor());
		if (!$.trim(value)) {
			$(element).html('Click to edit...');
		} else {
			$(element).html(value);
		}
	}
 };



/*
	$("#bootbox-status").on('click', function() {
		bootbox.confirm("Are you sure?", function(result) {
			if(result) {
				//
			}
		});
	});
*/

	$( "#link-addanote" ).on('click', function(e) {
		e.preventDefault();

		$( "#dialog-addanote" ).removeClass('hide').dialog({
			resizable: false,
			modal: true,
			title: "<div class='widget-header'><h4 class='smaller'><i class='icon-warning-sign red'></i> Empty the recycle bin?</h4></div>",
			title_html: true,
			buttons: [
				{
					html: "<i class='icon-trash bigger-110'></i>&nbsp; Delete all items",
					"class" : "btn btn-danger btn-xs",
					click: function() {
						$( this ).dialog( "close" );
					}
				}
				,
				{
					html: "<i class='icon-remove bigger-110'></i>&nbsp; Cancel",
					"class" : "btn btn-xs",
					click: function() {
						$( this ).dialog( "close" );
					}
				}
			]
		});
	});

});
