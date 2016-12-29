<?php


/**
 * Exclude the guest subscription level
 * 
 * @param array $levels 
 * @return levels
 */
function rcp_filter_get_levels_guest( $levels ) {
	global $rcpcl;

	if( ! empty( $levels ) && is_array( $levels ) ) {
		foreach( $levels as $key => $level ) {
			if( $level->id == rcp_get_guest_level() ) {
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
 * @return mixed 
 */
function rcp_filter_get_level_guest( $level ) {
	global $rcpcl;

	if( is_object( $level ) && $level->id == rcp_get_guest_level() ) {
		return false;
	}

	return $level;
}


/**
 * Restrict post content if users viewing limit is reached
 * 
 * @param string $content 
 * @return string
 */
function rcp_filter_content_restriction( $content ) {
	global $rcp_options;

	$post_type         = get_post_type_object( get_post_type() );
	$registration_page = isset( $rcp_options['registration_page'] ) ? get_permalink( $rcp_options['registration_page'] ) : home_url();

	if( rcp_user_is_restricted() ) {
		$content  = "<p>" . "\n";
		$content .= "<span>" . sprintf( __( 'You have reached your limit of free %s. ', 'rcp' ), strtolower( $post_type->labels->name ) ) . "</span>";
		$content .= "<a href=" . $registration_page . " class=\"button subscribe-more\">" . __( 'Subscribe Today', 'rcp' ) . "</a>" . "\n";
		$content .= "</p>" . "\n";

		return rcp_format_teaser( apply_filters( 'rcp_cl_limit_reached_text', $content, $post_type, $registration_page ) );
	}

	return $content;
}
