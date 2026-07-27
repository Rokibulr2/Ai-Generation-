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
 * correct for a file that WordPress requires while rendering a post — and fatal
 * for a file that is included during boot. Placing it in wp-content/mu-plugins/
 * or wp-content/plugins/ makes wp-settings.php include it before the main query
 * has run, and the first wp_head() callback that touches the query object dies
 * with "Call to a member function get_queried_object() on null", taking down the
 * front end AND wp-admin on every request.
 *
 * It belongs in the sandbox directory, where rk-blog-controller.php routes
 * single posts to it via the template hierarchy.
 * ---------------------------------------------------------------------------
 *
 * Self-contained by design: no site-wide stylesheet defines .rk-article,
 * .rk-crumbs or any other class used here, so the styles ship with the markup.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<style id="rk-single-css">
/* ===== RK single post ===== */
.rk-single{--rk-measure:760px;--rk-wide:1040px;
  font-family:'Geist',-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;
  max-width:var(--rk-wide);margin:0 auto;padding:44px 24px 88px;box-sizing:border-box}

/* every text element shares one measure so nothing is left-adrift */
.rk-crumbs,.rk-post-cat,.rk-post-title,.rk-post-dek,.rk-post-meta,
.rk-article,.rk-post-tags,.rk-author-card,.rk-related-head{
  max-width:var(--rk-measure);margin-left:auto;margin-right:auto}

/* --- breadcrumbs --- */
.rk-crumbs{display:flex;flex-wrap:wrap;align-items:center;gap:8px;
  font-size:13.5px;color:#888;margin-bottom:20px;padding:0;line-height:1.5}
.rk-crumbs a{color:#888;text-decoration:none;border-bottom:1px solid transparent;transition:.15s}
.rk-crumbs a:hover{color:#111;border-bottom-color:#e8e8e8}
.rk-crumbs .sep{color:#c8c8c8}
/* min-width:0 is load-bearing: a nowrap flex item defaults to min-width:auto and
   would force the whole page wider than the phone instead of ellipsing. */
.rk-crumbs .cur{color:#111;font-weight:500;min-width:0;flex:0 1 auto;
  max-width:100%;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}

/* --- category chip --- */
.rk-post-cat{margin-bottom:16px}
.rk-post-cat a{display:inline-block;background:#f0fdf4;color:#16a34a;
  font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;
  padding:6px 14px;border-radius:20px;border:1px solid #bbf7d0;text-decoration:none;transition:.15s}
.rk-post-cat a:hover{background:#16a34a;color:#fff;border-color:#16a34a}

/* --- title + dek --- */
.rk-post-title{font-size:44px;line-height:1.14;font-weight:700;color:#111;
  letter-spacing:-1.6px;margin:0 0 18px;overflow-wrap:break-word}
.rk-post-dek{font-size:19px;line-height:1.6;color:#555;margin:0 0 28px;font-weight:400}

/* --- byline --- */
.rk-post-meta{display:flex;align-items:center;gap:13px;
  padding-bottom:26px;margin-bottom:36px;border-bottom:1px solid #e8e8e8}
.rk-post-meta img,.rk-post-meta .avatar{width:46px;height:46px;border-radius:50%;
  object-fit:cover;flex-shrink:0;background:#f0f0ee}
.rk-post-meta .who{display:flex;flex-direction:column;min-width:0}
.rk-post-meta .name{font-size:15.5px;font-weight:600;color:#111;line-height:1.35}
.rk-post-meta .when{font-size:13.5px;color:#888;line-height:1.45;margin-top:2px}
.rk-post-meta .when .dot{margin:0 6px;color:#c8c8c8}

/* --- featured image: never cropped, shown at its own ratio --- */
.rk-post-hero{max-width:var(--rk-wide);margin:0 auto 46px;border-radius:16px;
  overflow:hidden;background:#fafafa;border:1px solid #e8e8e8}
.rk-post-hero img{width:100%;height:auto;display:block}
.rk-post-hero figcaption{font-size:13px;color:#888;padding:12px 16px;
  text-align:center;border-top:1px solid #e8e8e8;background:#fafafa}

/* --- article body --- */
.rk-article{font-size:17.5px;line-height:1.78;color:#333}
.rk-article > *:first-child{margin-top:0}
.rk-article p{margin:0 0 24px}
.rk-article h2{font-size:29px;line-height:1.25;letter-spacing:-.9px;font-weight:700;
  color:#111;margin:52px 0 18px;scroll-margin-top:96px}
.rk-article h3{font-size:22px;line-height:1.3;letter-spacing:-.4px;font-weight:600;
  color:#111;margin:38px 0 14px;scroll-margin-top:96px}
.rk-article h4{font-size:18px;font-weight:600;color:#111;margin:30px 0 12px}
.rk-article a{color:#111;text-decoration:underline;text-decoration-color:#d8d8d8;
  text-underline-offset:3px;transition:.15s}
.rk-article a:hover{text-decoration-color:#e8a013}
.rk-article ul,.rk-article ol{margin:0 0 24px;padding-left:24px}
.rk-article li{margin-bottom:10px}
.rk-article li::marker{color:#888}
.rk-article strong{font-weight:600;color:#111}
.rk-article hr{border:0;border-top:1px solid #e8e8e8;margin:44px 0}
.rk-article blockquote{margin:32px 0;padding:4px 0 4px 24px;
  border-left:3px solid #e8a013;font-size:19px;line-height:1.62;color:#111}
.rk-article blockquote p:last-child{margin-bottom:0}
.rk-article img{max-width:100%;height:auto;display:block;border-radius:12px}
.rk-article figure{margin:32px 0}
.rk-article figure img{margin:0 auto}
.rk-article figcaption{font-size:13.5px;color:#888;margin-top:11px;text-align:center;line-height:1.5}

/* --- code: inline stays inline (block display broke sentences) --- */
.rk-article :not(pre) > code{display:inline;white-space:normal;
  font-family:'Geist Mono',ui-monospace,SFMono-Regular,Menlo,monospace;
  font-size:.885em;background:#f3f3f1;border:1px solid #e8e8e8;border-radius:4px;
  padding:.06em .28em;color:#111;word-break:break-word}
.rk-article pre{display:block;background:#0f1115;color:#e8e8e8;border-radius:12px;
  padding:18px 20px;margin:28px 0;overflow-x:auto;
  font-family:'Geist Mono',ui-monospace,SFMono-Regular,Menlo,monospace;
  font-size:14px;line-height:1.65}
.rk-article pre code{display:block;background:none;border:0;padding:0;
  color:inherit;white-space:pre;font-size:inherit}

/* --- tables scroll instead of blowing out the column --- */
.rk-table-responsive{overflow-x:auto;-webkit-overflow-scrolling:touch;
  margin:28px 0;border:1px solid #e8e8e8;border-radius:12px}
/* CSS-only safety net: if the wrapper script has not run (or is blocked), a bare
   table still scrolls instead of pushing the page sideways. Stops matching the
   moment the script wraps it. */
.rk-article > table{display:block;overflow-x:auto;-webkit-overflow-scrolling:touch}
.rk-article table{width:100%;min-width:520px;border-collapse:collapse;font-size:15px;margin:0}
.rk-article th{background:#fafafa;text-align:left;font-weight:600;color:#111;
  padding:13px 15px;border-bottom:1px solid #e8e8e8}
.rk-article td{padding:13px 15px;border-bottom:1px solid #f0f0ee;color:#333;vertical-align:top}
.rk-article tr:last-child td{border-bottom:0}

/* --- tags --- */
.rk-post-tags{display:flex;flex-wrap:wrap;gap:8px;margin-top:44px}
.rk-post-tags a{font-size:13px;color:#555;background:#f4f4f2;border:1px solid #e8e8e8;
  border-radius:7px;padding:5px 11px;text-decoration:none;transition:.15s}
.rk-post-tags a:hover{background:#111;color:#fff;border-color:#111}

/* --- author E-E-A-T card --- */
.rk-author-card{margin-top:52px;padding:30px;background:#fafafa;border:1px solid #e8e8e8;
  border-radius:16px;display:flex;gap:22px;align-items:flex-start;flex-wrap:wrap}
.rk-author-card img,.rk-author-card .avatar{width:78px;height:78px;border-radius:50%;
  object-fit:cover;flex-shrink:0;background:#f0f0ee}
.rk-author-card .bio{flex:1;min-width:250px}
.rk-author-card h2{margin:0 0 8px;font-size:19px;font-weight:700;color:#111;letter-spacing:-.2px}
.rk-author-card p{margin:0 0 16px;font-size:14.5px;line-height:1.65;color:#555}
.rk-author-card .cta{display:inline-block;background:#16a34a;color:#fff;font-size:13.5px;
  font-weight:600;padding:10px 20px;border-radius:8px;text-decoration:none;transition:.15s}
.rk-author-card .cta:hover{background:#111}

/* --- related --- */
.rk-related{margin-top:66px;padding-top:44px;border-top:1px solid #e8e8e8}
.rk-related-head{font-size:13px;letter-spacing:.14em;text-transform:uppercase;
  color:#888;font-weight:600;margin:0 0 22px}
.rk-related-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:26px;align-items:start}
.rk-rcard{min-width:0}
.rk-rcard a{text-decoration:none;color:inherit;display:block}
.rk-rcard .thumb{min-width:0;border-radius:12px;overflow:hidden;background:#f4f4f2;
  border:1px solid #e8e8e8;margin-bottom:13px}
.rk-rcard .thumb img{width:100%;height:auto;aspect-ratio:16/9;object-fit:cover;display:block}
.rk-rcard h3{font-size:16.5px;line-height:1.4;font-weight:600;color:#111;margin:0;
  letter-spacing:-.2px;transition:.15s}
.rk-rcard a:hover h3{color:#e8a013}
.rk-rcard .when{font-size:12.5px;color:#888;margin-top:7px}

/* --- responsive --- */
@media(max-width:980px){
  .rk-single{padding:32px 20px 68px}
  .rk-post-title{font-size:35px;letter-spacing:-1.1px}
  .rk-post-dek{font-size:17.5px}
  .rk-related-grid{grid-template-columns:repeat(2,1fr);gap:22px}
}
@media(max-width:640px){
  .rk-single{padding:24px 16px 56px}
  .rk-post-title{font-size:27px;letter-spacing:-.7px;line-height:1.2}
  .rk-post-dek{font-size:16.5px;margin-bottom:22px}
  .rk-article{font-size:16.5px;line-height:1.72}
  .rk-article h2{font-size:24px;margin:40px 0 14px;letter-spacing:-.6px}
  .rk-article h3{font-size:19.5px;margin:30px 0 12px}
  .rk-article blockquote{font-size:17px;padding-left:18px}
  .rk-post-meta{gap:11px;padding-bottom:20px;margin-bottom:28px}
  .rk-post-meta img,.rk-post-meta .avatar{width:40px;height:40px}
  .rk-post-hero{margin-bottom:32px;border-radius:12px}
  .rk-author-card{padding:22px;gap:16px}
  .rk-author-card img,.rk-author-card .avatar{width:60px;height:60px}
  .rk-related{margin-top:48px;padding-top:34px}
  .rk-related-grid{grid-template-columns:1fr;gap:24px}
}
</style>

<main class="rk-single">

<?php
while ( have_posts() ) :
	the_post();

	$rk_cats   = get_the_category();
	$rk_words  = str_word_count( wp_strip_all_tags( strip_shortcodes( get_the_content() ) ) );
	$rk_read   = max( 1, (int) ceil( $rk_words / 200 ) );
	$rk_author = (int) get_the_author_meta( 'ID' );
	?>

	<nav class="rk-crumbs" aria-label="Breadcrumb">
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>">Home</a>
		<span class="sep" aria-hidden="true">/</span>
		<a href="<?php echo esc_url( home_url( '/blog/' ) ); ?>">Blog</a>
		<span class="sep" aria-hidden="true">/</span>
		<span class="cur"><?php the_title(); ?></span>
	</nav>

	<?php if ( ! empty( $rk_cats ) ) : ?>
		<div class="rk-post-cat">
			<a href="<?php echo esc_url( get_category_link( $rk_cats[0]->term_id ) ); ?>">
				<?php echo esc_html( $rk_cats[0]->name ); ?>
			</a>
		</div>
	<?php endif; ?>

	<h1 class="rk-post-title"><?php the_title(); ?></h1>

	<?php if ( has_excerpt() ) : ?>
		<p class="rk-post-dek"><?php echo esc_html( get_the_excerpt() ); ?></p>
	<?php endif; ?>

	<div class="rk-post-meta">
		<?php echo get_avatar( $rk_author, 96 ); ?>
		<div class="who">
			<span class="name"><?php the_author(); ?></span>
			<span class="when">
				<time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date( 'F j, Y' ) ); ?></time>
				<span class="dot" aria-hidden="true">&bull;</span><?php echo (int) $rk_read; ?> min read
			</span>
		</div>
	</div>

	<?php if ( has_post_thumbnail() ) : ?>
		<figure class="rk-post-hero">
			<?php the_post_thumbnail( 'full', array( 'sizes' => '(max-width:1080px) 100vw, 1080px' ) ); ?>
			<?php
			$rk_cap = get_the_post_thumbnail_caption();
			if ( $rk_cap ) {
				echo '<figcaption>' . esc_html( $rk_cap ) . '</figcaption>';
			}
			?>
		</figure>
	<?php endif; ?>

	<div class="rk-article">
		<?php the_content(); ?>
	</div>

	<?php
	$rk_tags = get_the_tags();
	if ( ! empty( $rk_tags ) && ! is_wp_error( $rk_tags ) ) :
		?>
		<div class="rk-post-tags">
			<?php foreach ( $rk_tags as $rk_tag ) : ?>
				<a href="<?php echo esc_url( get_tag_link( $rk_tag->term_id ) ); ?>">#<?php echo esc_html( $rk_tag->name ); ?></a>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>

	<aside class="rk-author-card">
		<?php echo get_avatar( $rk_author, 156 ); ?>
		<div class="bio">
			<h2>Written by <?php the_author(); ?></h2>
			<p>
				Rokibul Islam Shuvo is an SEO consultant and technical SEO lead helping brands
				get found across Google, Bing, ChatGPT and Perplexity. He works across technical
				SEO, semantic SEO, AEO and GEO, WordPress and Shopify development, and AI
				automation.
			</p>
			<a class="cta" href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">Book Strategy Call</a>
		</div>
	</aside>

	<?php
	// --- related posts, same primary category ---
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
		<section class="rk-related">
			<p class="rk-related-head">Keep reading</p>
			<div class="rk-related-grid">
				<?php
				while ( $rk_related->have_posts() ) :
					$rk_related->the_post();
					?>
					<article class="rk-rcard">
						<a href="<?php the_permalink(); ?>">
							<?php if ( has_post_thumbnail() ) : ?>
								<div class="thumb"><?php the_post_thumbnail( 'medium_large', array( 'alt' => the_title_attribute( array( 'echo' => false ) ) ) ); ?></div>
							<?php endif; ?>
							<h3><?php the_title(); ?></h3>
							<div class="when"><?php echo esc_html( get_the_date( 'F j, Y' ) ); ?></div>
						</a>
					</article>
					<?php
				endwhile;
				?>
			</div>
		</section>
		<?php
	endif;
	wp_reset_postdata();
	?>

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
