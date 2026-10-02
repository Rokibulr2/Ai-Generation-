<?php
/**
 * Home 2 redesign preview (brightpte.com).
 *
 * Serves the standalone template bp-home2/home-2.html in place of the theme
 * for the draft page whose ID is stored in the `bp_home2_page_id` option.
 * The page stays a draft, so only logged-in editors reach it via preview.
 *
 * Hooks only: this file is loaded by the sandbox on every request, so it must
 * never produce output at the top level.
 */

defined( 'ABSPATH' ) || exit;

add_action( 'template_redirect', function () {
	$page_id = (int) get_option( 'bp_home2_page_id' );
	if ( ! $page_id || ! is_page( $page_id ) ) {
		return;
	}
	$file = __DIR__ . '/bp-home2/home-2.html';
	if ( ! is_readable( $file ) ) {
		return;
	}
	nocache_headers();
	header( 'X-Robots-Tag: noindex, nofollow' );
	readfile( $file );
	exit;
}, 1 );
