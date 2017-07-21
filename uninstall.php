<?php
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	die();
}
global $wpdb;

// User metas.
$metas = $wpdb->get_results( "SELECT * FROM $wpdb->usermeta WHERE meta_key = 'meta-box-order_nav-menus' AND meta_value LIKE '%add-cpt-archive%'" );

if ( $metas ) {
	foreach ( $metas as $meta ) {
		$meta->meta_value = unserialize( $meta->meta_value );
		$meta->meta_value['side'] = trim( str_replace( ',add-cpt-archive,', ',', ',' . $meta->meta_value['side'] . ',' ), ',' );
		update_user_meta( $meta->user_id, 'meta-box-order_nav-menus', $meta->meta_value );
	}
}


// Menu items.
$menu_items = get_posts( array(
	'post_type'      => 'nav_menu_item',
	'posts_per_page' => -1,
	'fields'         => 'ids',
	'meta_query' => array(
		'relation' => 'OR',
		array(
			'key'   => '_menu_item_type',
			'value' => 'cpt-archive',
		),
		array(
			'key'   => '_menu_item_object',
			'value' => 'cpt-archive',
		),
	),
) );

if ( $menu_items ) {
	foreach ( $menu_items as $post_id ) {
		wp_delete_post( (int) post_id, true );
	}
}


// Options.
delete_option( 'sf_archiver' );
delete_option( 'sfar_version' );

/**/