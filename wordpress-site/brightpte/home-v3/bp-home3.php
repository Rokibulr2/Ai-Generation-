<?php
/**
 * Home v3 preview (brightpte.com), Electr design system in brand colours.
 *
 * Serves bp-home3/home-v3.html in place of the theme for the draft page
 * whose ID is stored in the `bp_home3_page_id` option. Drafts are only
 * reachable by logged-in editors via preview.
 *
 * Template token: {{LATEST_POSTS}} -> three newest English-titled posts.
 *
 * Hooks only: this file is loaded by the sandbox on every request, so it must
 * never produce output at the top level.
 */

defined( 'ABSPATH' ) || exit;

function bp_home3_latest_posts() {
	// This page is English-only, so skip posts with Bangla titles.
	$posts = get_posts( array(
		'numberposts'      => 15,
		'post_status'      => 'publish',
		'suppress_filters' => false,
	) );
	$posts = array_slice( array_values( array_filter( $posts, function ( $p ) {
		return ! preg_match( '/\p{Bengali}/u', $p->post_title );
	} ) ), 0, 3 );
	$arrow = '<svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M3 8h10M9 4l4 4-4 4"/></svg>';
	$html  = '';
	foreach ( $posts as $p ) {
		$thumb = get_the_post_thumbnail_url( $p, 'large' );
		$html .= sprintf(
			'<a class="post rv" href="%1$s"><div class="thumb">%2$s</div><span class="mono">%3$s</span><h5>%4$s</h5><span class="more">Read more %5$s</span></a>',
			esc_url( get_permalink( $p ) ),
			$thumb ? '<img src="' . esc_url( $thumb ) . '" alt="" loading="lazy">' : '',
			esc_html( get_the_date( 'M j, Y', $p ) ),
			esc_html( get_the_title( $p ) ),
			$arrow
		);
	}
	return $html;
}

add_action( 'template_redirect', function () {
	$page_id = (int) get_option( 'bp_home3_page_id' );
	if ( ! $page_id || ! is_page( $page_id ) ) {
		return;
	}
	$file = __DIR__ . '/bp-home3/home-v3.html';
	if ( ! is_readable( $file ) ) {
		return;
	}
	nocache_headers();
	header( 'X-Robots-Tag: noindex, nofollow' );
	echo strtr( file_get_contents( $file ), array( '{{LATEST_POSTS}}' => bp_home3_latest_posts() ) ); // phpcs:ignore WordPress.Security.EscapeOutput -- trusted template, token escaped above.
	exit;
}, 1 );
