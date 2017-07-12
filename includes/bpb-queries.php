<?php
if ( !defined( 'BPB_VERSION' ) ) exit;

/**
 * Adjust BP_User_Query
 * @since 1.0
 * @version 1.0
 */
function bpb_adjust_user_query( &$data ) {
	if ( !is_user_logged_in() ) return;
	
	$_list = bpb_get_blocked_users( get_current_user_id() );
	update_option( 'catch_query_users_query', $_list );
	if ( ! empty( $_list ) ) {
		$list = implode( ',', $_list );
		$data->query_vars_raw['exclude'] = $list;
		$data->query_vars['exclude'] = $list;
	}
}

/**
 * Adjust Total Count
 * @since 1.0
 * @version 1.0
 */
function bpb_adjust_total_count( $count ) {
	if ( !is_user_logged_in() ) return $count;
	$list = count( bpb_get_blocked_users( get_current_user_id() ) );
	if ( $list === 0 ) return $count;
	return $count-$list;
}

/**
 * Adjust Latest Update
 * @since 1.0
 * @version 1.0
 */
function bpb_adjust_latest_update( $update_content ) {
	if ( is_user_logged_in() ) {
		$list = bpb_get_blocked_users( bp_get_member_user_id() );
		if ( in_array( get_current_user_id(), $list ) ) return '';
	}
	
	return $update_content;
}

/**
 * Adjust Mentions
 * @since 1.0
 * @version 1.0
 */
function bpb_adjust_mentions( $content, $activity ) {
	// Are mentions disabled?
	if ( ! bp_activity_do_mentions() ) {
		return $content;
	}

	// Try to find mentions
	$usernames = bp_activity_find_mentions( $content );

	// My list
	$my_list = bpb_get_blocked_users( get_current_user_id() );

	// We have mentions!
	if ( ! empty( $usernames ) ) {
		// Replace @mention text with plain username to disable notifications
		foreach( (array) $usernames as $user_id => $username ) {
			// Get the mentioned users block list
			$list = bpb_get_blocked_users( $user_id );
			
			// Users that block us or users that we block needs to be stripped out to prevent notices
			if ( in_array( $activity->user_id, $list ) || in_array( $user_id, $my_list ) ) {
				$activity->content = preg_replace( '/(@' . $username . '\b)/', '#' . $username, $activity->content );
				$content = preg_replace( '/(@' . $username . '\b)/', '#' . $username, $content );
			}
		}
	}
	return $content;
}
?>