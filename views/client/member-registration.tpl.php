<?php echo MPT_Shortcode::get_message(); ?> 
<form id="member-registration-form"  method="post" action="">
	<div class="row">
		<div class="col-label">
			<label for="user_nickname"><?php _e( 'Login', 'mpt' ) ; ?> *</label>
		</div>
		<div class="col-input">
			<input type="text" class="text" name="new_user[user_nickname]" id="user_nickname" value="<?php echo MPT_Shortcode_Registration::get_field_value( 'user_nickname' ); ?>" />
		</div>
	</div>
	<div class="row">
		<div class="col-label">
			<label for="last-name"><?php _e( 'Firstname', 'mpt' ) ; ?> *</label>
		</div>
		<div class="col-input">
			<input type="text" class="text" name="new_user[first_name]" id="first-name" value="<?php echo MPT_Shortcode_Registration::get_field_value( 'first_name' ); ?>" />
		</div>
	</div>
	<div class="row">
		<div class="col-label">
			<label for="name"><?php _e( 'Last name', 'mpt' ) ; ?> *</label>
		</div>
		<div class="col-input">
			<input type="text" class="text" name="new_user[last_name]" id="last-name" value="<?php echo MPT_Shortcode_Registration::get_field_value( 'last_name' ); ?>" />
		</div>
	</div>
	<div class="row">
		<div class="col-label">
			<label for="new_user[user_email]"><?php _e( 'Email', 'mpt' ) ; ?> *</label>
		</div>
		<div class="col-input">
			<input type="email" name="new_user[user_email]" id="new_user[user_email]" class="text" value="<?php echo MPT_Shortcode_Registration::get_field_value( 'user_email' ); ?>" />
		</div>
	</div>
	<div class="row">
		<div class="col-label">
			<label for="new_user[password]"><?php _e( 'Password', 'mpt' ) ; ?> *</label>
		</div>
		<div class="col-input">
			<input type="password" class="text" id="new_user[password]" name="new_user[password]" value="" />
		</div>
	</div>
	<div class="row">
		<div class="col-label two-lines">
			<label for="new_user[password_repeat]"><?php _e( 'Password', 'mpt' ) ; ?> * <?php _e( '(confirmation)', 'mpt' ) ; ?></label>
		</div>
		<div class="col-input">
			<input type="password" class="text" id="new_user[password_repeat]" name="new_user[password_repeat]" value="" />
		</div>
	</div>
	<div class="row">
		<div class="col-notice">
			<span>* <?php _e( 'Required fields', 'mpt' ) ; ?></span>
		</div>
		<div class="col-submit">
			<?php wp_nonce_field( 'creation-user' ); ?>
			<input type="submit" value="<?php _e( 'Submit', 'mpt' ) ; ?>" name="creation_user" class="submit-btn" />
		</div>
	</div>
</form>