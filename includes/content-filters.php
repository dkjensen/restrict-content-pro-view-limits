<?php


function rcp_filter_content_restriction( $content ) {
	global $rcp_options;

	$post_type         = get_post_type_object( get_post_type() );
	$registration_page = isset( $rcp_options['registration_page'] ) ? get_permalink( $rcp_options['registration_page'] ) : home_url();

	if( ! rcp_user_can_view() ) {
		$content  = "<p>" . "\n";
		$content .= "<span>" . sprintf( __( 'You have reached your limit of free %s. ', 'rcp' ), strtolower( $post_type->labels->name ) ) . "</span>";
		$content .= "<a href=" . $registration_page . " class=\"button subscribe-more\">" . __( 'Subscribe Today', 'rcp' ) . "</a>" . "\n";
		$content .= "</p>" . "\n";

		return rcp_format_teaser( apply_filters( 'rcp_cl_limit_reached_text', $content, $post_type, $registration_page ) );
	}

	return $content;
}
