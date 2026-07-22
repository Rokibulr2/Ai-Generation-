<?php
/** RK Service Schema — Service + Breadcrumb + FAQPage JSON-LD on each service page. */
if (!defined('ABSPATH')) { exit; }

add_action('wp_head', function () {
    if (is_admin() || !is_page()) { return; }
    $id  = get_queried_object_id();
    $map = get_option('rk_svc_data', []);
    if (empty($map[$id])) { return; }
    $svc = $map[$id];

    $page_url = get_permalink($id);
    $title    = get_the_title($id);
    $desc     = isset($svc['intro']) ? $svc['intro'] : $title;
    $site     = home_url('/');

    $person = [
        '@type' => 'Person',
        'name'  => 'Rokibul Islam Shuvo',
        'url'   => $site,
        'jobTitle' => 'SEO Consultant',
    ];

    // Service schema — entity-rich via serviceType + about (core entities)
    $about = [];
    foreach (array_slice((array) ($svc['entities'] ?? []), 0, 8) as $e) {
        $about[] = ['@type' => 'Thing', 'name' => $e];
    }
    $service = [
        '@context'    => 'https://schema.org',
        '@type'       => 'Service',
        'name'        => $title,
        'description' => $desc,
        'serviceType' => $svc['center'] ?? $title,
        'url'         => $page_url,
        'provider'    => $person,
        'areaServed'  => ['@type' => 'Place', 'name' => 'Worldwide'],
        'about'       => $about,
    ];

    $breadcrumb = [
        '@context' => 'https://schema.org',
        '@type'    => 'BreadcrumbList',
        'itemListElement' => [
            ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => $site],
            ['@type' => 'ListItem', 'position' => 2, 'name' => 'Services', 'item' => $site . 'services/'],
            ['@type' => 'ListItem', 'position' => 3, 'name' => $title, 'item' => $page_url],
        ],
    ];

    // FAQPage — pull Q/A from the page's Elementor accordion, if present
    $faq_items = [];
    $data = json_decode(get_post_meta($id, '_elementor_data', true), true);
    if (is_array($data)) {
        $stack = $data;
        while ($stack) {
            $el = array_pop($stack);
            if (isset($el['widgetType']) && in_array($el['widgetType'], ['accordion', 'toggle'], true)) {
                $tabs = $el['settings']['tabs'] ?? [];
                foreach ($tabs as $t) {
                    $q = trim(wp_strip_all_tags($t['tab_title'] ?? ''));
                    $a = trim(wp_strip_all_tags($t['tab_content'] ?? ''));
                    if ($q !== '' && $a !== '') {
                        $faq_items[] = [
                            '@type' => 'Question',
                            'name'  => $q,
                            'acceptedAnswer' => ['@type' => 'Answer', 'text' => $a],
                        ];
                    }
                }
            }
            if (!empty($el['elements'])) { foreach ($el['elements'] as $c) { $stack[] = $c; } }
        }
    }

    $blocks = [$service, $breadcrumb];
    if ($faq_items) {
        $blocks[] = ['@context' => 'https://schema.org', '@type' => 'FAQPage', 'mainEntity' => $faq_items];
    }

    foreach ($blocks as $b) {
        echo "\n" . '<script type="application/ld+json">' . wp_json_encode($b, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</script>';
    }
}, 20);
