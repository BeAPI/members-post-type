<tr class="form-field">
	<th scope="row" valign="top"><label for="capabilities"><?php _ex('Capabilities', 'Taxonomy Capabilities', 'mpt'); ?></label></th>
	<td>
		<input type="hidden" name="mpt-capabilities" value="1" />

		<div id="term-capabilities">
			<?php foreach( $term->capabilities as $capability ) : ?>
				<p>
					<input type="text" name="caps[]" class="caps-field widefat regular-text" value="<?php echo esc_attr($capability); ?>" />
					<a href="#" class="remove-caps-field delete hide-if-no-js"><span><?php _e('Remove capability', 'mpt'); ?></span></a>
				</p>
			<?php endforeach; ?>
		</div>

		<input type="text" name="caps[]" class="widefat regular-text hide-if-js" value="" />
		<a href="#" id="add-caps-field" class="button hide-if-no-js"><?php _e('Add new capability', 'mpt'); ?></a>
		<br />
		<span class="description hide-if-js"><?php _e('Delete content of fields to remove them.', 'mpt'); ?></span>
		<span class="description"><?php _e('Each role is allowed to perform a set of tasks called Capabilities.', 'mpt'); ?></span>
	</td>
</tr>

