<?php
/**
 * Basic utility functions
 * 
 * @package restrict-content-pro-view-limits
 */


if( ! function_exists( 'get_current_user_ip' ) ) {

function get_current_user_ip() {
	$ipaddress = '';
    if( getenv( 'HTTP_CLIENT_IP' ) ) 
        $ipaddress = getenv('HTTP_CLIENT_IP');
    elseif( getenv( 'HTTP_X_FORWARDED_FOR') )
        $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
    elseif( getenv( 'HTTP_X_FORWARDED') )
        $ipaddress = getenv('HTTP_X_FORWARDED');
    elseif( getenv( 'HTTP_FORWARDED_FOR') )
        $ipaddress = getenv('HTTP_FORWARDED_FOR');
    elseif( getenv( 'HTTP_FORWARDED') )
       $ipaddress = getenv('HTTP_FORWARDED');
    elseif( getenv( 'REMOTE_ADDR') )
        $ipaddress = getenv('REMOTE_ADDR');
    else
        $ipaddress = '';

	return $ipaddress;
}

}

function rcp_get_level_limits( $subscription_id = 0, $post_type = false ) {

	$limits = get_metadata( 'level', absint( $subscription_id ), 'view_limits', true );

	if( $post_type !== false ) {
		if( isset( $limits[$post_type] ) ) {
			$limits = $limits[$post_type];
		}else {
			return false;
		}
	}

	return (array) $limits;

}


function rcp_get_guest_level() {
	return absint( get_option( 'rcp_cl_guest_level' ) );
}


function rcp_user_views_remaining( $user_id = 0, $post_type = '' ) {
	if( empty( $user_id ) && is_user_logged_in() ) {
		$user_id = get_current_user_id();
	}

	$user = new RCP_CL_User( $user_id );

	if( empty( $post_type ) ) {
		$obj = get_queried_object();

		if( ! $obj ) return false;

		$post_type = $obj->post_type;
	}

	return $user->get_views_remaining( $post_type );
}


function rcp_user_views_allowed( $user_id = 0, $post_type = '' ) {
	if( empty( $user_id ) && is_user_logged_in() ) {
		$user_id = get_current_user_id();
	}

	$user = new RCP_CL_User( $user_id );

	if( empty( $post_type ) ) {
		$obj = get_queried_object();

		if( ! $obj )
			return false;

		$post_type = $obj->post_type;
	}

	return $user->get_views_allowed( $post_type );
}


function rcp_user_views_remaining_formatted( $user_id = 0, $post_type = '' ) {
	if( empty( $user_id ) && is_user_logged_in() ) {
		$user_id = get_current_user_id();
	}

	if( empty( $post_type ) ) {
		$obj = get_queried_object();

		if( ! $obj ) return false;

		$post_type = $obj->post_type;
	}

	$remaining = rcp_user_views_remaining( $user_id, $post_type );
	$allowed   = rcp_user_views_allowed( $user_id, $post_type );

	if( $allowed === -1 ) {
		$return = __( 'Unlimited', 'rcp' );
	}else {
		$return = $remaining . '/' . $allowed;
	}

	return apply_filters( 'rcp_cl_user_views_remaining_formatted', $return, $remaining, $allowed );
}


function rcp_user_has_viewed( $user_id = 0, $post_id = 0 ) {
	if( empty( $user_id ) && is_user_logged_in() ) {
		$user_id = get_current_user_id();
	}

	if( empty( $post_id ) ) {
		$post_id = get_queried_object_id();
	}

	$user = new RCP_CL_User( $user_id );

	if( $user->has_viewed( $post_id ) )
		return true;

	return false;
}


function rcp_user_is_restricted( $user_id = 0 ) {
	global $rcp_options;

	if( empty( $user_id ) && is_user_logged_in() ) {
		$user_id = get_current_user_id();
	}

	$obj = get_queried_object();

	if( ! $obj ) return false;

	$post_type = $obj->post_type;

	if( ! rcp_user_can_access( $user_id, get_queried_object_id() ) )
		return false;

    $user = new RCP_CL_User( $user_id );

	if( in_array( $obj->ID, array(
			$rcp_options['registration_page'],
			$rcp_options['redirect'],
			$rcp_options['account_page'],
			$rcp_options['edit_profile'],
			$rcp_options['update_card']
		) ) ) {
		return false;
	}

	if( $user->get_views_remaining( $post_type ) === -1 )
        return false;
        
    if( user_can( $user_id, 'publish_posts' ) )
        return false;

	return true;
}


function rcp_view_restrictions_enabled( $post_id = 0 ) {
	if( empty( $post_id ) ) {
		$post_id = get_queried_object_id();
	}

	$disabled = (bool) get_post_meta( $post_id, 'rcp_disable_view_restriction', true );

	if( $disabled )
		return false;

	return true;
}


function rcp_user_record_view() {
	if( rcp_view_restrictions_enabled() ) {
		if( rcp_user_is_restricted() ) {

			$user = new RCP_CL_User( get_current_user_id() );
			$user->record_view();

			if( ! rcp_user_has_viewed() && rcp_user_views_remaining() === 0 ) {
				add_filter( 'the_content', 'rcp_filter_content_restriction', 110 );
			}
		}
	}
}
add_action( 'wp', 'rcp_user_record_view', -10 );