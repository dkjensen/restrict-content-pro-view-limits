<?php


/**
 * Admin menu page for view restrictions
 * 
 * @return type
 */
function rcp_view_limits_admin_menu() {
	add_submenu_page( 'rcp-members', __( 'View Limits', 'rcp' ), __( 'View Limits', 'rcp' ), 'rcp_view_view_limits', 'rcp-view-limits', 'rcp_view_limits_admin_view_limits' );
}
add_action( 'admin_menu', 'rcp_view_limits_admin_menu', 20 );


/**
 * Admin screen for view restrictions against subscription levels
 * 
 * @return type
 */
function rcp_view_limits_admin_view_limits() {
	global $rcpcl;

	$page = admin_url( '/admin.php?page=rcp-view-limits' );
	?>
	<div class="wrap">
		<?php if( isset( $_GET['edit_restrictions'] ) ) :
			rcp_view_limits_admin_edit_restrictions();
		else : ?>
		<h2><?php _e( 'View Restrictions', 'rcp' ); ?></h2>
		<table class="wp-list-table widefat fixed posts rcp-view-limits">
			<thead>
				<tr>
					<th scope="col" class="rcp-cl-name-col column-primary"><?php _e('Name', 'rcp'); ?></th>
				</tr>
			</thead>
			<tbody id="the-list">
			<?php $levels = rcp_get_subscription_levels( 'all' ); ?>

			<?php
			if($levels) :
				$i = 1;
				foreach( $levels as $key => $level) : ?>
					<tr class="rcp-view-restriction rcp_row <?php if( rcp_is_odd( $i ) ) { echo 'alternate'; } ?>">
						<td class="rcp-cl-name-col column-primary has-row-actions" data-colname="<?php _e( 'Name', 'rcp' ); ?>">
							<strong><a href="<?php echo esc_url( add_query_arg( 'edit_subscription', $level->id, $page ) ); ?>"><?php echo stripslashes( $level->name ); ?></a></strong> 
							<?php if( current_user_can( 'rcp_manage_levels' ) ) : ?>
								<div class="row-actions">
									<span class="rcp-cl-id-col" data-colname="<?php _e( 'ID:', 'rcp' ); ?>"> <?php echo __( 'ID:', 'rcp' ) . ' ' . $level->id; ?> | </span>
									<a href="<?php echo esc_url( add_query_arg('edit_restrictions', $level->id, $page) ); ?>"><?php _e('Edit Restrictions', 'rcp'); ?></a>
								</div>
							<?php endif; ?>
							<button type="button" class="toggle-row"><span class="screen-reader-text"><?php _e( 'Show more details', 'rcp' ); ?></span></button>
						</td>
					</tr>
				<?php $i++;
				endforeach;
			else : ?>
				<tr><td colspan="1"><?php _e( 'No subscription levels added yet.', 'rcp' ); ?></td></tr>
			<?php endif; ?>
			</tbody>
		</table>
		<?php endif; ?>
	</div>
	<?php
}


/**
 * Admin screen for editing a subscription levels view restrictions
 * 
 * @return type
 */
function rcp_view_limits_admin_edit_restrictions() {
	$level = rcp_get_subscription_details( absint( urldecode( $_GET['edit_restrictions'] ) ) );

	$limits = get_metadata( 'level', absint( urldecode( $_GET['edit_restrictions'] ) ), 'view_limits', true );
	?>

	<div class="wrap">
		<h2>
			<?php _e( 'Edit View Restrictions:', 'rcp' ); echo ' ' . stripslashes( $level->name ); ?>
			<a href="<?php echo admin_url( '/admin.php?page=rcp-view-limits' ); ?>" class="add-new-h2">
				<?php _e( 'Cancel', 'rcp' ); ?>
			</a>
		</h2>
		<p><?php _e( 'Use the table below to specify restrictions for how many views each post type is allowed for the given subscription level.', 'rcp' ); ?></p>
		<form method="post">
			<table class="wp-list-table widefat fixed posts rcp-edit-restrictions">
				<thead>
					<tr>
						<th scope="row" class="rcp-cl-enable-col" width="100"><?php _e( 'Enable', 'rcp' ); ?></th>
						<th scope="col" class="rcp-cl-post-type-col column-primary" width="300"><?php _e('Post Type', 'rcp'); ?></th>
						<th scope="col" class="rcp-cl-view-limit-col" width="120"><?php _e('View Limit', 'rcp'); ?></th>
						<th scope="col" class="rcp-cl-interval-col"><?php _e('Interval', 'rcp'); ?></th>
					</tr>
				</thead>
				<tbody id="the-list">
				<?php
					$i = 1;
					foreach( get_post_types( array( 'public' => true ), 'objects' ) as $post_type ) : 

					$enabled        = isset( $limits[$post_type->name]['enabled'] ) ? $limits[$post_type->name]['enabled'] : '';
					$views   	    = isset( $limits[$post_type->name]['views'] ) ? $limits[$post_type->name]['views'] : 0;
					$interval_count = isset( $limits[$post_type->name]['interval_count'] ) ? $limits[$post_type->name]['interval_count'] : 0;
					$interval       = isset( $limits[$post_type->name]['interval'] ) ? $limits[$post_type->name]['interval'] : '';
				?>

				<tr class="<?php if( rcp_is_odd( $i ) ) { echo 'alternate'; } ?>">
					<td>
						<label><input type="checkbox" class="enable-view-limit" name="_view_limit[<?php print esc_attr( $post_type->name ); ?>][enabled]" value="yes" <?php checked( esc_attr( $enabled ), 'yes' ); ?> /> <?php _e( 'Enable', 'rcp' ); ?></label>
					</td>
					<td>
						<?php print $post_type->label; ?>
					</td>
					<td>
						<input type="text" style="width: 40px;" name="_view_limit[<?php print esc_attr( $post_type->name ); ?>][views]" value="<?php print absint( $views ); ?>"> <?php _e( 'views every', 'rcp' ); ?>
					</td>
					<td>
						<input type="text" style="width: 40px;" name="_view_limit[<?php print esc_attr( $post_type->name ); ?>][interval_count]" value="<?php print absint( $interval_count ); ?>">
						<select name="_view_limit[<?php print esc_attr( $post_type->name ); ?>][interval]">
							<option value="min" <?php selected( 'min', $interval ); ?>><?php _e( 'Minutes(s)', 'rcp' ); ?></option>
							<option value="hour" <?php selected( 'hour', $interval ); ?>><?php _e( 'Hour(s)', 'rcp' ); ?></option>
							<option value="day" <?php selected( 'day', $interval ); ?>><?php _e( 'Day(s)', 'rcp' ); ?></option>
							<option value="week" <?php selected( 'week', $interval ); ?>><?php _e( 'Week(s)', 'rcp' ); ?></option>
							<option value="month" <?php selected( 'month', $interval ); ?>><?php _e( 'Month(s)', 'rcp' ); ?></option>
							<option value="year" <?php selected( 'year', $interval ); ?>><?php _e( 'Year(s)', 'rcp' ); ?></option>
						</select>
					</td>
				</tr>

				<?php $i++; endforeach; ?>
				</tbody>
			</table>
			<input type="hidden" name="_rcpcl_action" value="save-restrictions" />
			<?php wp_nonce_field( 'save-restrictions' ); ?>
			<?php submit_button( __( 'Save View Restrictions', 'rcp' ) ); ?>
		</form>
	</div>

	<?php
}


/**
 * Form handler for saving view restrictions
 * 
 * @return type
 */
function rcp_view_limits_admin_save() {
	if( ! is_admin() || ! current_user_can( 'rcp_manage_view_limits' )  || ! isset( $_POST['_rcpcl_action'] ) || $_POST['_rcpcl_action'] !== 'save-restrictions' )
		return;

	if( check_admin_referer( 'save-restrictions' ) ) {
		$level = absint( urldecode( $_GET['edit_restrictions'] ) );

		$view_limits = $_REQUEST['_view_limit'];

		$levels = new RCP_Levels();

		$update = $levels->update_meta( $level, 'view_limits', $view_limits );

		add_action( 'admin_notices', 'rcp_view_limits_admin_save_updated' );
	}
}
add_action( 'admin_init', 'rcp_view_limits_admin_save' );


/**
 * Admin notice upon saving view restrictions
 * 
 * @return type
 */
function rcp_view_limits_admin_save_updated() {
	?>

	<div class="notice notice-success is-dismissible">
        <p><?php _e( 'View restrictions updated successfully.', 'rcp' ); ?></p>
    </div>

	<?php
}


function rcp_view_limits_admin_scripts() {
	wp_enqueue_script( 'rcp_cl_admin', RCP_CL_PLUGIN_URL . 'includes/js/admin-scripts.js', array( 'jquery' ), '1.0.0', true );
}
add_action( 'admin_enqueue_scripts', 'rcp_view_limits_admin_scripts' );