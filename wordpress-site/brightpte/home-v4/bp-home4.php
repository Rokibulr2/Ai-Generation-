<?php
/**
 * Home v4 preview (brightpte.com), Mindly design system with the logo amber accent.
 *
 * Serves bp-home4/home-v4.html in place of the theme for the draft page
 * whose ID is stored in the `bp_home4_page_id` option. Drafts are only
 * reachable by logged-in editors via preview.
 *
 * Hooks only: this file is loaded by the sandbox on every request, so it must
 * never produce output at the top level.
 */

defined( 'ABSPATH' ) || exit;

add_action( 'template_redirect', function () {
	$page_id = (int) get_option( 'bp_home4_page_id' );
	if ( ! $page_id || ! is_page( $page_id ) ) {
		return;
	}
	$file = __DIR__ . '/bp-home4/home-v4.html';
	if ( ! is_readable( $file ) ) {
		return;
	}
	nocache_headers();
	header( 'X-Robots-Tag: noindex, nofollow' );
	readfile( $file );
	exit;
}, 1 );
