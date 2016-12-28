<?php
/**
 *  Plugin Name: Restrict Content Pro Content Limit
 *  Plugin URL: https://restrictcontentpro.com
 *  Description: Limits the number of posts users can view freely before being prompted to subscribe
 *  Version: 1.0.0
 *  Author: David Jensen
 *  Author URI: http://dkjensen.com
 *  Text Domain: rcp
 *  Domain Path: languages
**/


if( ! defined( 'RCP_CL_PLUGIN_DIR' ) ) {
	define( 'RCP_CL_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
}
if( ! defined( 'RCP_CL_PLUGIN_URL' ) ) {
	define( 'RCP_CL_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
}

if( ! defined( 'RCP_CL_DB_VERSION' ) ) {
	define( 'RCP_CL_DB_VERSION', '1.0.6' );
}

if ( ! function_exists( 'is_plugin_active_for_network' ) ) {
	require_once ABSPATH . '/wp-admin/includes/plugin.php';
}

function rcp_get_content_limits_db_name() {
	global $wpdb;

	$prefix = is_plugin_active_for_network( 'restrict-content-pro/restrict-content-pro.php' ) ? '' : $wpdb->prefix;

	return apply_filters( 'rcp_cl_db_name', $prefix . 'rcp_limits' );
}


/**
 * Activation hook used for checking if RCP is activated
 */
register_activation_hook( __FILE__, array( 'RCP_Content_Limit', 'plugin_activation' ) );


include RCP_CL_PLUGIN_DIR . 'includes/class-rcp-content-limit.php';
include RCP_CL_PLUGIN_DIR . 'includes/class-rcp-cl-user.php';
include RCP_CL_PLUGIN_DIR . 'includes/admin/rcp-content-limit-admin-screens.php';
include RCP_CL_PLUGIN_DIR . 'includes/admin/rcp-content-limit-metaboxes.php';
include RCP_CL_PLUGIN_DIR . 'includes/rcp-content-limit-filters.php';
include RCP_CL_PLUGIN_DIR . 'includes/rcp-content-limit-functions.php';


$rcpcl = new RCP_Content_Limit();

if( rcp_get_guest_level() && ( is_admin() && isset( $_GET['page'] ) && $_GET['page'] != 'rcp-view-restrictions' ) ) {
	add_filter( 'rcp_get_levels', 'rcp_filter_get_levels_guest', 15 );
	add_filter( 'rcp_get_level', 'rcp_filter_get_level_guest', 15 );
}

add_action( 'admin_init', array( $rcpcl, 'check_installed' ) );










