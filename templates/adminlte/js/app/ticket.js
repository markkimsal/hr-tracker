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
 });


function doLogUpdate(typ) {
	var burl = $('body').data('burl')|| '/';
	var pk = $('#details_obj_id').val();
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

function loadLog() {
	var burl = $('body').data('burl')|| '/';
	$('.iedit_date').each(function(index) {
 		$(this).editable({
			type: 'date',
			url: burl + $(this).data('updateaction'),
			pk: pk,
			format: 'mm/dd/yyyy'
		});
	});
}

function enableEdits() {
	var burl = $('body').data('burl')|| '/';
	var pk = $('#details_obj_id').val();
	$('.iedit_date').each(function(index) {
 		$(this).editable({
			type: 'date',
			url: burl + $(this).data('updateaction'),
			pk: pk,
			format: 'mm/dd/yyyy'
		});
	});

	$('.iedit_text').each(function(index) {
 		$(this).editable({
			type: 'text',
			url: burl + $(this).data('updateaction'),
			pk: pk,
			format: 'mm/dd/yyyy'
		});
	});

	$('.iedit_select').each(function(index) {
 		$(this).editable({
			type: 'select',
			url: burl + $(this).data('updateaction'),
			source: burl + $(this).data('sourceurl'),
			pk: pk,
			format: 'mm/dd/yyyy'
		});
	});

	$('.iedit_area').each(function(index) {
		$(this).editable({
			type: 'textarea',
			url: burl + $(this).data('updateaction'),
			source: burl + $(this).data('sourceurl'),
			mode: 'inline',
			pk: pk
		});
	});
}
}(jQuery));
