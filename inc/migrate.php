<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'Cheatin\' uh?' );
}

/*------------------------------------------------------------------------------------------------*/
/* !MIGRATE ===================================================================================== */
/*------------------------------------------------------------------------------------------------*/

// !Take the old option to build the new one. Then, delete the old option.

add_action( 'admin_init', 'sfar_migrate' );

function sfar_migrate() {
	$old = get_option( '_w3p_acpt', array() );

	if ( ! $old || ! is_array( $old ) ) {
		return;
	}

	$new = array();

	foreach ( $old as $post_type => $atts ) {
		if ( ! empty( $atts['ppp'] ) && (int) $atts['ppp'] > 0 ) {
			$out[ $post_type ] = array(
				'posts_per_archive_page' => (int) $atts['ppp'],
			);
		}
	}

	if ( $new ) {
		add_option( 'sf_archiver', $new, true );
	}

	delete_option( '_w3p_acpt' );
}


function sfar_migrate_to_220() {
	global $wpdb;

	// It's time to get rid of the old stuff.
	$metas = $wpdb->get_col( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = '_menu_item_object' AND meta_value = 'cpt-archive'" );

	if ( $metas ) {
		foreach ( $metas as $menu_item_id ) {
			$post_type = get_post_meta( $menu_item_id, '_menu_item_type', true );
			update_post_meta( $menu_item_id, '_menu_item_object', $post_type );
			update_post_meta( $menu_item_id, '_menu_item_type', 'cpt-archive' );
		}
	}
}

if ( ! get_option( 'sfar_version' ) ) {
	sfar_migrate_to_220();
	update_option( 'sfar_version', SFAR_VERSION );
}

/**/