jQuery(document).ready(function($) {
	// Feature toggle
	$("input[name^='mpt-main[features][']").on("change", function(event) {
		event.preventDefault();
		toggle_features_metabox();
	});

	// Security toggle
	$("input[name='mpt-security[mode]']").on("change", function(event) {
		event.preventDefault();
		toggle_security_metabox();
	});

	// Toggles default execution when page is loaded
	toggle_features_metabox();
	toggle_security_metabox();
});

function toggle_features_metabox() {
	// Role manager
	if( jQuery("input[name='mpt-main[features][role-manager]']").prop("checked") ) {
		jQuery('#postbox-field-role-manager').show();
	} else {
		jQuery('#postbox-field-role-manager').hide();
	}
	
	// Content permissions
	if( jQuery("input[name='mpt-main[features][content-permissions]']").prop("checked") ) {
		jQuery('#postbox-field-content-permissions').show();
	} else {
		jQuery('#postbox-field-content-permissions').hide();
	}
	
	// Private website
	if( jQuery("input[name='mpt-main[features][private-website]']").prop("checked") ) {
		jQuery('#postbox-field-private-website').show();
	} else {
		jQuery('#postbox-field-private-website').hide();
	}
}

function toggle_security_metabox() {
	var current_value = jQuery("input[name='mpt-security[mode]']:checked").val();

	if (current_value === 'none') {
		jQuery('#postbox-field-auto-mode').hide();
		jQuery('#postbox-field-custom-mode').hide();
	} else if (current_value === 'auto') {
		jQuery('#postbox-field-auto-mode').show();
		jQuery('#postbox-field-custom-mode').hide();
	} else if (current_value === 'custom') {
		jQuery('#postbox-field-auto-mode').hide();
		jQuery('#postbox-field-custom-mode').show();
	}
}