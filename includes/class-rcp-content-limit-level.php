<?php


class RCP_Content_Limit_Level {


	public $guest_level = 0;

	public function __construct() {
		global $rcpcl;

		if( ! empty( $rcpcl->guest_level ) && ( is_admin() && isset( $_GET['page'] ) && $_GET['page'] != 'rcp-view-restrictions' ) ) {
			add_filter( 'rcp_get_levels', array( $this, 'get_levels_guest' ), 15 );
			add_filter( 'rcp_get_level', array( $this, 'get_level_guest' ), 15 );
		}
	}

	/**
	 * Exclude the guest subscription level
	 * 
	 * @param type $levels 
	 * @return type
	 */
	public function get_levels_guest( $levels ) {
		global $rcpcl;

		if( ! empty( $levels ) && is_array( $levels ) ) {
			foreach( $levels as $key => $level ) {
				if( $level->id == $rcpcl->guest_level ) {
					unset( $levels[$key] );
				}
			}
		}

		return $levels;
	}


	/**
	 * Exclude the guest subscription level
	 * 
	 * @param type $levels 
	 * @return type
	 */
	public function get_level_guest( $level ) {
		global $rcpcl;
		
		if( $level == $rcpcl->guest_level ) {
			return false;
		}

		return $level;
	}
}