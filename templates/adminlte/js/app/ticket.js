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
/*
         $(this).editable(
		     burl + $(this).data('updateaction'), {
             type: 'datepicker',
             indicator : 'Saving...',
             tooltip   : 'Click to edit...'
     	})
*/
	});

/*
     $('.iedit_text').each(function(index) {
         $(this).editable(
		     burl + $(this).data('updateaction'), {
             indicator : 'Saving...',
             tooltip   : 'Click to edit...'
     })});

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
