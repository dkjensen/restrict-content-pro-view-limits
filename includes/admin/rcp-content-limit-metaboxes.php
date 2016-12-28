<?php


function rcp_cl_content_limit_metaboxes() {
	global $post;

	$disable_restriction = (string) get_post_meta( $post->ID, 'rcp_disable_view_restriction', true );
	?>

	<div id="rcp-metabox-field-content-limits" class="rcp-metabox-field">
		<p><strong><?php _e( 'Content view limits', 'rcp' ); ?></strong></p>
		<p>
			<label for="rcp-disable-content-limits">
				<input type="checkbox" name="rcp_disable_content_limits" id="rcp-disable-content-limits" value="1"<?php checked( true, $disable_restriction ); ?>/>
				<?php _e( 'Disable users subscription level view restrictions for this content.', 'rcp' ); ?>
			</label>
		</p>
	</div>

	<?php
}
add_action( 'rcp_metabox_fields_after', 'rcp_cl_content_limit_metaboxes' );


function rcp_cl_content_limit_metaboxes_save( $post_id ) {
	if( ! isset( $_POST['rcp_meta_box'] ) || ! wp_verify_nonce( $_POST['rcp_meta_box'], 'metabox.php' ) ) {
		return;
	}

	if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if( 'page' == $_POST['post_type'] ) {
		if( ! current_user_can( 'edit_page', $post_id ) ) {
			return;
		}
	} elseif( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$disable_restriction = isset( $_POST['rcp_disable_content_limits'] );

	update_post_meta( $post_id, 'rcp_disable_view_restriction', $disable_restriction );
}
add_action( 'save_post', 'rcp_cl_content_limit_metaboxes_save' );