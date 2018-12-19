<?php
/**
 *  Plugin Name: Restrict Content Pro - View Limits
 *  Plugin URL: https://restrictcontentpro.com
 *  Description: Limits the number of posts users can view freely before being prompted to subscribe
 *  Version: 1.0.0
 *  Author: David Jensen
 *  Author URI: http://dkjensen.com
 *  Text Domain: rcp
 *  Domain Path: languages
 * 
 * @package restrict-content-pro-view-limits
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

function rcp_get_view_limits_db_name() {
	global $wpdb;

	$prefix = is_plugin_active_for_network( 'restrict-content-pro/restrict-content-pro.php' ) ? '' : $wpdb->prefix;

	return apply_filters( 'rcp_cl_db_name', $prefix . 'rcp_limits' );
}


include RCP_CL_PLUGIN_DIR . 'includes/class-rcp-view-limits.php';


/**
 * Activation hook used for checking if RCP is activated
 */
register_activation_hook( __FILE__, array( 'RCP_View_Limits', 'plugin_activation' ) );


/**
 * Let's make sure RCP is active again...
 */
if( ! RCP_View_Limits::rcp_active() ) return;


include RCP_CL_PLUGIN_DIR . 'includes/class-rcp-cl-user.php';
include RCP_CL_PLUGIN_DIR . 'includes/admin/rcp-view-limits-admin-screens.php';
include RCP_CL_PLUGIN_DIR . 'includes/admin/rcp-view-limits-metaboxes.php';
include RCP_CL_PLUGIN_DIR . 'includes/rcp-view-limits-filters.php';
include RCP_CL_PLUGIN_DIR . 'includes/rcp-view-limits-functions.php';



$rcpcl = new RCP_View_Limits();

if( rcp_get_guest_level() && ( is_admin() && isset( $_GET['page'] ) && $_GET['page'] != 'rcp-view-limits' ) ) {
	add_filter( 'rcp_get_levels', 'rcp_filter_get_levels_guest', 15 );
	add_filter( 'rcp_get_level', 'rcp_filter_get_level_guest', 15 );
}

add_action( 'admin_init', array( $rcpcl, 'check_installed' ) );










