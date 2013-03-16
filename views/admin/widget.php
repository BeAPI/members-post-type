<!-- This file is used to markup the administration form of the widget. -->
<p>
	<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'mpt' ) ?></label>
	<br/>
	<input type="text" class="widefat" value="<?php echo esc_attr( $instance['title'] ); ?>" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" />
</p>

<p>
	<input type="checkbox" value="1" <?php checked($instance['is_lost_link'], 1); ?> id="<?php echo $this->get_field_id( 'is_lost_link' ); ?>" name="<?php echo $this->get_field_name( 'is_lost_link' ); ?>" />
	<label for="<?php echo $this->get_field_id( 'is_lost_link' ); ?>"><?php _e( 'Display lost password link ?', 'mpt' ) ?></label>
</p>

<p>
	<input type="checkbox" value="1" <?php checked($instance['is_register_link'], 1); ?> id="<?php echo $this->get_field_id( 'is_register_link' ); ?>" name="<?php echo $this->get_field_name( 'is_register_link' ); ?>" />
	<label for="<?php echo $this->get_field_id( 'is_register_link' ); ?>"><?php _e( 'Display register link ?', 'mpt' ) ?></label>
</p>

<p>
	<label><?php _e( 'Mode :', 'mpt' ) ?></label>
	<br />
	<label for="<?php echo $this->get_field_id( 'mode' ); ?>">
		<input type="radio" value="html" <?php checked($instance['mode'], 'html'); ?> id="<?php echo $this->get_field_id( 'mode' ); ?>" name="<?php echo $this->get_field_name( 'mode' ); ?>" />
		<?php _e( 'HTML', 'mpt' ) ?>
	</label>

	<label for="<?php echo $this->get_field_id( 'mode' ); ?>">
		<input type="radio" value="ajax" <?php checked($instance['mode'], 'ajax'); ?> id="<?php echo $this->get_field_id( 'mode' ); ?>" name="<?php echo $this->get_field_name( 'mode' ); ?>" />
		<?php _e( 'AJAX', 'mpt' ) ?>
	</label>
</p>