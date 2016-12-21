<?php


final class RCP_Content_Limit {

	public $table_name          = '';

	public $guest_level         = 0;

	protected static $_instance = null;

	/**
	 * Late static binding
	 * 
	 * @return type
	 */
	public static function instance() {
		if( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}


	/**
	 * Initialize the plugin
	 */
	protected function __construct() {
		add_action( 'admin_init', array( $this, 'check_installed' ) );
		add_action( 'init', array( $this, 'setup' ) );

		add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );
	}


	public function admin_scripts() {
		wp_enqueue_script( 'rcp_cl_admin', RCP_CL_PLUGIN_URL . 'includes/js/admin-scripts.js', array( 'jquery' ), '1.0.0', true );
	}


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

	public function setup() {
		global $wpdb;

		$prefix = is_plugin_active_for_network( 'restrict-content-pro/restrict-content-pro.php' ) ? '' : $wpdb->prefix;

		$this->table_name  = apply_filters( 'rcp_cl_db_name', $prefix . 'rcp_limits' );
		$this->guest_level = absint( get_option( 'rcp_cl_guest_level' ) );

		new RCP_Content_Limit_Level();

		if( is_admin() ) {
			new RCP_Content_Limit_Admin_Screens();
		}
		
	}


	/**
	 * Install database table required to log users' post views
	 */
	public function install() {
		global $wpdb, $wp_roles;

		$db_version = get_option( 'rcp_cl_db_version' );
		$guest_level = get_option( 'rcp_cl_guest_level' );

		if( version_compare( $db_version, RCP_CL_DB_VERSION ) < 0 ) {
			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

			$charset_collate = $wpdb->get_charset_collate();

			$sql = "CREATE TABLE {$this->table_name} (
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
			$wp_roles->add_cap( 'administrator', 'rcp_view_view_restrictions' );
			$wp_roles->add_cap( 'administrator', 'rcp_manage_view_restrictions' );
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
		return function_exists( 'rcp_user_can_access' );
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


	/**
	 * Prevent cloning of an instance of the class via the clone operator
	 * 
	 * @return type
	 */
	private function __clone() {}


	/**
	 * Prevent unserializing of an instance of the class via the global function unserialize()
	 * 
	 * @return type
	 */
	private function __wakeup() {}

}