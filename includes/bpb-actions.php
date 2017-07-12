<?php
if ( !defined( 'BPB_VERSION' ) ) exit;

/**
 * Action Handler
 * @since 1.0
 * @version 1.0
 */
function bpb_handle_actions() {
	if ( !is_user_logged_in() ) return;
	if ( !isset( $_REQUEST['action'] ) || !isset( $_REQUEST['list'] ) || !isset( $_REQUEST['token'] ) || !isset( $_REQUEST['num'] ) ) return;
	
	switch ( $_REQUEST['action'] ) {
		case 'unblock' :
			if ( wp_verify_nonce( $_REQUEST['token'], 'unblock-' . $_REQUEST['list'] ) ) {
				$current = bpb_get_blocked_users( (int) $_REQUEST['list'] );
				if ( isset( $current[ $_REQUEST['num'] ] ) ) {
					unset( $current[ $_REQUEST['num'] ] );
					update_user_meta( (int) $_REQUEST['list'], '_block', $current );
					
					do_action( 'bpb_action_unblock', $current );
					
					bp_core_add_message( __( 'User successfully unblocked', 'bpblock' ) );
				}
			}
		break;
		case 'block' :
			if ( wp_verify_nonce( $_REQUEST['token'], 'block-' . $_REQUEST['list'] ) ) {
				$current = bpb_get_blocked_users( (int) $_REQUEST['list'] );
				if ( user_can( (int) $_REQUEST['num'], BPB_ADMIN_CAP ) ) {
					bp_core_add_message( __( 'You can not block administrators / moderators', 'bpblock' ), 'error' );
				}
				else {
					$current[] = (int) $_REQUEST['num'];
					update_user_meta( (int) $_REQUEST['list'], '_block', $current );
				
					do_action( 'bpb_action_block', $current );
				
					bp_core_add_message( __( 'User successfully blocked', 'bpblock' ) );
				}
			}
		break;
		default :
			do_action( 'bpb_action' );
		break;
	}
	
	wp_safe_redirect( remove_query_arg( array( 'action', 'list', 'num', 'token' ) ) );
	exit();
}

/**
 * Add Block Button in Members List
 * @since 1.0
 * @version 1.0
 */
function bpb_insert_block_button_loop() {
	if ( !is_user_logged_in() ) return;
	$user_id = get_current_user_id();
	$member_id = bp_get_member_user_id();
	if ( $user_id == $member_id || user_can( $member_id, BPB_ADMIN_CAP ) ) return;
	echo '<div class="generic-button block-this-user"><a href="' . bpb_block_link( $user_id, $member_id ) . '" class="activity-button">' . __( 'Block', 'bpblock' ) . '</a></div>';
}

/**
 * Add Block Button in Loop
 * @since 1.0
 * @version 1.0
 */
function bpb_insert_block_button_profile() {
	if ( !is_user_logged_in() ) return;
	$user_id = get_current_user_id();
	$member_id = bp_displayed_user_id();
	if ( $user_id == $member_id || user_can( $member_id, BPB_ADMIN_CAP ) ) return;
	echo '<div class="generic-button block-this-user"><a href="' . bpb_block_link( $user_id, $member_id ) . '" class="activity-button">' . __( 'Block', 'bpblock' ) . '</a></div>';
}

?>