<?php echo MPT_Shortcode::get_messages(); ?> 
<form id="member-reset-password"  method="post" action="">
	<div class="row">
		<div class="col-label">
			<label for="mpt_user_old_password"><?php _e( 'Your old password', 'mpt' ) ; ?> *</label>
		</div>
		<div class="col-input">
			<input type="password" class="text" id="mpt_user_old_password" name="mpt_user_old_password" value="" />
		</div>
	</div>
	<div class="row">
		<div class="col-label">
			<label for="mpt_user_password"><?php _e( 'Your new password', 'mpt' ) ; ?> *</label>
		</div>
		<div class="col-input">
			<input type="password" class="text" id="mpt_user_password" name="mpt_user_password" value="" />
		</div>
	</div>
	<div class="row">
		<div class="col-label">
			<label for="mpt_user_password_confirm"><?php _e( 'Your new password', 'mpt' ) ; ?> * <?php _e( '(Confirm)', 'mpt' ) ; ?></label>
		</div>
		<div class="col-input">
			<input type="password" class="text" id="mpt_user_password_confirm" name="mpt_user_password_confirm" value="" />
		</div>
	</div>
	<div class="row">
		<div class="col-submit">
			<?php wp_nonce_field( 'reset-password' ); ?>
			<input type="submit" value="<?php _e( 'Submit', 'mpt' ) ; ?>" name="reset_password" class="submit-btn" />
		</div>
	</div>
</form>