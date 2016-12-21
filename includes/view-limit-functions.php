<?php

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

/*
function user_get_post_type_viewed_count( $post_type = 'post' ) {
	global $wpdb;

	$post_type  = $post_type;

	$table_name = apply_filters( 'rcp_cl_db_name', $prefix . 'rcp_limits' );

	$user_ip    = get_current_user_ip();

	return $wpdb->get_var( $wpdb->prepare( "
			SELECT limit_viewed 
			FROM %s 
			WHERE user_ip = '%s' 
			AND post_type = %s
			ORDER BY limit_created DESC
			LIMIT 1
		",
		$table_name,
		$user_ip,
		$post_type ) );
}
*/

if( ! function_exists( 'get_view_limit' ) ) {

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

}

