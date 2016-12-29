<?php


class RCP_View_Limits {


	/**
	 * Check if we have installed RCP Content Limit before
	 * If not setup plugin and install tables
	 */
	public function check_installed() {
		$db_version = get_option( 'rcl_cl_db_version' );

		if( ! get_option( 'rcp_cl_installed' ) || version_compare( $db_version, RCP_CL_DB_VERSION ) < 0 ) {
			$this->install();
		}
	}


	/**
	 * Install database table required to log users' post views
	 */
	public function install() {
		global $wpdb, $wp_roles;

		$table = rcp_get_view_limits_db_name();

		$db_version = get_option( 'rcp_cl_db_version' );
		$guest_level = rcp_get_guest_level();

		if( version_compare( $db_version, RCP_CL_DB_VERSION ) < 0 ) {
			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

			$charset_collate = $wpdb->get_charset_collate();

			$sql = "CREATE TABLE {$table} (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				user_id bigint(20) NOT NULL DEFAULT 0,
				user_ip varchar(255) DEFAULT NULL,
				post_type varchar(255) DEFAULT NULL,
				post_ids longtext NOT NULL,
				limit_start int(11) NOT NULL DEFAULT 0,
				limit_viewed bigint(20) NOT NULL DEFAULT 0,
				last_viewed bigint(20) NOT NULL DEFAULT 0,
				PRIMARY KEY id (id)
				) $charset_collate";

			@dbDelta( $sql );

			update_option( 'rcp_cl_db_version', RCP_CL_DB_VERSION );
		}

		if( class_exists( 'WP_Roles' ) )
			if( ! isset( $wp_roles ) )
				$wp_roles = new WP_Roles();

		if( is_object( $wp_roles ) ) {
			$wp_roles->add_cap( 'administrator', 'rcp_view_view_limits' );
			$wp_roles->add_cap( 'administrator', 'rcp_manage_view_limits' );
		}

		if( empty( $guest_level ) ) {
			$levels = new RCP_Levels();
			$guest_level = $levels->insert( array( 'name' => 'Guest', 'description' => __( 'Non-logged in users', 'rcp' ), 'status' => 'inactive' ) );
			
			if( $guest_level ) {
				update_option( 'rcp_cl_guest_level', absint( $guest_level ) );
			}
		}

		update_option( 'rcp_cl_installed', 1 );
	}


	/**
	 * Returns whether or not RCP is active
	 */
	public static function rcp_active() {
		return is_plugin_active( 'restrict-content-pro/restrict-content-pro.php' );
	}


	/**
	 * Check if RCP is active or not before activating this plugin
	 */
	public static function plugin_activation() {
		if( ! self::rcp_active() ) {
			deactivate_plugins( plugin_basename( __FILE__ ) );
			wp_die( __( 'Restrict Content Pro must be active in order to activate this plugin.', 'rcp' ) );
		}
	}
}