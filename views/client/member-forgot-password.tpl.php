<?php echo MPT_Shortcode::get_messages(); ?> 
<form id="member-forgot-password"  method="post" action="">
	<div class="row">
		<div class="col-label">
			<label for="mpt_user_email"><?php _e( 'Your email', 'mpt' ) ; ?></label>
		</div>
		<div class="col-input">
			<input type="text" class="text" name="user_login" id="mpt_user_email" value="" />
		</div>
	</div>
	<div class="row">
		<div class="col-submit">
			<?php wp_nonce_field( 'forgot-password' ); ?>
			<input type="hidden" name="forgot_password" value="1" />
			<input type="submit" value="<?php _e( 'Submit', 'mpt' ) ; ?>" class="submit-btn" />
		</div>
	</div>
</form>