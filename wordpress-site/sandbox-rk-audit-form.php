<?php
/**
 * Free SEO Audit form: single source of truth.
 *
 * The markup used to live inline in tpl-free-audit.php. It is now here so the
 * same form can be embedded on service and industry pages via [rk_audit_form]
 * without the two copies drifting apart.
 *
 * Assets (rk-custom-pages.css / rk-free-audit.js) are enqueued by
 * rk-custom-pages-controller.php on the audit page itself, and by the hook at
 * the bottom of this file anywhere the shortcode appears.
 */
if (!defined('ABSPATH')) { exit; }

/**
 * Every rule in rk-custom-pages.css is scoped under .rk-cp, so the form has to
 * carry that wrapper to be styled anywhere other than the custom templates,
 * which provide it themselves. Nesting it inside an existing .rk-cp is
 * harmless — descendant selectors still match.
 */
function rk_render_audit_form() {
    ob_start(); ?>
    <div class="rk-cp rk-audit-standalone">
<section class="rk-sec soft" id="audit-form" aria-labelledby="fa-form">
    <div class="rk-wrap">
      <div class="rk-sec-head"><div class="rk-eyebrow">Request Your Audit</div><h2 class="rk-h2" id="fa-form">Claim Your Free Manual SEO Audit</h2></div>
      <form class="rk-form-shell" id="rk-audit-form" novalidate>
        <div class="rk-notice">
          <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 8v5M12 16v.01"/></svg>
          <span>Please note: I personally review every request. I&rsquo;ll contact you first to confirm you genuinely need the audit before preparing and sending your report.</span>
        </div>
        <div class="rk-row2">
          <div class="rk-field"><label for="fa-name">Name <span class="req">*</span></label><input type="text" id="fa-name" name="name_field" autocomplete="name" required></div>
          <div class="rk-field"><label for="fa-email">Email <span class="req">*</span></label><input type="email" id="fa-email" name="email" autocomplete="email" required></div>
        </div>
        <div class="rk-row2">
          <div class="rk-field"><label for="fa-phone">Phone Number</label><input type="tel" id="fa-phone" name="phone" autocomplete="tel"></div>
          <div class="rk-field"><label for="fa-website">Website URL <span class="req">*</span></label><input type="url" id="fa-website" name="website" placeholder="https://" required></div>
        </div>
        <div class="rk-field"><label for="fa-type">Audit Type</label>
          <select id="fa-type" name="audit_type">
            <option>Technical SEO</option><option>Full SEO Audit</option><option>AEO (Answer Engine Optimization)</option><option>GEO (Generative Engine Optimization)</option><option>Local SEO</option><option>E-commerce SEO</option><option>Not sure yet</option>
          </select>
        </div>
        <div class="rk-field"><label for="fa-message">Message <span class="req">*</span></label><textarea id="fa-message" name="message" placeholder="Tell me about your website and what you want to improve…" required></textarea></div>
        <div class="rk-verify">
          <div class="q">Quick human check <span class="req" style="color:#dc2626">*</span></div>
          <div class="hint">Just confirming you&rsquo;re human &mdash; no CAPTCHA needed! The website name is <code>growwithrokibul</code> (ignore the &ldquo;.com&rdquo;). Type its <strong>last 7 letters</strong> &mdash; they spell my first name.</div>
          <input type="text" name="verify" aria-label="Human verification" autocomplete="off" placeholder="Enter 7 letters">
        </div>
        <label class="rk-confirm-check"><input type="checkbox" id="rk-audit-check"> I confirm that I am a real person and the information I have provided is accurate.</label>
        <div class="rk-submit-wrap"><button type="submit" class="rk-btn rk-btn-dark rk-btn-block">Send My Free Audit Request</button></div>
        <div class="rk-form-status" role="status" aria-live="polite"></div>
      </form>
    </div>
  </section>
    </div>
    <?php return ob_get_clean();
}

add_shortcode('rk_audit_form', 'rk_render_audit_form');

/** Enqueue the form assets wherever the shortcode is used. */
function rk_audit_form_assets() {
    static $done = false;
    if ($done) { return; }
    $done = true;
    $base = function_exists('rk_assets_url') ? rk_assets_url() : wp_upload_dir()['baseurl'] . '/rk-assets';
    $ver  = '1.3.0';
    wp_enqueue_style('rk-custom-pages', $base . '/rk-custom-pages.css', [], $ver);
    wp_enqueue_script('rk-free-audit', $base . '/rk-free-audit.js', [], $ver, true);
    wp_localize_script('rk-free-audit', 'RK_AUDIT', [
        'ajax'   => admin_url('admin-ajax.php'),
        'nonce'  => wp_create_nonce('rk_audit'),
        'verify' => defined('RK_VERIFY_ANSWER') ? RK_VERIFY_ANSWER : 'rokibul',
    ]);
}

add_action('wp_enqueue_scripts', function () {
    if (!is_singular() || is_page('free-seo-audit')) { return; }
    $id = get_queried_object_id();
    if (!$id) { return; }
    $haystack = (string) get_post_meta($id, '_elementor_data', true) . (string) get_post_field('post_content', $id);
    if (strpos($haystack, 'rk_audit_form') === false) { return; }
    rk_audit_form_assets();
}, 20);
