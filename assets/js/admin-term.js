jQuery(document).ready(function($) {
	// Remove event click
	$("#term-capabilities").on("click", "a.remove-caps-field", function(event){
		event.preventDefault();
		$(this).parent().remove();
	});

	// Add event click
	$('#add-caps-field').click( function(event){
		event.preventDefault();

		$("#term-capabilities").append('<p>' 
			+ '<input type="text" name="caps[]" class="caps-field widefat regular-text" value="" />'
			+ '<a href="#" class="remove-caps-field delete hide-if-no-js"><span>' + mpt_term_l10n.remove_link_label + '</span></a>'
			+ '</p>'
		);
	});
});