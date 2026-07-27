<?php
/**
 * RK — route single blog posts to the sandbox single-post template.
 *
 * DEPLOY TO:  wp-content/novamira-sandbox/rk-single-post-route.php
 *
 * Background: rk-blog-controller.php used to send single posts to
 * tpl-single-post.php. That routing stopped working after the template was
 * moved out of the sandbox and the controller was edited to point at
 * wp-content/mu-plugins/tpl-rk-single.php, which no longer exists — so
 * WordPress falls back to the theme's default single.php.
 *
 * This file restores the routing without needing to touch the controller. It
 * runs at priority 999 on template_include so it wins over whatever the
 * controller returns, and it only acts when the template file is actually
 * present, so deleting or renaming the template cleanly reverts to the theme
 * default instead of producing a blank page.
 *
 * SAFETY: this file registers hooks only. It emits no output and calls no
 * template or query function at the top level, which is what makes it safe for
 * a directory that is included on every request during boot. Do not add
 * markup, get_header(), or any conditional tag outside the callbacks below.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'rk_route_single_post_template' ) ) {

	/**
	 * Send single posts to the sandbox template when it exists.
	 *
	 * @param string $template Template path resolved so far.
	 * @return string
	 */
	function rk_route_single_post_template( $template ) {

		// Only ordinary blog posts. Leaves pages, attachments, feeds,
		// embeds and every custom post type on their own templates.
		if ( ! is_singular( 'post' ) || is_embed() ) {
			return $template;
		}

		$custom = __DIR__ . '/tpl-single-post.php';

		return is_readable( $custom ) ? $custom : $template;
	}

	add_filter( 'template_include', 'rk_route_single_post_template', 999 );
}
