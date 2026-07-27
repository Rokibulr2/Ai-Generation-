<?php
/** RK Testimonials — admin-managed text + video testimonials with front-end sliders. */
if (!defined('ABSPATH')) { exit; }

/* ---------------------------------------------------------------------------
 * 1. Custom Post Type
 * ------------------------------------------------------------------------- */
add_action('init', function () {
    register_post_type('rk_testimonial', [
        'labels' => [
            'name'               => 'Testimonials',
            'singular_name'      => 'Testimonial',
            'add_new'            => 'Add Testimonial',
            'add_new_item'       => 'Add New Testimonial',
            'edit_item'          => 'Edit Testimonial',
            'new_item'           => 'New Testimonial',
            'view_item'          => 'View Testimonial',
            'search_items'       => 'Search Testimonials',
            'not_found'          => 'No testimonials yet',
            'not_found_in_trash' => 'No testimonials in trash',
            'all_items'          => 'All Testimonials',
            'menu_name'          => 'Testimonials',
        ],
        'public'        => false,
        'show_ui'       => true,
        'show_in_menu'  => true,
        'menu_icon'     => 'dashicons-format-quote',
        'menu_position' => 26,
        'supports'      => ['title', 'thumbnail', 'page-attributes'],
        'capability_type' => 'post',
    ]);
});

/* ---------------------------------------------------------------------------
 * 2. Meta box
 * ------------------------------------------------------------------------- */
add_action('add_meta_boxes', function () {
    add_meta_box('rk_testimonial_details', 'Testimonial Details', 'rk_testimonial_metabox', 'rk_testimonial', 'normal', 'high');
});

function rk_testimonial_metabox($post) {
    wp_nonce_field('rk_testimonial_save', 'rk_testimonial_nonce');
    $type    = get_post_meta($post->ID, 'rk_t_type', true) ?: 'text';
    $company = get_post_meta($post->ID, 'rk_t_company', true);
    $quote   = get_post_meta($post->ID, 'rk_t_quote', true);
    $rating  = get_post_meta($post->ID, 'rk_t_rating', true);
    $rating  = ($rating === '' ? '5' : $rating);
    $video   = get_post_meta($post->ID, 'rk_t_video', true);
    ?>
    <style>
        .rk-tm-field{margin:16px 0}
        .rk-tm-field label{display:block;font-weight:600;margin-bottom:6px;font-size:13px}
        .rk-tm-field input[type=text],.rk-tm-field input[type=url],.rk-tm-field textarea,.rk-tm-field select{width:100%;max-width:560px;padding:8px 10px;border:1px solid #dcdcde;border-radius:6px}
        .rk-tm-field textarea{min-height:90px}
        .rk-tm-hint{color:#787c82;font-size:12px;margin-top:4px}
        .rk-tm-video-only{border-left:3px solid #16a34a;padding-left:14px}
    </style>
    <div class="rk-tm-field">
        <label for="rk_t_type">Type</label>
        <select name="rk_t_type" id="rk_t_type">
            <option value="text"  <?php selected($type, 'text');  ?>>Text testimonial (written review)</option>
            <option value="video" <?php selected($type, 'video'); ?>>Video testimonial (portrait + video)</option>
        </select>
        <p class="rk-tm-hint">Text testimonials show in the "Don't Take My Word For It" slider. Video testimonials show in the video slider.</p>
    </div>
    <div class="rk-tm-field">
        <label for="rk_t_company">Company / Role</label>
        <input type="text" name="rk_t_company" id="rk_t_company" value="<?php echo esc_attr($company); ?>" placeholder="e.g. Founder, Peak Ridge Dental" />
    </div>
    <div class="rk-tm-field">
        <label for="rk_t_quote">Quote</label>
        <textarea name="rk_t_quote" id="rk_t_quote" placeholder="What the client said..."><?php echo esc_textarea($quote); ?></textarea>
    </div>
    <div class="rk-tm-field">
        <label for="rk_t_rating">Star rating (1–5) — text testimonials</label>
        <select name="rk_t_rating" id="rk_t_rating">
            <?php for ($i = 5; $i >= 1; $i--) { echo '<option value="' . $i . '" ' . selected($rating, (string) $i, false) . '>' . $i . ' stars</option>'; } ?>
        </select>
    </div>
    <div class="rk-tm-field rk-tm-video-only">
        <label for="rk_t_video">Video URL — video testimonials only</label>
        <input type="url" name="rk_t_video" id="rk_t_video" value="<?php echo esc_attr($video); ?>" placeholder="https://www.youtube.com/watch?v=... or Vimeo / MP4 URL" />
        <p class="rk-tm-hint">Paste a YouTube, Vimeo or direct MP4 link. Clicking the card opens the video.</p>
    </div>
    <div class="rk-tm-field">
        <p class="rk-tm-hint"><strong>Photo:</strong> set the person's photo (text) or the video thumbnail (video) using the <strong>Featured image</strong> box on the right.</p>
    </div>
    <?php
}

add_action('save_post_rk_testimonial', function ($post_id) {
    if (!isset($_POST['rk_testimonial_nonce']) || !wp_verify_nonce($_POST['rk_testimonial_nonce'], 'rk_testimonial_save')) { return; }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) { return; }
    if (!current_user_can('edit_post', $post_id)) { return; }
    $type = (isset($_POST['rk_t_type']) && $_POST['rk_t_type'] === 'video') ? 'video' : 'text';
    update_post_meta($post_id, 'rk_t_type', $type);
    update_post_meta($post_id, 'rk_t_company', sanitize_text_field($_POST['rk_t_company'] ?? ''));
    update_post_meta($post_id, 'rk_t_quote', wp_kses_post($_POST['rk_t_quote'] ?? ''));
    $rating = intval($_POST['rk_t_rating'] ?? 5);
    $rating = max(1, min(5, $rating));
    update_post_meta($post_id, 'rk_t_rating', (string) $rating);
    update_post_meta($post_id, 'rk_t_video', esc_url_raw($_POST['rk_t_video'] ?? ''));
});

/* ---------------------------------------------------------------------------
 * 3. Admin list columns
 * ------------------------------------------------------------------------- */
add_filter('manage_rk_testimonial_posts_columns', function ($cols) {
    $new = [];
    foreach ($cols as $k => $v) {
        if ($k === 'title') { $new['rk_thumb'] = 'Photo'; }
        $new[$k] = $v;
        if ($k === 'title') {
            $new['rk_type']    = 'Type';
            $new['rk_company'] = 'Company / Role';
            $new['rk_rating']  = 'Rating';
        }
    }
    return $new;
});
add_action('manage_rk_testimonial_posts_custom_column', function ($col, $post_id) {
    if ($col === 'rk_thumb') {
        echo has_post_thumbnail($post_id) ? get_the_post_thumbnail($post_id, [46, 46], ['style' => 'border-radius:8px;object-fit:cover']) : '—';
    } elseif ($col === 'rk_type') {
        $t = get_post_meta($post_id, 'rk_t_type', true) ?: 'text';
        $bg = $t === 'video' ? '#16a34a' : '#111';
        echo '<span style="background:' . $bg . ';color:#fff;font-size:11px;padding:2px 9px;border-radius:20px;text-transform:capitalize">' . esc_html($t) . '</span>';
    } elseif ($col === 'rk_company') {
        echo esc_html(get_post_meta($post_id, 'rk_t_company', true));
    } elseif ($col === 'rk_rating') {
        $r = intval(get_post_meta($post_id, 'rk_t_rating', true) ?: 5);
        echo '<span style="color:#f5a623">' . str_repeat('&#9733;', $r) . '</span>';
    }
}, 10, 2);

/* ---------------------------------------------------------------------------
 * 4. Query helper
 * ------------------------------------------------------------------------- */
function rk_get_testimonials($type) {
    $q = new WP_Query([
        'post_type'      => 'rk_testimonial',
        'post_status'    => 'publish',
        'posts_per_page' => 30,
        'orderby'        => 'menu_order date',
        'order'          => 'ASC',
        'meta_query'     => [[
            'key'   => 'rk_t_type',
            'value' => $type,
        ]],
        'no_found_rows'  => true,
    ]);
    return $q->posts;
}

/* ---------------------------------------------------------------------------
 * 5. Shortcode: text testimonials
 * ------------------------------------------------------------------------- */
add_shortcode('rk_text_testimonials', function () {
    $items = rk_get_testimonials('text');
    if (empty($items)) { return ''; }
    ob_start();
    ?>
    <div class="rk-slider rk-tslider" data-rk-slider>
        <button type="button" class="rk-slider-arrow prev" aria-label="Previous">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"></polyline></svg>
        </button>
        <div class="rk-slider-track">
            <?php foreach ($items as $p) :
                $company = get_post_meta($p->ID, 'rk_t_company', true);
                $quote   = get_post_meta($p->ID, 'rk_t_quote', true);
                $rating  = intval(get_post_meta($p->ID, 'rk_t_rating', true) ?: 5);
                $img     = get_the_post_thumbnail_url($p->ID, 'thumbnail');
            ?>
            <div class="rk-tcard">
                <div class="rk-tcard-stars"><?php echo str_repeat('&#9733;', $rating) . str_repeat('<span style="color:#e2e2e0">&#9733;</span>', 5 - $rating); ?></div>
                <p class="rk-tcard-quote"><?php echo wp_kses_post(wpautop($quote)); ?></p>
                <div class="rk-tcard-foot">
                    <?php if ($img) : ?><img src="<?php echo esc_url($img); ?>" alt="<?php echo esc_attr($p->post_title); ?>" loading="lazy" /><?php endif; ?>
                    <div>
                        <div class="rk-tcard-name"><?php echo esc_html($p->post_title); ?></div>
                        <?php if ($company) : ?><div class="rk-tcard-role"><?php echo esc_html($company); ?></div><?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <button type="button" class="rk-slider-arrow next" aria-label="Next">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
        </button>
    </div>
    <?php
    return ob_get_clean();
});

/* ---------------------------------------------------------------------------
 * 6. Shortcode: video testimonials
 * ------------------------------------------------------------------------- */
add_shortcode('rk_video_testimonials', function () {
    $items = rk_get_testimonials('video');
    if (empty($items)) { return ''; }
    ob_start();
    ?>
    <div class="rk-slider rk-vslider" data-rk-slider>
        <button type="button" class="rk-slider-arrow prev" aria-label="Previous">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"></polyline></svg>
        </button>
        <div class="rk-slider-track">
            <?php foreach ($items as $p) :
                $company = get_post_meta($p->ID, 'rk_t_company', true);
                $quote   = get_post_meta($p->ID, 'rk_t_quote', true);
                $video   = get_post_meta($p->ID, 'rk_t_video', true);
                $img     = get_the_post_thumbnail_url($p->ID, 'medium');
                $tag     = $video ? 'a' : 'div';
                $attrs   = $video ? ' href="#" class="rk-vt-media" data-rk-video="' . esc_attr($video) . '"' : ' class="rk-vt-media"';
            ?>
            <div class="rk-vcard">
                <<?php echo $tag . $attrs; ?>>
                    <?php if ($img) : ?><img src="<?php echo esc_url($img); ?>" alt="<?php echo esc_attr($p->post_title); ?>" loading="lazy" /><?php endif; ?>
                    <?php if ($video) : ?><span class="rk-vt-play"><span>&#9658;</span></span><?php endif; ?>
                    <span class="rk-vt-meta">
                        <span class="rk-vt-name"><?php echo esc_html($p->post_title); ?></span>
                        <?php if ($company) : ?><span class="rk-vt-company"><?php echo esc_html($company); ?></span><?php endif; ?>
                    </span>
                </<?php echo $tag; ?>>
                <?php if ($quote) : ?>
                <div class="rk-vt-quote">
                    <span class="rk-vt-qmark">&ldquo;</span>
                    <p class="rk-vt-text"><?php echo esc_html($quote); ?></p>
                </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
        <button type="button" class="rk-slider-arrow next" aria-label="Next">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
        </button>
    </div>
    <div class="rk-vt-modal" id="rk-vt-modal" aria-hidden="true">
        <div class="rk-vt-modal-backdrop" data-rk-close></div>
        <div class="rk-vt-modal-inner">
            <button type="button" class="rk-vt-modal-close" data-rk-close aria-label="Close">&times;</button>
            <div class="rk-vt-modal-frame"></div>
        </div>
    </div>
    <?php
    return ob_get_clean();
});

/* ---------------------------------------------------------------------------
 * 7. Front-end slider + video-modal JS (printed once, on the front end)
 * ------------------------------------------------------------------------- */
add_action('wp_footer', function () {
    if (is_admin()) { return; }
    static $done = false;
    if ($done) { return; }
    $done = true;
    ?>
    <script>
    (function () {
        function cardStep(track) {
            var card = track.querySelector(':scope > *');
            if (!card) { return track.clientWidth; }
            var style = getComputedStyle(track);
            var gap = parseFloat(style.columnGap || style.gap || '0') || 0;
            return card.getBoundingClientRect().width + gap;
        }
        function initSlider(slider) {
            var track = slider.querySelector('.rk-slider-track');
            var prev = slider.querySelector('.rk-slider-arrow.prev');
            var next = slider.querySelector('.rk-slider-arrow.next');
            if (!track) { return; }
            function update() {
                var max = track.scrollWidth - track.clientWidth - 2;
                if (prev) { prev.classList.toggle('is-disabled', track.scrollLeft <= 2); }
                if (next) { next.classList.toggle('is-disabled', track.scrollLeft >= max); }
            }
            if (prev) { prev.addEventListener('click', function () { track.scrollBy({ left: -cardStep(track), behavior: 'smooth' }); }); }
            if (next) { next.addEventListener('click', function () { track.scrollBy({ left: cardStep(track), behavior: 'smooth' }); }); }
            track.addEventListener('scroll', update, { passive: true });
            window.addEventListener('resize', update);
            update();
        }
        document.querySelectorAll('[data-rk-slider]').forEach(initSlider);

        // Video modal
        var modal = document.getElementById('rk-vt-modal');
        if (modal) {
            var frame = modal.querySelector('.rk-vt-modal-frame');
            function embed(url) {
                var yt = url.match(/(?:youtu\.be\/|youtube\.com\/(?:watch\?v=|embed\/|shorts\/))([\w-]{11})/);
                if (yt) { return '<iframe src="https://www.youtube.com/embed/' + yt[1] + '?autoplay=1" allow="autoplay; encrypted-media" allowfullscreen frameborder="0"></iframe>'; }
                var vm = url.match(/vimeo\.com\/(?:video\/)?(\d+)/);
                if (vm) { return '<iframe src="https://player.vimeo.com/video/' + vm[1] + '?autoplay=1" allow="autoplay; fullscreen" allowfullscreen frameborder="0"></iframe>'; }
                return '<video src="' + url + '" controls autoplay playsinline></video>';
            }
            function open(url) { frame.innerHTML = embed(url); modal.classList.add('is-open'); modal.setAttribute('aria-hidden', 'false'); document.body.style.overflow = 'hidden'; }
            function close() { modal.classList.remove('is-open'); modal.setAttribute('aria-hidden', 'true'); frame.innerHTML = ''; document.body.style.overflow = ''; }
            document.querySelectorAll('[data-rk-video]').forEach(function (el) {
                el.addEventListener('click', function (e) { e.preventDefault(); open(el.getAttribute('data-rk-video')); });
            });
            modal.querySelectorAll('[data-rk-close]').forEach(function (el) { el.addEventListener('click', close); });
            document.addEventListener('keydown', function (e) { if (e.key === 'Escape') { close(); } });
        }
    })();
    </script>
    <?php
}, 99);
