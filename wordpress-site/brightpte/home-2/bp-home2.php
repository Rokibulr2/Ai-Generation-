<?php
/**
 * Home 2 redesign preview (brightpte.com).
 *
 * Serves the standalone template bp-home2/home-2.html in place of the theme
 * for the draft page whose ID is stored in the `bp_home2_page_id` option.
 * The page stays a draft, so only logged-in editors reach it via preview.
 *
 * Template tokens filled at render time:
 *   {{LATEST_POSTS}}   three most recent English-titled posts as blog cards
 *   {{IMG_xx}}         media-library image imported for Home 2, looked up by
 *   {{IMG_xx_SM}}      its Unsplash source id (falls back to the Unsplash CDN)
 *
 * Hooks only: this file is loaded by the sandbox on every request, so it must
 * never produce output at the top level.
 */

defined( 'ABSPATH' ) || exit;

function bp_home2_image( $src_id, $size ) {
	$ids = get_posts( array(
		'post_type'   => 'attachment',
		'post_status' => 'inherit',
		'meta_key'    => '_bp_home2_src',
		'meta_value'  => $src_id,
		'numberposts' => 1,
		'fields'      => 'ids',
	) );
	if ( $ids ) {
		$url = wp_get_attachment_image_url( $ids[0], $size );
		if ( $url ) {
			return $url;
		}
	}
	$w = 'thumbnail' === $size ? 160 : 1200;
	return 'https://images.unsplash.com/photo-' . $src_id . '?w=' . $w . '&q=80&fm=jpg&fit=crop';
}

function bp_home2_latest_posts() {
	// This page is English-only, so skip posts with Bangla titles.
	$posts = get_posts( array(
		'numberposts'      => 15,
		'post_status'      => 'publish',
		'suppress_filters' => false,
	) );
	$posts = array_slice( array_values( array_filter( $posts, function ( $p ) {
		return ! preg_match( '/\p{Bengali}/u', $p->post_title );
	} ) ), 0, 3 );
	$html = '';
	foreach ( $posts as $i => $p ) {
		$thumb = get_the_post_thumbnail_url( $p, 'large' );
		$cats  = get_the_category( $p->ID );
		$cat   = $cats ? $cats[0]->name : 'PTE Guide';
		$mins  = max( 1, (int) round( str_word_count( wp_strip_all_tags( $p->post_content ) ) / 220 ) );
		$html .= sprintf(
			'<a class="post rv" href="%1$s" style="transition-delay:%2$ss"><div class="thumb">%3$s</div><div class="meta"><span class="cat">%4$s</span><span>%5$s</span><span>· %6$d min read</span></div><h5>%7$s</h5></a>',
			esc_url( get_permalink( $p ) ),
			esc_attr( $i * 0.08 ),
			$thumb ? '<img src="' . esc_url( $thumb ) . '" alt="" loading="lazy">' : '',
			esc_html( $cat ),
			esc_html( get_the_date( 'M j, Y', $p ) ),
			$mins,
			esc_html( get_the_title( $p ) )
		);
	}
	return $html;
}

add_action( 'template_redirect', function () {
	$page_id = (int) get_option( 'bp_home2_page_id' );
	if ( ! $page_id || ! is_page( $page_id ) ) {
		return;
	}
	$file = __DIR__ . '/bp-home2/home-2.html';
	if ( ! is_readable( $file ) ) {
		return;
	}
	$images = array(
		'AU' => '1506973035872-a4ec16b8e8d9',
		'CA' => '1517935706615-2717063c2225',
		'UK' => '1513635269975-59663e0ac1ad',
		'NZ' => '1507699622108-4be3abd695ad',
		'IE' => '1590089415225-401ed6f9db8e',
		'DE' => '1587330979470-3595ac045ab0',
	);
	$tokens = array( '{{LATEST_POSTS}}' => bp_home2_latest_posts() );
	foreach ( $images as $code => $src_id ) {
		$tokens[ '{{IMG_' . $code . '}}' ]    = esc_url( bp_home2_image( $src_id, 'large' ) );
		$tokens[ '{{IMG_' . $code . '_SM}}' ] = esc_url( bp_home2_image( $src_id, 'thumbnail' ) );
	}
	nocache_headers();
	header( 'X-Robots-Tag: noindex, nofollow' );
	echo strtr( file_get_contents( $file ), $tokens ); // phpcs:ignore WordPress.Security.EscapeOutput -- trusted template, tokens escaped above.
	exit;
}, 1 );
