<?php
// don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

echo MPT_Shortcode::get_messages();
?>

<p class="two-factor-prompt"><?php esc_html_e( 'A verification code has been sent to the email address associated with your account.', 'mpt' ); ?></p>
<form method="post">
    <div class="mpt-field">
        <label for="mpt-two-factor-code"><?php esc_html_e( 'Verification Code:', 'mpt' ); ?></label>
        <input id="mpt-two-factor-code" type="text" inputmode="numeric" name="mpttwofactor[code]" placeholder="12345678"/>
    </div>
    <div class="mpt-field">
        <input type="hidden" name="mpttwofactor[challenge_id]" value="<?php echo esc_attr( $member_data['challenge_id'] ); ?>"/>
        <input type="hidden" name="mpttwofactor[nonce]" value="<?php echo esc_attr( $member_data['nonce'] ); ?>"/>
        <input type="hidden" name="mpttwofactor[redirect_to]" value="<?php echo esc_attr( $member_data['redirect_to'] ); ?>"/>
        <input type="hidden" name="mpttwofactor[rememberme]" value="<?php echo esc_attr( $member_data['rememberme'] ); ?>"/>
        <input type="submit" value="<?php _e( 'Submit', 'mpt' ); ?>"/>
        <input type="submit" name="<?php echo esc_attr( 'mpt-two-factor-resend-code' ); ?>" value="<?php esc_attr_e( 'Resend Code', 'mpt' ); ?>"/>
    </div>
</form>
