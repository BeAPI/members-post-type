jQuery(document).ready(function($) {
    $('#member-password').val('').keyup(mpt_check_pass_strength);
    $('#member-confirm_password').val('').keyup(mpt_check_pass_strength);
    $('#pass-strength-result').show();

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
});
