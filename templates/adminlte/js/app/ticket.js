(function($) {
 $(document).ready(function() {
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
			source: burl + 'emp/attend/listTypes',
			pk: pk,
			format: 'mm/dd/yyyy'
		});
	});

/*
     $('.iedit_area').each(function(index) {
         $(this).editable(
		     burl + $(this).data('updateaction'), {
             indicator : 'Saving...',
             tooltip   : 'Click to edit...'
     })});

     $('.iedit_select').each(function(index) {
         $(this).editable(
		     burl + $(this).data('updateaction'), {
             indicator : 'Saving...',
             tooltip   : 'Click to edit...'
     })});
*/
 });
}(jQuery));
