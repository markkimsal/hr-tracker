require.config({
paths: {
    jquery: '../../components/jquery/dist/jquery', 
    moment: '../../components/moment/moment', 
    datatables: '../../components/DataTables/media/js/jquery.dataTables'
},
shim: {
      "datatables": ['jquery'],
      "moment": ['jquery']
     }
});
require( ['jquery', 'datatables', 'moment'], function($, dataTable, moment) {
(function($) {
 $(document).ready(function() {
	var burl = $('body').data('burl')|| '/';

	enableEdits();
	doLogUpdate('comments')

	$('#link-status, #link-comments, #link-all').click(function(e) {
		var self = $(this);
		var url = burl + self.data('source');
		$(self.attr('href') + ' ol')
			.load(url, function(){
			   self.tab('show');
			});
	});

	enableDataTables();
 });


function doLogUpdate(typ) {
	var burl = $('body').data('burl')|| '/';
	var pk = $('#details_obj_id').val();
	//if we're not on a page that has the ID, just stop
	if (pk == 0 || pk == undefined) {
		return;
	}
	$.ajax({
		url: burl + 'cportal/ticket/log/id='+pk+'/t='+typ,
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
			$("#ticket-"+typ+"-log").empty();
			$("#ticket-"+typ+"-log").html(xml);
		}
	});
	return true;
}

function enableEdits() {
	var burl = $('body').data('burl')|| '/';
	var pk = $('#details_obj_id').val();
	$('.details_edit .iedit_date').each(function(index) {
 		$(this).editable({
			type: 'date',
			url: burl + $(this).data('updateaction'),
			pk: pk,
			format: 'mm/dd/yyyy'
		});
	});

	$('.details_edit .iedit_text').each(function(index) {
 		$(this).editable({
			type: 'text',
			url: burl + $(this).data('updateaction'),
			pk: pk,
			format: 'mm/dd/yyyy'
		});
	});

	$('.details_edit .iedit_select').each(function(index) {
 		$(this).editable({
			type: 'select',
			url: burl + $(this).data('updateaction'),
			source: burl + $(this).data('sourceurl'),
			pk: pk,
			format: 'mm/dd/yyyy'
		});
	});

	$('.details_edit .iedit_area').each(function(index) {
		$(this).editable({
			type: 'textarea',
			url: burl + $(this).data('updateaction'),
			source: burl + $(this).data('sourceurl'),
			mode: 'inline',
			pk: pk
		});
	});
}


function enableDataTables() {
	var dtsettings = {};
	var burl = $('body').data('burl')|| '/';
	$('.dataTable').each(function(index) {
		dtsettings = {};

		var dateCols = null;
		var timeCols = null;
		if ($(this).data('date-cols')) {
			dateCols = new String($(this).data('date-cols')).split(',');
			for(var i=0; i < dateCols.length; i++) { dateCols[i] = parseInt(dateCols[i]); } 

			timeCols = new String($(this).data('time-cols')).split(',');
			for(var i=0; i < timeCols.length; i++) { timeCols[i] = parseInt(timeCols[i]); } 

			dtsettings.columnDefs = [
				{
					"render": function (data, type, row) {
						if (type == "sort" || type == 'type') {
							return data;
						}
						return moment(data).format("MMM Do YYYY");
					},
					"targets": dateCols
				},
				{
					"render": function (data, type, row) {
						if (type == "sort" || type == 'type') {
							return data;
						}
						return moment(data).fromNow();
					},
					"targets": timeCols
				}

			]
		}
		if ($(this).data('source')) {
			dtsettings.ajax = {
				"url": burl + $(this).data('source'),
				"dataSrc": "main"
			};
/*
			dtsettings.ajax = function (data, cb, settings) {
				$.ajax({
					"url": burl + $(this).data('source')
				}).done(function(data) {
					cb( {data:data.main} );
				});
			};
*/
		}
		$('.dataTable').dataTable(dtsettings);
	});
}
}(jQuery));
});
