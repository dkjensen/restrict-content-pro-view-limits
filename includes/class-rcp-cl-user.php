<?php


class RCP_CL_User extends WP_User {


	/**
	 * Returns the users subscription level or the hidden
	 * guest level if not logged in
	 * 
	 * @return integer
	 */
	public function get_user_level() {
		global $rcpcl;

		$user_level = get_user_meta( $this->ID, 'rcp_subscription_level', true );

		if( empty( $user_level ) ) {
			$user_level = rcp_get_guest_level();
		}

		return absint( $user_level );
	}
	

	/**
	 * Returns if the post type has view restrictions enabled
	 * for the users subscription level
	 * 
	 * @return boolean
	 */
	public function view_restrictions_enabled( $post_type = 'post' ) {
		$limits  = rcp_get_level_limits( $this->get_user_level(), $post_type );
		$enabled = false;

		if( isset( $limits ) ) {
			$enabled = (bool) ! empty( $limits['enabled'] ) && $limits['enabled'] === 'yes' ? true : false;
		}

		return $enabled;
	}


	/**
	 * Get the number of views a user has for a given post type within
	 * the set timeframe
	 * 
	 * @param string $post_type 
	 * @return integer
	 */
	public function get_views_count( $post_type = 'post' ) {
		global $wpdb, $rcpcl;

		$table = rcp_get_view_limits_db_name();

		$viewed = $wpdb->get_var(
			$wpdb->prepare( "
				SELECT limit_viewed FROM {$table} WHERE 
					`user_id`		  = %d AND 
					`user_ip`		  = %s AND 
					`post_type`       = %s AND
					`limit_start`    >= %d 
				ORDER BY `limit_start` DESC
				LIMIT 1
			;",
				absint( $this->ID ),
				sanitize_text_field( get_current_user_ip() ),
				sanitize_text_field( $post_type ),
				absint( $this->views_timeframe( $post_type ) )
			) );

		if( $viewed ) {
			return absint( $viewed );
		}

		return 0;
	}


	/**
	 * Get how many views remaining for a given post type
	 * 
	 * @param string $post_type 
	 * @return integer
	 */
	public function get_views_remaining( $post_type = 'post' ) {
		$limits = rcp_get_level_limits( $this->get_user_level(), $post_type );

		if( isset( $limits ) ) {
			if( isset( $limits['enabled'] ) && $limits['enabled'] === 'yes' ) {
				$remaining = (int) $limits['views'] - $this->get_views_count( $post_type );
			}else {
				return -1;
			}
		}

		if( $remaining <= 0 ) return 0;

		return $remaining;
	}


	/**
	 * Gets the allowed number of views for a post type within
	 * the set timeframe
	 * 
	 * @param string $post_type 
	 * @return integer
	 */
	public function get_views_allowed( $post_type = 'post' ) {
		$limits = rcp_get_level_limits( $this->get_user_level(), $post_type );

		if( isset( $limits ) ) {
			if( isset( $limits['enabled'] ) && $limits['enabled'] === 'yes' ) {
				return absint( $limits['views'] );
			}
		}

		return -1;
	}


	/**
	 * Gets the timeframe window for limiting views
	 * 
	 * @param string $post_type 
	 * @return integer
	 */
	public function views_timeframe( $post_type = 'post' ) {
		$limits    = rcp_get_level_limits( $this->get_user_level(), $post_type );
		$timeframe = 0;

		if( isset( $limits ) ) {
			switch( $limits['interval'] ) {
				case 'min' :
					$timeframe = 60;
					break;
				case 'hour' :
					$timeframe = 3600;
					break;
				case 'day' :
					$timeframe = 86400;
					break;
				case 'week' :
					$timeframe = 604800;
					break;
				case 'month' :
					$timeframe = 2592000;
					break;
				case 'year' :
					$timeframe = 31536000;
					break;
			}

			return time() - ( $timeframe * $limits['interval_count'] );
		}

		return $timeframe;
	}


	/**
	 * Checks whether the user has viewed the specified post
	 * within the set timeframe
	 * 
	 * @param integer $post_id 
	 * @return boolean
	 */
	public function has_viewed( $post_id = 0 ) {
		global $wpdb, $rcpcl;

		if( empty( $post_id ) ) {
			$post_id = get_queried_object_id();
		}

		$post_type = get_post_type( $post_id );

		$table = rcp_get_view_limits_db_name();

		$viewed = $wpdb->get_var(
			$wpdb->prepare( "
				SELECT post_ids FROM {$table} WHERE 
					`user_id`		  = %d AND 
					`user_ip`		  = %s AND 
					`post_type`       = %s AND 
					`limit_start`    >= %d 
				ORDER BY `limit_start` DESC
				LIMIT 1
			;",
				absint( $this->ID ),
				sanitize_text_field( get_current_user_ip() ),
				$post_type,
				absint( $this->views_timeframe( $post_type ) )
			) );

		$viewed = (array) maybe_unserialize( $viewed );

		if( in_array( $post_id, $viewed ) ) {
			return true;
		}

		return false;
	}


	/**
	 * Records a viewed post given certain criteria is met
	 * 
	 * @param integer $post_id 
	 * @return boolean
	 */
	public function record_view( $post_id = 0 ) {
		global $wpdb, $rcpcl;

		if( empty( $post_id ) ) {
			$post_id = get_queried_object_id();
		}

		$post_type = get_post_type( $post_id );

		if( $this->has_viewed( $post_id ) )
			return false;

		if( $this->get_views_remaining( $post_type ) <= 0 )
			return false;

		if( $this->is_crawler() )
			return false;

		do_action( 'rcp_cl_before_record_view', $post_id );

		$table = rcp_get_view_limits_db_name();

		$row = $wpdb->get_row(
			$wpdb->prepare( "
				SELECT * FROM {$table} WHERE 
					`user_id`		  = %d AND 
					`user_ip`		  = %s AND 
					`post_type`       = %s AND 
					`limit_start`    >= %d 
				ORDER BY `limit_start` DESC
				LIMIT 1
			;",
				absint( $this->ID ),
				get_current_user_ip(),
				$post_type,
				absint( $this->views_timeframe() )
			), ARRAY_A 
		);

		if( $row ) {
			$post_ids = (array) maybe_unserialize( $row['post_ids'] );
			$post_ids[] = absint( get_queried_object_id() );

			$wpdb->query(
				$wpdb->prepare( "
					UPDATE {$table} SET
						`last_viewed`     = %d,
						`limit_viewed`    = `limit_viewed` + 1,
						`post_ids`        = %s
					WHERE `id`            = %d
				;",
					time(),
					serialize( array_unique( array_filter( $post_ids ) ) ),
					absint( $row['id'] )
				)
			);
			
			return true;
		}else {
			$wpdb->query(
				$wpdb->prepare( "
					INSERT INTO {$table} SET
						`user_id`		  = %d,
						`user_ip`		  = %s,
						`last_viewed`     = %d,
						`post_type`       = %s, 
						`limit_start`     = %d,
						`limit_viewed`    = %d,
						`post_ids`        = %s
				;",
					$this->ID,
					get_current_user_ip(),
					time(),
					$post_type,
					time(),
					1,
					serialize( (array) get_queried_object_id() )
				)
			);

			return true;
		}

		return false;
	}



	/**
	 * Perform a reverse and forward DNS lookup to determine
	 * if client is a crawler
	 * 
	 * @return boolean
	 */
	public function is_crawler( $ip = '' ) {
		if( empty( $ip ) ) {
			$ip  = get_current_user_ip();
		}
		
		$host    = gethostbyaddr( $ip );
		$crawler = false;

		if( 
			// Google
			strrpos( $host, 'googlebot.com' )     !== false || 
			strrpos( $host, 'google.com' )        !== false ||

			// Yandex
			strrpos( $host, 'yandex.ru' )         !== false ||
			strrpos( $host, 'yandex.com' )        !== false ||
			strrpos( $host, 'yandex.net' )        !== false ||

			// Bing
			strrpos( $host, 'search.msn.com' )    !== false ||

			// Yahoo
			strrpos( $host, 'crawl.yahoo.net' )   !== false ||

			// Baidu
			strrpos( $host, 'baidu.com' )         !== false ||
			strrpos( $host, 'baidu.jp' )          !== false || 

			// Alexa
			strrpos( $host, 'alexa.com' )         !== false || 

			// DuckDuckGo
			in_array( $ip, array( '72.94.249.34', '72.94.249.35', '72.94.249.36', '72.94.249.37', '72.94.249.38' ) ) ) {

			if( gethostbyname( $host ) == $ip ) {
				$crawler = true;
			}
		}

		return apply_filters( 'rcp_cl_is_crawler', $crawler );
	}


}
