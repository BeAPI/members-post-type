jQuery(document).ready(function($) {
	$('#member-password').val('').keyup(mpt_check_pass_strength);
	$('#member-confirm_password').val('').keyup(mpt_check_pass_strength);
	$('#pass-strength-result').show();
	$('#member-password').prop('disabled', false);
	$('#member-confirm_password').prop('disabled', false);
	
	
	$('#member-password-generate').click(function(){
		var pwd_generate = $('#member-password-generate:checked').val();
		if( pwd_generate == 'on' ){
			$('#member-password').prop('disabled', true);
			$('#member-confirm_password').prop('disabled', true);
		}else{
			$('#member-password').prop('disabled', false);
			$('#member-confirm_password').prop('disabled', false);
		}
	});
	
	
	var member_social_id = $('#member-social_id'), connection_type = $('#member-connection_type');

	connection_type.on('change', mpt_check_connection_type);
	function mpt_check_connection_type() {
		if (connection_type.val() === 'default') {
			member_social_id.closest('tr').hide();
		} else {
			member_social_id.closest('tr').show();
		}
	}

	function mpt_check_pass_strength() {
		var pass1 = $('#member-password').val(), pass2 = $('#member-confirm_password').val(), strength;

		if ($('#member-username').size() > 0) {
			var user = $('#member-username').val();
		} else {
			var user = $('#member-email').val();
		}

		$('#pass-strength-result').removeClass('short bad good strong');
		if (!pass1) {
			$('#pass-strength-result').html(pwsL10n.empty);
			return;
		}

		strength = passwordStrength(pass1, user, pass2);

		switch (strength) {
			case 2:
				$('#pass-strength-result').addClass('bad').html(pwsL10n['bad']);
				break;
			case 3:
				$('#pass-strength-result').addClass('good').html(pwsL10n['good']);
				break;
			case 4:
				$('#pass-strength-result').addClass('strong').html(pwsL10n['strong']);
				break;
			case 5:
				$('#pass-strength-result').addClass('short').html(pwsL10n['mismatch']);
				break;
			default:
				$('#pass-strength-result').addClass('short').html(pwsL10n['short']);
		}
	}
	
	mpt_check_connection_type();
});
