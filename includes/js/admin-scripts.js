(function($) {

	$('input[type=checkbox].enable-view-limit').on('change', function(e) {
		var $parent = $(this).closest('tr');
		
		if( ! $(this).is(':checked' ) ) {
			$parent.addClass('disabled');
			$parent.find('input[type=text], select' ).attr('disabled', 'disabled' );
		}else {
			$parent.removeClass('disabled');
			$parent.find('input[type=text], select' ).removeAttr('disabled' );
		}

	}).change();

})(jQuery);