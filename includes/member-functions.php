<?php


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