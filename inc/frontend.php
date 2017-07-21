<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'Cheatin\' uh?' );
}

/*------------------------------------------------------------------------------------------------*/
/* !FRONTEND ==================================================================================== */
/*------------------------------------------------------------------------------------------------*/

// !Alter the URL in menu items for cpt-archive objects and add/remove classes.

add_filter( 'wp_get_nav_menu_items', 'sfar_cpt_archive_menu_filter', 5 );

function sfar_cpt_archive_menu_filter( $items ) {

	if ( empty( $items ) ) {
		return $items;
	}

	foreach ( $items as &$item ) {
		if ( 'cpt-archive' !== $item->type ) {
			continue;
		}

		if ( post_type_exists( $item->object ) ) {

			$item->url = get_post_type_archive_link( $item->object );

			if ( is_post_type_archive( $item->object ) && ! is_search() ) {
				$item->classes[] = 'current-menu-item';
				$item->current   = true;
			}
			elseif ( is_singular( $item->object ) ) {
				$item->classes[] = 'current_page_parent';
			}
		}
		else {
			$item->url = user_trailingslashit( home_url() );
		}
	}

	return $items;
}


// !WordPress adds a CSS class to the blog item, but our archive items are wrongly detected as it.

add_filter( 'nav_menu_css_class', 'sfar_remove_current_blog_css_class', 1, 2 );

function sfar_remove_current_blog_css_class( $classes, $item ) {
	static $home_page_id;
	static $is_not_blog;

	if ( ! isset( $home_page_id ) ) {
		$home_page_id = (int) get_option( 'page_for_posts' );

		if ( empty( $home_page_id ) ) {
			remove_filter( 'nav_menu_css_class', 'sfar_remove_current_blog_css_class', 1 );
		}

		$is_not_blog = is_post_type_archive() || ( is_singular() && ! is_singular( 'post' ) );
	}

	if ( 'post_type' === $item->type && $home_page_id === (int) $item->object_id && $is_not_blog ) {
		$classes = array_diff( $classes, array( 'current_page_parent' ) );
	}

	return $classes;
}


// !Posts per page limit.

add_action( 'pre_get_posts', 'sfar_pre_get_posts' );

function sfar_pre_get_posts( $query ) {

	if ( ! $query->is_main_query() ) {
		return;
	}

	remove_action( 'pre_get_posts', 'sfar_pre_get_posts' );

	$post_type = array_filter( (array) $query->get( 'post_type' ) );

	if ( 1 === count( $post_type ) && $query->is_post_type_archive() && ! $query->get( 'posts_per_archive_page' ) ) {

		$post_type = reset( $post_type );
		$settings  = sfar_get_settings();

		if ( ! $settings || empty( $settings[ $post_type ]['posts_per_archive_page'] ) ) {
			return;
		}

		$query->set( 'posts_per_archive_page', $settings[ $post_type ]['posts_per_archive_page'] );

	}

}

/**/