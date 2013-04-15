jQuery(document).ready(function($) {
	// Remove event click
	$("input[name='mpt-security[mode]']").on("change", function(event){
		event.preventDefault();
		toggle_security_metabox();
	});
	
	toggle_security_metabox();
});

function toggle_security_metabox() {
    var current_value = jQuery("input[name='mpt-security[mode]']:checked").val();
    
    if ( current_value === 'none' ) {
	jQuery('#postbox-field-auto-mode').hide();
	jQuery('#postbox-field-custom-mode').hide();
    } else if( current_value === 'auto' ) {
	jQuery('#postbox-field-auto-mode').show();
	jQuery('#postbox-field-custom-mode').hide();
    } else if( current_value === 'custom' ) {
	jQuery('#postbox-field-auto-mode').hide();
	jQuery('#postbox-field-custom-mode').show();
   }
}