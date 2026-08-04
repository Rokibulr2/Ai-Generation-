<?php
/**
 * RK Design System — Single Blog Post Template
 *
 * DEPLOY TO:  wp-content/novamira-sandbox/tpl-single-post.php
 *
 * ---------------------------------------------------------------------------
 * THIS IS A TEMPLATE FILE, NOT A PLUGIN.
 *
 * It calls get_header(), have_posts() and the_post() at the top level, which is
 * correct for a file WordPress requires while rendering a post — and fatal for a
 * file included during boot. Putting it in wp-content/mu-plugins/ makes
 * wp-settings.php include it before the main query has run, and the first
 * wp_head() callback that touches the query object dies with "Call to a member
 * function get_queried_object() on null", taking down the front end AND
 * wp-admin on every request.
 *
 * It belongs in the sandbox directory, where rk-blog-controller.php routes
 * single posts to it through the template hierarchy.
 * ---------------------------------------------------------------------------
 *
 * DESIGN: this deliberately reuses the site's existing classes rather than
 * inventing new ones, so single posts match the rest of the site:
 *
 *   .rk-cs-wrap .rk-cs-hero .rk-cs-back .rk-cs-eyebrow .rk-cs-title
 *   .rk-cs-tagline .rk-cs-meta (.k/.v) .rk-cs-figure .rk-cs-inner
 *   .rk-hblog-grid .rk-bcard .rk-post-cat .rk-post-meta .rk-readmore
 *   .rk-prose-fig  (figures already inside post content inherit this)
 *
 * All of the above are defined site-wide and load on single posts already.
 * .rk-bcard and .rk-cs-figure are also watched by the site's scroll-reveal
 * script, so they animate in like every other card on the site.
 *
 * Only .rk-article (the post body) was ever template-local, so only that — plus
 * code, table and a few layout rules — is defined here.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<style id="rk-single-post-css">
/* ===== single post: body typography (the only template-local styles) ===== */
.rk-single-main{padding-bottom:80px}
.rk-single-col{max-width:820px;margin:0 auto;width:100%}
.rk-single-figure{max-width:980px;margin:0 auto 56px}

.rk-article{font-size:17px;line-height:1.78;color:#444;
  font-family:'Geist',-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif}
.rk-article > *:first-child{margin-top:0}
.rk-article p{margin:0 0 20px}
.rk-article h2{font-size:29px;font-weight:800;letter-spacing:-.8px;color:#0d0d0d;
  line-height:1.18;margin:48px 0 18px;scroll-margin-top:96px}
.rk-article h3{font-size:19px;font-weight:700;letter-spacing:-.3px;color:#111;
  margin:34px 0 10px;scroll-margin-top:96px}
.rk-article h4{font-size:17px;font-weight:700;color:#111;margin:28px 0 10px}
.rk-article strong{color:#111;font-weight:700}
.rk-article a{color:#111;text-decoration:underline;text-decoration-color:#cfcfcf;
  text-underline-offset:3px;transition:text-decoration-color .18s}
.rk-article a:hover{text-decoration-color:#16a34a}
.rk-article ul,.rk-article ol{margin:0 0 20px;padding-left:22px}
.rk-article li{margin-bottom:9px}
.rk-article li::marker{color:#aaa}
.rk-article hr{border:0;border-top:1px solid #e8e8e8;margin:40px 0}
.rk-article blockquote{margin:30px 0;padding:2px 0 2px 22px;border-left:3px solid #e8a013;
  font-size:18px;line-height:1.6;color:#111}
.rk-article blockquote p:last-child{margin-bottom:0}
.rk-article img{max-width:100%;height:auto;display:block}
/* figures in post content already carry .rk-prose-fig site-wide; this is for bare ones */
.rk-article figure:not(.rk-prose-fig){margin:30px 0}
.rk-article figure:not(.rk-prose-fig) img{border-radius:16px;border:1px solid rgba(0,0,0,.09)}
.rk-article figure:not(.rk-prose-fig) figcaption{font-family:'Geist Mono',monospace;
  font-size:12.5px;color:#888;margin-top:10px;text-align:center}

/* inline code stays inline — block display was breaking sentences */
.rk-article :not(pre) > code{display:inline;white-space:normal;
  font-family:'Geist Mono',ui-monospace,SFMono-Regular,Menlo,monospace;
  font-size:.885em;background:#f3f3f1;border:1px solid #e8e8e8;border-radius:4px;
  padding:.06em .28em;color:#111;word-break:break-word}
.rk-article pre{display:block;background:#0f1115;color:#e8e8e8;border-radius:14px;
  padding:18px 20px;margin:26px 0;overflow-x:auto;
  font-family:'Geist Mono',ui-monospace,SFMono-Regular,Menlo,monospace;
  font-size:14px;line-height:1.65}
.rk-article pre code{display:block;background:none;border:0;padding:0;color:inherit;
  white-space:pre;font-size:inherit}

/* wide tables scroll instead of pushing the page sideways */
.rk-table-responsive{overflow-x:auto;-webkit-overflow-scrolling:touch;margin:26px 0;
  border-radius:14px;box-shadow:0 0 0 1px rgba(0,0,0,.08)}
.rk-article table{width:100%;min-width:520px;border-collapse:collapse;font-size:15px;margin:0}
.rk-article th{background:#fafafa;text-align:left;font-weight:700;color:#111;
  padding:13px 15px;border-bottom:1px solid #e8e8e8}
.rk-article td{padding:13px 15px;border-bottom:1px solid #f0f0ee;color:#4f4f4f;vertical-align:top}
.rk-article tr:last-child td{border-bottom:0}
/* CSS-only fallback if the wrapping script has not run; stops matching once it has */
.rk-article > table{display:block;overflow-x:auto;-webkit-overflow-scrolling:touch}

/* breadcrumb line above the hero */
.rk-single-crumbs{display:flex;flex-wrap:wrap;align-items:center;justify-content:center;
  gap:7px;font-size:13px;color:#888;margin-bottom:20px;line-height:1.5}
.rk-single-crumbs a{color:#888;text-decoration:none}
.rk-single-crumbs a:hover{color:#111}
.rk-single-crumbs .sep{color:#ccc}
/* min-width:0 is load-bearing: a nowrap flex item defaults to min-width:auto and
   would widen the whole page on a phone instead of ellipsing */
.rk-single-crumbs .cur{color:#111;min-width:0;flex:0 1 auto;max-width:100%;
  overflow:hidden;text-overflow:ellipsis;white-space:nowrap}

/* tags */
.rk-post-tags{display:flex;flex-wrap:wrap;gap:8px;margin-top:40px}
.rk-post-tags a{font-family:'Geist Mono',monospace;font-size:11.5px;letter-spacing:.04em;
  color:#666;background:#f4f4f2;border:1px solid rgba(0,0,0,.08);border-radius:100px;
  padding:6px 13px;text-decoration:none;transition:.18s}
.rk-post-tags a:hover{background:#111;color:#fff;border-color:#111}

/* author card reuses .rk-cs-inner (bg #fafafa, radius 20px, padding 40px) */
.rk-author-card{margin-top:48px;display:flex;gap:24px;align-items:flex-start;flex-wrap:wrap}
.rk-author-card img,.rk-author-card .avatar{width:76px;height:76px;border-radius:50%;
  object-fit:cover;flex-shrink:0;background:#eee}
.rk-author-card .bio{flex:1;min-width:250px}
.rk-author-card .bio h2{margin:0 0 8px;font-size:19px;font-weight:700;color:#111;letter-spacing:-.3px}
.rk-author-card .bio p{margin:0 0 16px;font-size:14.5px;line-height:1.7;color:#555}
.rk-author-card .bio .rk-readmore-btn{display:inline-flex;align-items:center;gap:7px;
  background:#111;color:#fff;font-size:13px;font-weight:600;padding:10px 20px;
  border-radius:100px;text-decoration:none;transition:background .2s ease,gap .2s ease}
.rk-author-card .bio .rk-readmore-btn:hover{background:#16a34a;gap:11px}

/* related block */
.rk-single-related{margin-top:66px;padding-top:46px;border-top:1px solid #ececec}
/* .rk-eyebrow is a fit-content pill, so text-align on it cannot self-centre */
.rk-single-related .rk-eyebrow{display:flex;width:fit-content;margin:0 auto 14px}
.rk-single-related h2{font-size:29px;font-weight:700;letter-spacing:-1px;color:#111;
  text-align:center;margin:0 0 30px}

@media(max-width:980px){
  .rk-single-figure{margin-bottom:44px}
  .rk-single-related h2{font-size:25px}
}
@media(max-width:760px){
  .rk-single-main{padding-bottom:56px}
  .rk-article{font-size:16.5px;line-height:1.75}
  .rk-article h2{font-size:24px;margin:38px 0 14px}
  .rk-article h3{font-size:17.5px;margin:28px 0 10px}
  .rk-article blockquote{font-size:16.5px;padding-left:18px}
  .rk-single-figure{margin-bottom:32px}
  .rk-author-card{gap:18px}
  .rk-author-card img,.rk-author-card .avatar{width:60px;height:60px}
  .rk-single-related{margin-top:48px;padding-top:36px}
  .rk-single-related h2{font-size:22px}
}
</style>

<main class="rk-single-main">

<?php
while ( have_posts() ) :
	the_post();

	$rk_cats   = get_the_category();
	$rk_words  = str_word_count( wp_strip_all_tags( strip_shortcodes( get_the_content() ) ) );
	$rk_read   = max( 1, (int) ceil( $rk_words / 200 ) );
	$rk_author = (int) get_the_author_meta( 'ID' );
	?>

	<div class="rk-cs-wrap">

		<header class="rk-cs-hero">

			<nav class="rk-single-crumbs" aria-label="Breadcrumb">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>">Home</a>
				<span class="sep" aria-hidden="true">/</span>
				<a href="<?php echo esc_url( home_url( '/blog/' ) ); ?>">Blog</a>
				<span class="sep" aria-hidden="true">/</span>
				<span class="cur"><?php the_title(); ?></span>
			</nav>

			<?php if ( ! empty( $rk_cats ) ) : ?>
				<span class="rk-cs-eyebrow"><?php echo esc_html( $rk_cats[0]->name ); ?></span>
			<?php endif; ?>

			<h1 class="rk-cs-title"><?php the_title(); ?></h1>

			<?php if ( has_excerpt() ) : ?>
				<p class="rk-cs-tagline"><?php echo esc_html( get_the_excerpt() ); ?></p>
			<?php endif; ?>

			<div class="rk-cs-meta">
				<div>
					<span class="k">Written by</span>
					<span class="v"><?php the_author(); ?></span>
				</div>
				<div>
					<span class="k">Published</span>
					<span class="v">
						<time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date( 'F j, Y' ) ); ?></time>
					</span>
				</div>
				<div>
					<span class="k">Read time</span>
					<span class="v"><?php echo (int) $rk_read; ?> min</span>
				</div>
			</div>

		</header>

		<?php if ( has_post_thumbnail() ) : ?>
			<figure class="rk-cs-figure rk-single-figure">
				<?php the_post_thumbnail( 'full', array( 'sizes' => '(max-width:980px) 100vw, 980px' ) ); ?>
			</figure>
		<?php endif; ?>

		<div class="rk-single-col">

			<div class="rk-article">
				<?php the_content(); ?>
			</div>

			<?php
			$rk_tags = get_the_tags();
			if ( ! empty( $rk_tags ) && ! is_wp_error( $rk_tags ) ) :
				?>
				<div class="rk-post-tags">
					<?php foreach ( $rk_tags as $rk_tag ) : ?>
						<a href="<?php echo esc_url( get_tag_link( $rk_tag->term_id ) ); ?>"><?php echo esc_html( $rk_tag->name ); ?></a>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

			<aside class="rk-cs-inner rk-author-card">
				<?php echo get_avatar( $rk_author, 156 ); ?>
				<div class="bio">
					<h2>Written by <?php the_author(); ?></h2>
					<p>
						Rokibul Islam Shuvo is an SEO consultant and technical SEO lead helping
						brands get found across Google, Bing, ChatGPT and Perplexity. He works
						across technical SEO, semantic SEO, AEO and GEO, WordPress and Shopify
						development, and AI automation.
					</p>
					<a class="rk-readmore-btn" href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">
						Book Strategy Call
						<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
					</a>
				</div>
			</aside>

		</div><!-- /.rk-single-col -->

		<?php
		// --- related posts, rendered as the site's standard blog cards ---
		$rk_related_args = array(
			'post_type'           => 'post',
			'posts_per_page'      => 3,
			'post__not_in'        => array( get_the_ID() ),
			'ignore_sticky_posts' => true,
			'no_found_rows'       => true,
		);
		if ( ! empty( $rk_cats ) ) {
			$rk_related_args['cat'] = (int) $rk_cats[0]->term_id;
		}
		$rk_related = new WP_Query( $rk_related_args );

		if ( $rk_related->have_posts() ) :
			?>
			<section class="rk-single-related">
				<p class="rk-eyebrow">Keep reading</p>
				<h2>More on search visibility</h2>
				<div class="rk-hblog-grid">
					<?php
					while ( $rk_related->have_posts() ) :
						$rk_related->the_post();
						$rk_rcats = get_the_category();
						?>
						<a class="rk-bcard" href="<?php the_permalink(); ?>">
							<?php if ( has_post_thumbnail() ) : ?>
								<div class="thumb"><?php the_post_thumbnail( 'medium_large' ); ?></div>
							<?php endif; ?>
							<div class="body">
								<?php if ( ! empty( $rk_rcats ) ) : ?>
									<span class="rk-post-cat"><?php echo esc_html( $rk_rcats[0]->name ); ?></span>
								<?php endif; ?>
								<h3><?php the_title(); ?></h3>
								<p><?php echo esc_html( wp_trim_words( get_the_excerpt(), 18, '…' ) ); ?></p>
								<div class="rk-post-meta">
									<span><?php echo esc_html( get_the_date( 'M j, Y' ) ); ?></span>
									<span class="rk-readmore">
										Read
										<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
									</span>
								</div>
							</div>
						</a>
						<?php
					endwhile;
					?>
				</div>
			</section>
			<?php
		endif;
		wp_reset_postdata();
		?>

	</div><!-- /.rk-cs-wrap -->

	<script type="application/ld+json">
	<?php
	echo wp_json_encode(
		array(
			'@context'        => 'https://schema.org',
			'@type'           => 'BreadcrumbList',
			'itemListElement' => array(
				array(
					'@type'    => 'ListItem',
					'position' => 1,
					'name'     => 'Home',
					'item'     => home_url( '/' ),
				),
				array(
					'@type'    => 'ListItem',
					'position' => 2,
					'name'     => 'Blog',
					'item'     => home_url( '/blog/' ),
				),
				array(
					'@type'    => 'ListItem',
					'position' => 3,
					'name'     => wp_strip_all_tags( get_the_title() ),
					'item'     => get_permalink(),
				),
			),
		),
		JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
	);
	?>
	</script>

<?php endwhile; ?>

</main>

<script>
/* Wrap article tables so wide ones scroll instead of breaking the column. */
document.addEventListener('DOMContentLoaded', function () {
	document.querySelectorAll('.rk-article table').forEach(function (table) {
		var parent = table.parentElement;
		if (parent && parent.classList.contains('rk-table-responsive')) { return; }
		var wrap = document.createElement('div');
		wrap.className = 'rk-table-responsive';
		table.parentNode.insertBefore(wrap, table);
		wrap.appendChild(table);
	});
});
</script>

<?php
get_footer();
