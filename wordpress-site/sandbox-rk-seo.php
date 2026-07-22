<?php
/** RK SEO — lightweight SEO manager (meta titles, descriptions, social, robots, schema). */
if (!defined('ABSPATH')) { exit; }

/* ------------------------------------------------------------------ config */
function rk_seo_post_types() {
    $pt = get_post_types(['public' => true], 'names');
    unset($pt['attachment']);
    return array_values($pt);
}
function rk_seo_opt($k, $d = '') {
    $o = get_option('rk_seo_settings', []);
    return isset($o[$k]) && $o[$k] !== '' ? $o[$k] : $d;
}
function rk_seo_sep() { return rk_seo_opt('sep', '—'); }
function rk_seo_tokens($s, $id = 0) {
    $title = $id ? get_the_title($id) : get_bloginfo('name');
    return str_replace(['%title%', '%sitename%', '%sep%'], [$title, get_bloginfo('name'), rk_seo_sep()], $s);
}

/* -------------------------------------------------------------- meta box UI */
add_action('add_meta_boxes', function () {
    foreach (rk_seo_post_types() as $pt) {
        add_meta_box('rk_seo_box', 'RK SEO — Search Appearance', 'rk_seo_metabox', $pt, 'normal', 'high');
    }
});

function rk_seo_metabox($post) {
    wp_nonce_field('rk_seo_save', 'rk_seo_nonce');
    $g = function ($k) use ($post) { return get_post_meta($post->ID, $k, true); };
    $title  = $g('_rk_seo_title');
    $desc   = $g('_rk_seo_desc');
    $focus  = $g('_rk_seo_focus');
    $canon  = $g('_rk_seo_canonical');
    $ogimg  = $g('_rk_seo_og_image');
    $schema = $g('_rk_seo_schema');
    $noindex  = $g('_rk_seo_noindex');
    $nofollow = $g('_rk_seo_nofollow');
    $url = get_permalink($post->ID);
    $host = wp_parse_url(home_url(), PHP_URL_HOST);
    $schema_types = ['' => 'Auto (recommended)', 'WebPage' => 'WebPage', 'AboutPage' => 'AboutPage', 'ContactPage' => 'ContactPage', 'ProfilePage' => 'ProfilePage', 'Article' => 'Article', 'CollectionPage' => 'CollectionPage'];
    ?>
    <style>
        .rk-seo-wrap{font-size:13px}
        .rk-seo-preview{border:1px solid #dcdcde;border-radius:8px;padding:14px 16px;margin:0 0 18px;background:#fff;max-width:600px}
        .rk-seo-preview .u{color:#202124;font-size:13px}
        .rk-seo-preview .t{color:#1a0dab;font-size:18px;line-height:1.3;margin:2px 0 3px;font-family:arial,sans-serif}
        .rk-seo-preview .d{color:#4d5156;font-size:13px;line-height:1.5}
        .rk-seo-field{margin:14px 0}
        .rk-seo-field label{display:block;font-weight:600;margin-bottom:5px}
        .rk-seo-field input[type=text],.rk-seo-field input[type=url],.rk-seo-field textarea,.rk-seo-field select{width:100%;max-width:600px;padding:8px 10px;border:1px solid #dcdcde;border-radius:6px}
        .rk-seo-field textarea{min-height:70px}
        .rk-seo-count{font-size:11px;color:#787c82;margin-top:4px}
        .rk-seo-count b.ok{color:#16a34a}.rk-seo-count b.warn{color:#d98a00}.rk-seo-count b.bad{color:#c00}
        .rk-seo-row{display:flex;gap:20px;flex-wrap:wrap}
        .rk-seo-chk{display:flex;align-items:center;gap:7px;font-weight:600}
    </style>
    <div class="rk-seo-wrap">
        <div class="rk-seo-preview">
            <div class="u"><?php echo esc_html($host); ?> › <?php echo esc_html(trim(str_replace(home_url(), '', $url), '/')); ?></div>
            <div class="t" id="rk-seo-pv-t"><?php echo esc_html($title ?: get_the_title($post->ID)); ?></div>
            <div class="d" id="rk-seo-pv-d"><?php echo esc_html($desc ?: 'Add a meta description to control how this page appears in search results.'); ?></div>
        </div>
        <div class="rk-seo-field">
            <label for="rk_seo_title">SEO Title</label>
            <input type="text" id="rk_seo_title" name="rk_seo_title" value="<?php echo esc_attr($title); ?>" placeholder="<?php echo esc_attr(get_the_title($post->ID) . ' ' . rk_seo_sep() . ' ' . get_bloginfo('name')); ?>" maxlength="70" />
            <div class="rk-seo-count">Length: <b id="rk-seo-tc">0</b> / ~60 chars</div>
        </div>
        <div class="rk-seo-field">
            <label for="rk_seo_desc">Meta Description</label>
            <textarea id="rk_seo_desc" name="rk_seo_desc" maxlength="200" placeholder="Write a compelling 150–160 character summary with your focus keyword."><?php echo esc_textarea($desc); ?></textarea>
            <div class="rk-seo-count">Length: <b id="rk-seo-dc">0</b> / ~155 chars</div>
        </div>
        <div class="rk-seo-row">
            <div class="rk-seo-field" style="flex:1;min-width:220px"><label for="rk_seo_focus">Focus Keyword</label><input type="text" id="rk_seo_focus" name="rk_seo_focus" value="<?php echo esc_attr($focus); ?>" placeholder="e.g. technical seo services" /></div>
            <div class="rk-seo-field" style="flex:1;min-width:220px"><label for="rk_seo_schema">Schema Type</label><select id="rk_seo_schema" name="rk_seo_schema"><?php foreach ($schema_types as $k => $v) { echo '<option value="' . esc_attr($k) . '" ' . selected($schema, $k, false) . '>' . esc_html($v) . '</option>'; } ?></select></div>
        </div>
        <div class="rk-seo-field"><label for="rk_seo_canonical">Canonical URL <span style="font-weight:400;color:#787c82">(optional)</span></label><input type="url" id="rk_seo_canonical" name="rk_seo_canonical" value="<?php echo esc_attr($canon); ?>" placeholder="<?php echo esc_attr($url); ?>" /></div>
        <div class="rk-seo-field"><label for="rk_seo_og_image">Social Share Image URL <span style="font-weight:400;color:#787c82">(Open Graph / Twitter)</span></label><input type="url" id="rk_seo_og_image" name="rk_seo_og_image" value="<?php echo esc_attr($ogimg); ?>" placeholder="https://…/image.jpg" /></div>
        <div class="rk-seo-field">
            <label>Robots Meta</label>
            <div class="rk-seo-row">
                <label class="rk-seo-chk"><input type="checkbox" name="rk_seo_noindex" value="1" <?php checked($noindex, '1'); ?> /> No-index (hide from search)</label>
                <label class="rk-seo-chk"><input type="checkbox" name="rk_seo_nofollow" value="1" <?php checked($nofollow, '1'); ?> /> No-follow (don't pass link equity)</label>
            </div>
        </div>
    </div>
    <script>
    (function(){
        function bind(inp,out,ideal){var el=document.getElementById(inp),o=document.getElementById(out);if(!el)return;function u(){var n=el.value.length;o.textContent=n;o.className=n===0?'bad':(n<=ideal?(n<ideal*0.5?'warn':'ok'):'warn');var pv=document.getElementById(inp==='rk_seo_title'?'rk-seo-pv-t':'rk-seo-pv-d');if(pv&&el.value)pv.textContent=el.value;}el.addEventListener('input',u);u();}
        bind('rk_seo_title','rk-seo-tc',60);
        bind('rk_seo_desc','rk-seo-dc',155);
    })();
    </script>
    <?php
}

add_action('save_post', function ($post_id) {
    if (!isset($_POST['rk_seo_nonce']) || !wp_verify_nonce($_POST['rk_seo_nonce'], 'rk_seo_save')) { return; }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) { return; }
    if (!current_user_can('edit_post', $post_id)) { return; }
    update_post_meta($post_id, '_rk_seo_title', sanitize_text_field($_POST['rk_seo_title'] ?? ''));
    update_post_meta($post_id, '_rk_seo_desc', sanitize_textarea_field($_POST['rk_seo_desc'] ?? ''));
    update_post_meta($post_id, '_rk_seo_focus', sanitize_text_field($_POST['rk_seo_focus'] ?? ''));
    update_post_meta($post_id, '_rk_seo_canonical', esc_url_raw($_POST['rk_seo_canonical'] ?? ''));
    update_post_meta($post_id, '_rk_seo_og_image', esc_url_raw($_POST['rk_seo_og_image'] ?? ''));
    update_post_meta($post_id, '_rk_seo_schema', sanitize_text_field($_POST['rk_seo_schema'] ?? ''));
    update_post_meta($post_id, '_rk_seo_noindex', isset($_POST['rk_seo_noindex']) ? '1' : '');
    update_post_meta($post_id, '_rk_seo_nofollow', isset($_POST['rk_seo_nofollow']) ? '1' : '');
});

/* --------------------------------------------------------- front-end output */
add_filter('pre_get_document_title', function ($title) {
    if (is_admin()) { return $title; }
    if (is_singular()) {
        $id = get_queried_object_id();
        $custom = get_post_meta($id, '_rk_seo_title', true);
        if ($custom) { return rk_seo_tokens($custom, $id); }
    }
    return $title;
}, 20);

add_action('wp_head', function () {
    if (is_admin()) { return; }
    $id = is_singular() ? get_queried_object_id() : 0;
    $out = [];

    // meta description
    $desc = $id ? get_post_meta($id, '_rk_seo_desc', true) : rk_seo_opt('home_desc');
    if (!$desc && $id) {
        $ex = get_post_field('post_excerpt', $id);
        $desc = $ex ? $ex : wp_trim_words(wp_strip_all_tags(get_post_field('post_content', $id)), 30, '');
    }
    if ($desc) { $out[] = '<meta name="description" content="' . esc_attr($desc) . '" />'; }

    // robots
    $robots = [];
    if (!get_option('blog_public') || ($id && get_post_meta($id, '_rk_seo_noindex', true) === '1')) { $robots[] = 'noindex'; } else { $robots[] = 'index'; }
    $robots[] = ($id && get_post_meta($id, '_rk_seo_nofollow', true) === '1') ? 'nofollow' : 'follow';
    $robots[] = 'max-image-preview:large';
    $out[] = '<meta name="robots" content="' . esc_attr(implode(', ', $robots)) . '" />';

    // canonical (custom overrides core)
    $canon = $id ? get_post_meta($id, '_rk_seo_canonical', true) : '';
    if ($canon) { $out[] = '<link rel="canonical" href="' . esc_url($canon) . '" />'; }

    // Open Graph + Twitter
    $og_title = ($id && ($t = get_post_meta($id, '_rk_seo_title', true))) ? rk_seo_tokens($t, $id) : wp_get_document_title();
    $og_url   = $id ? get_permalink($id) : home_url('/');
    $og_type  = is_singular('post') ? 'article' : 'website';
    $og_img   = $id ? get_post_meta($id, '_rk_seo_og_image', true) : '';
    if (!$og_img && $id && has_post_thumbnail($id)) { $og_img = get_the_post_thumbnail_url($id, 'full'); }
    if (!$og_img) { $og_img = rk_seo_opt('default_image'); }

    $out[] = '<meta property="og:locale" content="en_US" />';
    $out[] = '<meta property="og:type" content="' . esc_attr($og_type) . '" />';
    $out[] = '<meta property="og:title" content="' . esc_attr($og_title) . '" />';
    if ($desc) { $out[] = '<meta property="og:description" content="' . esc_attr($desc) . '" />'; }
    $out[] = '<meta property="og:url" content="' . esc_url($og_url) . '" />';
    $out[] = '<meta property="og:site_name" content="' . esc_attr(get_bloginfo('name')) . '" />';
    if ($og_img) { $out[] = '<meta property="og:image" content="' . esc_url($og_img) . '" />'; }
    $out[] = '<meta name="twitter:card" content="summary_large_image" />';
    $out[] = '<meta name="twitter:title" content="' . esc_attr($og_title) . '" />';
    if ($desc) { $out[] = '<meta name="twitter:description" content="' . esc_attr($desc) . '" />'; }
    if ($og_img) { $out[] = '<meta name="twitter:image" content="' . esc_url($og_img) . '" />'; }

    // optional per-page schema type
    if ($id && ($stype = get_post_meta($id, '_rk_seo_schema', true))) {
        $schema = [
            '@context' => 'https://schema.org',
            '@type'    => $stype,
            'name'     => $og_title,
            'url'      => $og_url,
        ];
        if ($desc) { $schema['description'] = $desc; }
        $out[] = '<script type="application/ld+json">' . wp_json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</script>';
    }

    echo "\n<!-- RK SEO -->\n" . implode("\n", $out) . "\n";
}, 1);

// remove core canonical when a custom one is set
add_action('template_redirect', function () {
    if (is_singular()) {
        $c = get_post_meta(get_queried_object_id(), '_rk_seo_canonical', true);
        if ($c) { remove_action('wp_head', 'rel_canonical'); }
    }
});

/* -------------------------------------------------------- admin dashboard */
add_action('admin_menu', function () {
    add_menu_page('RK SEO', 'RK SEO', 'manage_options', 'rk-seo', 'rk_seo_dashboard_page', 'dashicons-chart-line', 58);
    add_submenu_page('rk-seo', 'SEO Overview', 'Overview', 'manage_options', 'rk-seo', 'rk_seo_dashboard_page');
    add_submenu_page('rk-seo', 'SEO Settings', 'Settings', 'manage_options', 'rk-seo-settings', 'rk_seo_settings_page');
});

function rk_seo_dashboard_page() {
    $posts = get_posts(['post_type' => rk_seo_post_types(), 'post_status' => 'publish', 'numberposts' => -1, 'orderby' => 'title', 'order' => 'ASC']);
    $total = count($posts); $ok = 0;
    foreach ($posts as $p) { if (get_post_meta($p->ID, '_rk_seo_title', true) && get_post_meta($p->ID, '_rk_seo_desc', true)) { $ok++; } }
    ?>
    <div class="wrap">
        <h1>RK SEO — Overview</h1>
        <p style="font-size:14px"><strong><?php echo $ok; ?></strong> of <strong><?php echo $total; ?></strong> published items have a complete SEO title &amp; description.</p>
        <table class="widefat striped">
            <thead><tr><th>Page</th><th>SEO Title</th><th>Meta Description</th><th>Status</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($posts as $p) :
                $t = get_post_meta($p->ID, '_rk_seo_title', true);
                $d = get_post_meta($p->ID, '_rk_seo_desc', true);
                $has = $t && $d;
                ?>
                <tr>
                    <td><strong><?php echo esc_html($p->post_title); ?></strong><br><span style="color:#787c82;font-size:12px"><?php echo esc_html(get_post_type($p)); ?></span></td>
                    <td><?php echo $t ? esc_html($t) . ' <span style="color:#787c82">(' . strlen($t) . ')</span>' : '<em style="color:#c00">missing</em>'; ?></td>
                    <td><?php echo $d ? esc_html(wp_trim_words($d, 18)) . ' <span style="color:#787c82">(' . strlen($d) . ')</span>' : '<em style="color:#c00">missing</em>'; ?></td>
                    <td><?php echo $has ? '<span style="color:#16a34a;font-weight:600">● Good</span>' : '<span style="color:#d98a00;font-weight:600">● Incomplete</span>'; ?></td>
                    <td><a class="button button-small" href="<?php echo esc_url(get_edit_post_link($p->ID)); ?>">Edit</a></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
}

function rk_seo_settings_page() {
    if (isset($_POST['rk_seo_settings_nonce']) && wp_verify_nonce($_POST['rk_seo_settings_nonce'], 'rk_seo_settings')) {
        update_option('rk_seo_settings', [
            'sep'           => sanitize_text_field($_POST['sep'] ?? '—'),
            'home_desc'     => sanitize_textarea_field($_POST['home_desc'] ?? ''),
            'default_image' => esc_url_raw($_POST['default_image'] ?? ''),
        ]);
        echo '<div class="notice notice-success"><p>Settings saved.</p></div>';
    }
    $o = get_option('rk_seo_settings', []);
    ?>
    <div class="wrap">
        <h1>RK SEO — Settings</h1>
        <form method="post">
            <?php wp_nonce_field('rk_seo_settings', 'rk_seo_settings_nonce'); ?>
            <table class="form-table">
                <tr><th><label for="sep">Title Separator</label></th><td><input type="text" id="sep" name="sep" value="<?php echo esc_attr($o['sep'] ?? '—'); ?>" class="small-text" /> <span style="color:#787c82">Used in %sep% token.</span></td></tr>
                <tr><th><label for="home_desc">Homepage Meta Description</label></th><td><textarea id="home_desc" name="home_desc" rows="3" class="large-text"><?php echo esc_textarea($o['home_desc'] ?? ''); ?></textarea></td></tr>
                <tr><th><label for="default_image">Default Social Image URL</label></th><td><input type="url" id="default_image" name="default_image" value="<?php echo esc_attr($o['default_image'] ?? ''); ?>" class="large-text" /></td></tr>
            </table>
            <?php submit_button('Save Settings'); ?>
        </form>
    </div>
    <?php
}

/* ------------------------------------------------------ post-list SEO column */
foreach (['page', 'post'] as $pt) {
    add_filter("manage_{$pt}_posts_columns", function ($cols) {
        $cols['rk_seo'] = 'SEO';
        return $cols;
    });
    add_action("manage_{$pt}_posts_custom_column", function ($col, $post_id) {
        if ($col !== 'rk_seo') { return; }
        $has = get_post_meta($post_id, '_rk_seo_title', true) && get_post_meta($post_id, '_rk_seo_desc', true);
        echo $has ? '<span style="color:#16a34a" title="SEO complete">●</span>' : '<span style="color:#d98a00" title="SEO incomplete">●</span>';
    }, 10, 2);
}
