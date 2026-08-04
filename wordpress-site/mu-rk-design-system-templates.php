<?php
/* Plugin Name: RK Design System Blog & Single Post Templates */

/**
 * LIVE AT: wp-content/mu-plugins/rk-design-system-templates.php
 *
 * Route single blog posts to the sandbox template.
 *
 * The template at wp-content/novamira-sandbox/tpl-single-post.php guards itself
 * with did_action('template_redirect') and renders a complete document, so it
 * must be reached through template_include and never included at boot.
 *
 * This file previously pointed at __DIR__ . '/tpl-rk-single.php'. That file was
 * a page template sitting in mu-plugins, which wp-settings.php includes on every
 * request before the main query exists -- its top-level get_header() reached a
 * wp_head() callback that called get_queried_object() on a null $wp_query and
 * took the whole site down, admin included. Do not put templates in this folder.
 */
add_filter('template_include', function ($template) {
    if (is_singular('post') && !is_embed()) {
        $custom = WP_CONTENT_DIR . '/novamira-sandbox/tpl-single-post.php';
        if (is_readable($custom)) {
            return $custom;
        }
    }
    return $template;
}, 9999);
