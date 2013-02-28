<?php echo MPT_Shortcode::get_message(); ?> 
<form id="member-login-form"  method="post" action="">
	<div class="row">
		<div class="col-label">
			<label for="mpt_user_login"><?php _e( 'Login', 'mpt' ) ; ?></label>
		</div>
		<div class="col-input">
			<input type="text" class="text" name="mpt_user_login" id="mpt_user_login" value="" />
		</div>
	</div>
	<div class="row">
		<div class="col-label">
			<label for="mpt_user_password"><?php _e( 'Password', 'mpt' ) ; ?></label>
		</div>
		<div class="col-input">
			<input type="password" class="text" id="mpt_user_password" name="mpt_user_password" value="" />
		</div>
	</div>
	<div class="row">
		<div class="col-submit">
			<?php wp_nonce_field( 'log-user' ); ?>
			<input type="submit" value="<?php _e( 'Submit', 'mpt' ) ; ?>" name="log_user" class="submit-btn" />
		</div>
	</div>
	<p><a href=""><?php _e( 'Forgot password ?', 'mpt' ) ; ?></a></p>
</form>