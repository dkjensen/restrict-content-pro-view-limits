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



/**
 * Activation hook used for checking if RCP is activated
 */
register_activation_hook( __FILE__, array( 'RCP_Content_Limit', 'plugin_activation' ) );





include RCP_CL_PLUGIN_DIR . 'includes/class-rcp-content-limit.php';

$rcpcl = RCP_Content_Limit::instance();

if( is_admin() ) {
	include RCP_CL_PLUGIN_DIR . 'includes/admin/class-rcp-content-limit-admin-screens.php';
	include RCP_CL_PLUGIN_DIR . 'includes/admin/class-rcp-content-limit-metaboxes.php';
}else {
	include RCP_CL_PLUGIN_DIR . 'includes/class-rcp-content-limit-client.php';
	include RCP_CL_PLUGIN_DIR . 'includes/view-limit-functions.php';
	include RCP_CL_PLUGIN_DIR . 'includes/member-functions.php';
}



include RCP_CL_PLUGIN_DIR . 'includes/class-rcp-content-limit-level.php';



include RCP_CL_PLUGIN_DIR . 'includes/content-filters.php';




