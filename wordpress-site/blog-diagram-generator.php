<?php
/**
 * Diagram generator for the post "Optimizing Jewelry Brands for ChatGPT Search"
 * (post ID 703 on growwithrokibul.com).
 *
 * Renders the four in-article diagrams straight to the uploads directory with GD,
 * overwriting the existing attachment files so their IDs and URLs stay stable.
 *
 * Run inside a WordPress context, e.g.
 *     wp eval-file wordpress-site/blog-diagram-generator.php
 *
 * Every block of text is measured with imagettfbbox before anything is drawn:
 * titles shrink to fit the content column, body copy wraps to its container, and
 * the canvas height is derived from the laid-out content. Nothing is positioned
 * with a hard-coded coordinate that text can outgrow.
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'This script must run inside WordPress.' );
}

/**
 * Small measured-layout drawing surface over GD.
 *
 * All coordinates are given in design units (1200-unit wide page). The canvas is
 * rendered at SCALE times that size and resampled down on save, which is what
 * gives the rounded corners, arrowheads and stars their anti-aliased edges.
 */
class RK_Diagram {

	const SCALE  = 2;
	const WIDTH  = 1200;
	const MARGIN = 56;

	/** @var resource|GdImage */
	private $im;

	/** @var int */
	private $height;

	/** @var array<string,int> */
	private $colors = array();

	/** @var array<string,string> */
	public $font;

	public function __construct() {
		$dir = WP_CONTENT_DIR . '/plugins/google-site-kit/dist/assets/js/fonts/';

		$this->font = array(
			'display' => $dir . 'google-sans-display-medium-b41b7e0618b540110582.ttf',
			'medium'  => $dir . 'google-sans-text-medium-53980445227ada4764a7.ttf',
			'regular' => $dir . 'google-sans-text-regular-0d01ceaea2903216c4ee.ttf',
		);

		foreach ( $this->font as $path ) {
			if ( ! file_exists( $path ) ) {
				throw new RuntimeException( "Missing font: {$path}" );
			}
		}
	}

	public function content_width() {
		return self::WIDTH - 2 * self::MARGIN;
	}

	/* ---------------------------------------------------------------- metrics
	 * These work before the canvas exists, so a layout can be measured in full
	 * and the canvas height computed from the result.
	 */

	/** Width of a string, in design units. */
	public function text_width( $size, $font, $string ) {
		if ( '' === $string ) {
			return 0;
		}
		$box = imagettfbbox( $size * self::SCALE, 0, $font, $string );

		return ( $box[2] - $box[0] ) / self::SCALE;
	}

	/** Largest size in [$min,$max] at which $string fits inside $max_width. */
	public function fit_size( $string, $font, $max_width, $max, $min ) {
		for ( $size = $max; $size >= $min; $size -= 0.5 ) {
			if ( $this->text_width( $size, $font, $string ) <= $max_width ) {
				return $size;
			}
		}

		return $min;
	}

	/** Greedy word wrap. Returns an array of lines. */
	public function wrap( $string, $size, $font, $max_width ) {
		$words = preg_split( '/\s+/u', trim( $string ) );
		$lines = array();
		$line  = '';

		foreach ( $words as $word ) {
			$candidate = ( '' === $line ) ? $word : $line . ' ' . $word;

			if ( '' === $line || $this->text_width( $size, $font, $candidate ) <= $max_width ) {
				$line = $candidate;
			} else {
				$lines[] = $line;
				$line    = $word;
			}
		}

		if ( '' !== $line ) {
			$lines[] = $line;
		}

		return $lines;
	}

	/* ---------------------------------------------------------------- canvas */

	public function begin( $height ) {
		$this->height = (int) round( $height );
		$this->im     = imagecreatetruecolor( self::WIDTH * self::SCALE, $this->height * self::SCALE );
		imagealphablending( $this->im, true );
		$this->fill_rect( 0, 0, self::WIDTH, $this->height, $this->color( '#FFFFFF' ) );
	}

	public function color( $hex ) {
		$hex = ltrim( $hex, '#' );

		if ( ! isset( $this->colors[ $hex ] ) ) {
			$this->colors[ $hex ] = imagecolorallocate(
				$this->im,
				hexdec( substr( $hex, 0, 2 ) ),
				hexdec( substr( $hex, 2, 2 ) ),
				hexdec( substr( $hex, 4, 2 ) )
			);
		}

		return $this->colors[ $hex ];
	}

	public function fill_rect( $x, $y, $w, $h, $color ) {
		$s = self::SCALE;
		imagefilledrectangle(
			$this->im,
			(int) round( $x * $s ),
			(int) round( $y * $s ),
			(int) round( ( $x + $w ) * $s ) - 1,
			(int) round( ( $y + $h ) * $s ) - 1,
			$color
		);
	}

	public function round_rect( $x, $y, $w, $h, $r, $color ) {
		$s = self::SCALE;
		$x *= $s;
		$y *= $s;
		$w *= $s;
		$h *= $s;
		$r *= $s;

		imagefilledrectangle( $this->im, (int) ( $x + $r ), (int) $y, (int) ( $x + $w - $r ), (int) ( $y + $h ), $color );
		imagefilledrectangle( $this->im, (int) $x, (int) ( $y + $r ), (int) ( $x + $w ), (int) ( $y + $h - $r ), $color );

		$d = (int) ( $r * 2 );
		imagefilledellipse( $this->im, (int) ( $x + $r ), (int) ( $y + $r ), $d, $d, $color );
		imagefilledellipse( $this->im, (int) ( $x + $w - $r ), (int) ( $y + $r ), $d, $d, $color );
		imagefilledellipse( $this->im, (int) ( $x + $r ), (int) ( $y + $h - $r ), $d, $d, $color );
		imagefilledellipse( $this->im, (int) ( $x + $w - $r ), (int) ( $y + $h - $r ), $d, $d, $color );
	}

	/** A 1px-bordered rounded card. */
	public function card( $x, $y, $w, $h, $r, $bg, $border ) {
		$this->round_rect( $x, $y, $w, $h, $r, $border );
		$this->round_rect( $x + 1, $y + 1, $w - 2, $h - 2, max( 0, $r - 1 ), $bg );
	}

	public function ellipse( $cx, $cy, $d, $color ) {
		$s = self::SCALE;
		imagefilledellipse( $this->im, (int) round( $cx * $s ), (int) round( $cy * $s ), (int) ( $d * $s ), (int) ( $d * $s ), $color );
	}

	public function polygon( array $points, $color ) {
		$scaled = array();

		foreach ( $points as $p ) {
			$scaled[] = (int) round( $p * self::SCALE );
		}

		imagefilledpolygon( $this->im, $scaled, $color );
	}

	/**
	 * Draw text on a baseline. $align is 'l', 'c' or 'r'.
	 * Returns the advance width so runs can be chained.
	 */
	public function text( $x, $y, $size, $font, $color, $string, $align = 'l' ) {
		$w = $this->text_width( $size, $font, $string );

		if ( 'c' === $align ) {
			$x -= $w / 2;
		} elseif ( 'r' === $align ) {
			$x -= $w;
		}

		imagettftext(
			$this->im,
			$size * self::SCALE,
			0,
			(int) round( $x * self::SCALE ),
			(int) round( $y * self::SCALE ),
			$color,
			$font,
			$string
		);

		return $w;
	}

	/** Five-pointed star, used for the review rating row. */
	public function star( $cx, $cy, $r, $color ) {
		$points = array();

		for ( $i = 0; $i < 10; $i++ ) {
			$angle  = -M_PI / 2 + $i * M_PI / 5;
			$radius = ( 0 === $i % 2 ) ? $r : $r * 0.42;

			$points[] = $cx + cos( $angle ) * $radius;
			$points[] = $cy + sin( $angle ) * $radius;
		}

		$this->polygon( $points, $color );
	}

	/** Right-pointing arrowhead with its tip at ($x,$y). */
	public function arrow_right( $x, $y, $size, $color ) {
		$this->polygon(
			array( $x - $size, $y - $size / 2, $x, $y, $x - $size, $y + $size / 2 ),
			$color
		);
	}

	/** Up-pointing arrowhead with its tip at ($x,$y). */
	public function arrow_up( $x, $y, $size, $color ) {
		$this->polygon(
			array( $x - $size / 2, $y + $size, $x + $size / 2 + 1, $y + $size, $x + 0.5, $y ),
			$color
		);
	}

	/** Green confirmation tick, drawn from the baseline at ($x,$y). */
	public function check( $x, $y, $color = '#1E8E3E' ) {
		$s   = self::SCALE;
		$col = $this->color( $color );

		imagesetthickness( $this->im, (int) ( 2.4 * $s ) );
		imageline( $this->im, (int) ( $x * $s ), (int) ( ( $y - 6 ) * $s ), (int) ( ( $x + 4 ) * $s ), (int) ( ( $y - 2 ) * $s ), $col );
		imageline( $this->im, (int) ( ( $x + 4 ) * $s ), (int) ( ( $y - 2 ) * $s ), (int) ( ( $x + 12 ) * $s ), (int) ( ( $y - 11 ) * $s ), $col );
		imagesetthickness( $this->im, 1 );
	}

	/** Title + subtitle + hairline. Returns the y the body content starts at. */
	public function header( $title, $subtitle, $gap = 30 ) {
		$cw    = $this->content_width();
		$size  = $this->fit_size( $title, $this->font['display'], $cw - 8, 34, 22 );
		$lines = $this->wrap( $subtitle, 15, $this->font['regular'], $cw );

		$y = 44 + $size;
		$this->text( self::MARGIN, $y, $size, $this->font['display'], $this->color( '#111111' ), $title );

		$y += 26;
		foreach ( $lines as $line ) {
			$this->text( self::MARGIN, $y, 15, $this->font['regular'], $this->color( '#5F6368' ), $line );
			$y += 20;
		}

		// Back off the trailing line advance, then clear the baseline before the rule.
		$y += -20 + 16;
		$this->fill_rect( self::MARGIN, $y, $cw, 1, $this->color( '#E4E4E4' ) );

		return $y + $gap;
	}

	/**
	 * Height the header will occupy, so a caller can size the canvas up front.
	 * Mirrors header() exactly.
	 */
	public function header_height( $title, $subtitle, $gap = 30 ) {
		$cw    = $this->content_width();
		$size  = $this->fit_size( $title, $this->font['display'], $cw - 8, 34, 22 );
		$lines = $this->wrap( $subtitle, 15, $this->font['regular'], $cw );

		return 44 + $size + 26 + count( $lines ) * 20 - 4 + $gap;
	}

	/** The black pull-quote bar plus the site watermark under it. */
	public function footer( $y, $callout ) {
		$cw    = $this->content_width();
		$lines = $this->wrap( $callout, 16, $this->font['medium'], $cw - 64 );
		$bar_h = max( 58, count( $lines ) * 24 + 34 );

		$this->round_rect( self::MARGIN, $y, $cw, $bar_h, 8, $this->color( '#0A0A0A' ) );
		$this->round_rect( self::MARGIN, $y, 5, $bar_h, 2.5, $this->color( '#E8A013' ) );

		$ty = $y + ( ( $bar_h - count( $lines ) * 24 ) / 2 ) + 17;
		foreach ( $lines as $line ) {
			$this->text( self::MARGIN + 28, $ty, 16, $this->font['medium'], $this->color( '#FFFFFF' ), $line );
			$ty += 24;
		}

		$mark     = 'growwithrokibul.com';
		$baseline = $y + $bar_h + 34;
		$right    = self::WIDTH - self::MARGIN;

		$this->ellipse( $right - $this->text_width( 13, $this->font['regular'], $mark ) - 12, $baseline - 4, 6, $this->color( '#E8A013' ) );
		$this->text( $right, $baseline, 13, $this->font['regular'], $this->color( '#5F6368' ), $mark, 'r' );
	}

	/** Height of footer() including the watermark and bottom padding. */
	public function footer_height( $callout ) {
		$lines = $this->wrap( $callout, 16, $this->font['medium'], $this->content_width() - 64 );

		return max( 58, count( $lines ) * 24 + 34 ) + 34 + 18;
	}

	/** Resample down to 1x and write the PNG. */
	public function save( $path ) {
		$out = imagecreatetruecolor( self::WIDTH, $this->height );
		imagecopyresampled(
			$out, $this->im,
			0, 0, 0, 0,
			self::WIDTH, $this->height,
			self::WIDTH * self::SCALE, $this->height * self::SCALE
		);

		imagepng( $out, $path, 9 );
		imagedestroy( $out );
		imagedestroy( $this->im );

		return filesize( $path );
	}
}

/* ====================================================================== data */

function rk_diagram_uploads_dir() {
	$dir = wp_upload_dir();

	return $dir['basedir'] . '/2026/07/';
}

/* ============================================ 1. traditional vs AI discovery */

function rk_diagram_search_comparison( RK_Diagram $d ) {
	$title   = 'Traditional Organic Search vs. Conversational AI Search';
	$sub     = 'How a high-intent jewelry buyer moves from first query to purchase decision in each discovery model.';
	$callout = 'If your specifications, pricing and policies are not machine-readable, your brand is absent from the consideration set.';

	$traditional = array(
		array( 'Types a short keyword query', '“18k gold engagement ring”' ),
		array( 'Scans a page of ten blue links', 'Ads, collections, marketplaces and review sites compete for the click' ),
		array( 'Opens six to ten tabs', 'Manual cross-checking of specs, price, sizing and return terms' ),
		array( 'Self-directed evaluation', 'Buyer assembles their own shortlist across several sessions' ),
		array( 'Purchase decision', 'Brand wins on ranking position plus on-site persuasion' ),
	);

	$generative = array(
		array( 'States full intent in one prompt', '“Ethically sourced 18k emerald ring under $3,000”' ),
		array( 'Model decomposes the request', 'Budget, metal, stone, occasion and policy become separate constraints' ),
		array( 'Retrieval across index and live crawl', 'Only machine-readable product data qualifies for consideration' ),
		array( 'Three to five cited options returned', 'One synthesized answer — there is no second page to rank on' ),
		array( 'Purchase decision', 'Brand wins by being inside the generated shortlist' ),
	);

	$m    = RK_Diagram::MARGIN;
	$cw   = $d->content_width();
	$colw = ( $cw - 32 ) / 2;
	$cols = array( $m, $m + $colw + 32 );

	$text_x = 44;
	$text_w = $colw - $text_x - 18;
	$body_lh = 17;

	// Measure every step so the paired rows share a height and nothing overflows.
	$wrapped = array();
	$row_h   = array();

	for ( $i = 0; $i < 5; $i++ ) {
		$h = 0;

		foreach ( array( 0, 1 ) as $col ) {
			$source = $col ? $generative : $traditional;

			$heading = $d->wrap( $source[ $i ][0], 15, $d->font['medium'], $text_w );
			$body    = $d->wrap( $source[ $i ][1], 12.5, $d->font['regular'], $text_w );

			$wrapped[ $col ][ $i ] = array( $heading, $body );

			$h = max( $h, 16 + count( $heading ) * 20 + 4 + count( $body ) * $body_lh + 14 );
		}

		$row_h[ $i ] = max( 66, $h );
	}

	$header_h = $d->header_height( $title, $sub );
	$head_y   = $header_h;
	$head_box = 58;
	$steps_y  = $head_y + $head_box + 18;

	$y = $steps_y;
	foreach ( $row_h as $h ) {
		$y += $h + 22;
	}
	$y -= 22;

	$bar_y = $y + 34;
	$d->begin( $bar_y + $d->footer_height( $callout ) );
	$d->header( $title, $sub );

	$ink   = $d->color( '#111111' );
	$body  = $d->color( '#3C4043' );
	$muted = $d->color( '#5F6368' );
	$line  = $d->color( '#E4E4E4' );
	$white = $d->color( '#FFFFFF' );
	$gold  = $d->color( '#E8A013' );
	$grey  = $d->color( '#CFCFCF' );

	foreach ( array( 0, 1 ) as $col ) {
		$x     = $cols[ $col ];
		$is_ai = ( 1 === $col );

		$d->card(
			$x, $head_y, $colw, $head_box, 10,
			$is_ai ? $d->color( '#FFF8EA' ) : $d->color( '#FAFAFA' ),
			$is_ai ? $d->color( '#F0D9A8' ) : $d->color( '#E8E8E8' )
		);

		$d->text(
			$x + $colw / 2, $head_y + 25, 13.5, $d->font['medium'],
			$is_ai ? $d->color( '#9A6B0A' ) : $body,
			$is_ai ? 'CONVERSATIONAL AI SEARCH' : 'TRADITIONAL ORGANIC SEARCH',
			'c'
		);

		$d->text(
			$x + $colw / 2, $head_y + 44, 12, $d->font['regular'], $muted,
			$is_ai ? 'Model does the comparison work' : 'Buyer does the comparison work',
			'c'
		);

		$cy = $steps_y;

		for ( $i = 0; $i < 5; $i++ ) {
			$h = $row_h[ $i ];

			$d->card( $x, $cy, $colw, $h, 10, $white, $line );
			$d->round_rect( $x, $cy, 3.5, $h, 1.5, $is_ai ? $gold : $d->color( '#BDBDBD' ) );

			$d->ellipse( $x + 24, $cy + 30, 27, $is_ai ? $d->color( '#FFF8EA' ) : $d->color( '#FAFAFA' ) );
			$d->text( $x + 24, $cy + 34, 11, $d->font['medium'], $is_ai ? $d->color( '#9A6B0A' ) : $muted, sprintf( '%02d', $i + 1 ), 'c' );

			$ty = $cy + 31;
			foreach ( $wrapped[ $col ][ $i ][0] as $l ) {
				$d->text( $x + $text_x, $ty, 15, $d->font['medium'], $ink, $l );
				$ty += 20;
			}

			$ty += 2;
			foreach ( $wrapped[ $col ][ $i ][1] as $l ) {
				$d->text( $x + $text_x, $ty, 12.5, $d->font['regular'], $body, $l );
				$ty += $body_lh;
			}

			if ( $i < 4 ) {
				$ax = $x + $colw / 2;
				$ay = $cy + $h + 6;
				$d->fill_rect( $ax, $ay, 1, 7, $grey );
				$d->polygon( array( $ax - 3, $ay + 7, $ax + 4, $ay + 7, $ax + 0.5, $ay + 11 ), $grey );
			}

			$cy += $h + 22;
		}
	}

	$d->footer( $bar_y, $callout );

	return $d->save( rk_diagram_uploads_dir() . 'search_comparison_discovery.png' );
}

/* ================================================ 2. RAG retrieval pipeline */

function rk_diagram_rag_pipeline( RK_Diagram $d ) {
	$title   = 'How ChatGPT Search Retrieves and Recommends Jewelry Products';
	$sub     = 'Retrieval-augmented generation: the path from a buyer’s prompt to a cited product recommendation.';
	$callout = 'Retrieval happens before generation — a page that cannot be fetched and parsed never reaches the ranking stage at all.';

	$stages = array(
		array( 'USER PROMPT', 'A single natural-language request carrying budget, material, occasion and policy constraints at once.' ),
		array( 'QUERY FAN-OUT', 'The model rewrites one prompt into several targeted sub-queries and runs them in parallel.' ),
		array( 'RETRIEVAL', 'Candidates are pulled from the search index and from live fetches of your product URLs.' ),
		array( 'RE-RANKING', 'Candidates are scored on specificity, freshness, entity match and independent trust signals.' ),
		array( 'SYNTHESIS & CITATION', 'A shortlist is written into one answer, each pick linked back to its source page.' ),
	);

	$supply = array(
		'Intent-shaped titles, H1s and 40–60 word summaries',
		'Attribute-level coverage: metal, carat, cut, setting, sizing',
		'Crawler access for OAI-SearchBot and ChatGPT-User, fast TTFB',
		'Ratings, review volume and third-party editorial mentions',
		'Product, Offer and return-policy JSON-LD for the citation',
	);

	$m    = RK_Diagram::MARGIN;
	$cw   = $d->content_width();
	$gap  = 18;
	$colw = ( $cw - 4 * $gap ) / 5;
	$pad  = 16;
	$inw  = $colw - 2 * $pad;

	// Labels and bodies are locked to shared offsets so all five cards align,
	// even though "SYNTHESIS & CITATION" wraps to two lines and the others don't.
	$measured  = array();
	$max_label = 1;
	$max_body  = 1;

	foreach ( $stages as $i => $stage ) {
		$label = $d->wrap( $stage[0], 12.5, $d->font['medium'], $inw );
		$body  = $d->wrap( $stage[1], 12, $d->font['regular'], $inw );

		$measured[ $i ] = array( $label, $body );
		$max_label      = max( $max_label, count( $label ) );
		$max_body       = max( $max_body, count( $body ) );
	}

	$label_off = $pad + 24 + 14;
	$rule_off  = $label_off + $max_label * 16 + 6;
	$body_off  = $rule_off + 20;
	$card_h    = $body_off + $max_body * 16.5 + $pad - 4;

	$supply_lines = array();
	$supply_h     = 0;

	foreach ( $supply as $i => $s ) {
		$lines              = $d->wrap( $s, 12, $d->font['regular'], $inw - 14 );
		$supply_lines[ $i ] = $lines;
		$supply_h           = max( $supply_h, count( $lines ) * 16.5 );
	}

	$panel_h = 20 + 18 + 14 + $supply_h + 20;

	$card_y  = $d->header_height( $title, $sub, 32 );
	$panel_y = $card_y + $card_h + 46;
	$bar_y   = $panel_y + $panel_h + 34;

	$d->begin( $bar_y + $d->footer_height( $callout ) );
	$d->header( $title, $sub, 32 );

	$ink    = $d->color( '#111111' );
	$body_c = $d->color( '#3C4043' );
	$line   = $d->color( '#E4E4E4' );
	$white  = $d->color( '#FFFFFF' );
	$gold   = $d->color( '#E8A013' );
	$gold_l = $d->color( '#F0D9A8' );
	$grey   = $d->color( '#B8B8B8' );

	for ( $i = 0; $i < 5; $i++ ) {
		$x    = $m + $i * ( $colw + $gap );
		$last = ( 4 === $i );

		$d->card( $x, $card_y, $colw, $card_h, 10, $last ? $d->color( '#FFF8EA' ) : $white, $last ? $gold_l : $line );
		$d->round_rect( $x, $card_y, $colw, 3.5, 1.5, $last ? $gold : $ink );

		$d->text( $x + $pad, $card_y + $pad + 24, 26, $d->font['display'], $last ? $gold : $d->color( '#D2D2D2' ), sprintf( '%02d', $i + 1 ) );

		$ty = $card_y + $label_off;
		foreach ( $measured[ $i ][0] as $l ) {
			$d->text( $x + $pad, $ty, 12.5, $d->font['medium'], $last ? $d->color( '#9A6B0A' ) : $ink, $l );
			$ty += 16;
		}

		$d->fill_rect( $x + $pad, $card_y + $rule_off, 22, 2, $last ? $gold : $d->color( '#DADADA' ) );

		$ty = $card_y + $body_off;
		foreach ( $measured[ $i ][1] as $l ) {
			$d->text( $x + $pad, $ty, 12, $d->font['regular'], $body_c, $l );
			$ty += 16.5;
		}

		if ( ! $last ) {
			$ax = $x + $colw + $gap / 2;
			$ay = $card_y + $card_h / 2;
			$d->fill_rect( $ax - 6, $ay, 8, 1.5, $grey );
			$d->arrow_right( $ax + 7, $ay + 0.75, 5, $grey );
		}

		// Dashed feed line from the requirements panel back up into the stage.
		$cx = $x + $colw / 2;
		$y1 = $card_y + $card_h + 8;

		for ( $dy = $panel_y - 8; $dy > $y1 + 6; $dy -= 8 ) {
			$d->fill_rect( $cx, $dy - 4, 1.5, 4, $gold_l );
		}

		$d->arrow_up( $cx, $y1, 6, $gold );
	}

	$d->card( $m, $panel_y, $cw, $panel_h, 10, $d->color( '#FAFAFA' ), $d->color( '#EAEAEA' ) );
	$d->text( $m + $pad + 2, $panel_y + 30, 13, $d->font['medium'], $ink, 'WHAT YOUR PRODUCT PAGES MUST SUPPLY AT EACH STAGE' );
	$d->fill_rect( $m + $pad + 2, $panel_y + 44, $cw - 2 * $pad - 4, 1, $line );

	for ( $i = 0; $i < 5; $i++ ) {
		$x  = $m + $i * ( $colw + $gap ) + $pad;
		$ty = $panel_y + 70;

		$d->ellipse( $x + 3, $ty - 4, 5, $gold );

		foreach ( $supply_lines[ $i ] as $l ) {
			$d->text( $x + 14, $ty, 12, $d->font['regular'], $body_c, $l );
			$ty += 16.5;
		}
	}

	$d->footer( $bar_y, $callout );

	return $d->save( rk_diagram_uploads_dir() . 'rag_retrieval_pipeline.png' );
}

/* ============================================== 3. on-page zone architecture */

function rk_diagram_onpage_architecture( RK_Diagram $d ) {
	$title   = 'Information Architecture of an AI-Optimized Jewelry Product Page';
	$sub     = 'Each zone is a distinct retrieval target. The left column is DOM order; the right column is what a generative parser lifts out of it.';
	$callout = 'Attributes buried inside marketing prose cannot be parsed into a recommendation — structure is what makes them retrievable.';

	$rows = array(
		array( 'H1 — PRODUCT TITLE', '1.20ct Emerald-Cut Diamond Ring — 18K White Gold', 'Product entity and primary material resolved from a single string.' ),
		array( 'ANSWER SUMMARY', '40–60 words placed directly under the H1, written to be quoted.', 'A clean extractive snippet the model quotes rather than paraphrasing your marketing copy.' ),
		array( 'SPECIFICATION TABLE', 'Metal · Purity · Carat · Cut · Clarity · Colour · Setting · Band width', 'Attribute-value pairs that satisfy constraint filters such as “under 1.5ct” or “18k only”.' ),
		array( 'MATERIALS & PROVENANCE', 'Sourcing statement plus the GIA / IGI certificate number for the centre stone.', 'Provenance and certification signals that answer ethical-sourcing prompts.' ),
		array( 'SIZING & FIT', 'Ring size chart, resizing window and band-width guidance.', 'Fit answers resolved on-page, so the model has no reason to send the buyer elsewhere.' ),
		array( 'FAQ BLOCK', 'Question-shaped H3s answering the follow-ups a buyer asks next.', 'Q&A pairs that map one-to-one onto conversational follow-up prompts.' ),
		array( 'VERIFIED REVIEWS', 'Aggregate rating, review count and dated customer photographs.', 'Independent trust signal, weighted heavily during the re-ranking stage.' ),
		array( 'RETURNS, WARRANTY & SHIPPING', '30-day window, free resizing, insured delivery, lifetime warranty.', 'Policy terms surfaced inside the recommendation card itself.' ),
	);

	$m       = RK_Diagram::MARGIN;
	$cw      = $d->content_width();
	$spine_x = $m + 2;
	$card_x  = $m + 22;
	$card_w  = 500;
	$right_x = $m + 580;
	$right_w = $cw - 580;
	$pad     = 14;
	$inw     = $card_w - $pad - 16;

	$measured = array();
	$row_h    = array();

	foreach ( $rows as $i => $row ) {
		$left  = $d->wrap( $row[1], 14, $d->font['regular'], $inw );
		$right = $d->wrap( $row[2], 13.5, $d->font['regular'], $right_w - 20 );

		$measured[ $i ] = array( $left, $right );
		$row_h[ $i ]    = max( 62, $pad + 13 + 8 + count( $left ) * 19 + $pad, count( $right ) * 19 + 8 );
	}

	$header_h  = $d->header_height( $title, $sub, 26 );
	$head_base = $header_h;
	$head_rule = $head_base + 12;
	$rows_y    = $head_rule + 26;

	$y = $rows_y;
	foreach ( $row_h as $h ) {
		$y += $h + 14;
	}
	$y -= 14;

	$rows_end = $y;
	$bar_y    = $y + 38;

	$d->begin( $bar_y + $d->footer_height( $callout ) );
	$d->header( $title, $sub, 26 );

	$ink     = $d->color( '#111111' );
	$body_c  = $d->color( '#3C4043' );
	$line    = $d->color( '#E4E4E4' );
	$white   = $d->color( '#FFFFFF' );
	$gold    = $d->color( '#E8A013' );
	$gold_l  = $d->color( '#F0D9A8' );
	$gold_t  = $d->color( '#9A6B0A' );

	$d->text( $card_x, $head_base, 13, $d->font['medium'], $ink, 'PAGE STRUCTURE, IN DOM ORDER' );
	$d->text( $right_x, $head_base, 13, $d->font['medium'], $gold_t, 'WHAT THE GENERATIVE PARSER EXTRACTS' );
	$d->fill_rect( $card_x, $head_rule, $card_w, 1, $d->color( '#DCDCDC' ) );
	$d->fill_rect( $right_x, $head_rule, $right_w, 1, $gold_l );

	$d->fill_rect( $spine_x, $rows_y + 10, 1.5, $rows_end - $rows_y - 20, $d->color( '#E8E8E8' ) );

	$cy = $rows_y;

	foreach ( $rows as $i => $row ) {
		$h = $row_h[ $i ];

		$d->card( $card_x, $cy, $card_w, $h, 8, $white, $line );
		$d->round_rect( $card_x, $cy, 3.5, $h, 1.5, $ink );

		$d->text( $card_x + $pad + 8, $cy + $pad + 11, 11.5, $d->font['medium'], $gold_t, $row[0] );

		$ty = $cy + $pad + 32;
		foreach ( $measured[ $i ][0] as $l ) {
			$d->text( $card_x + $pad + 8, $ty, 14, $d->font['regular'], $ink, $l );
			$ty += 19;
		}

		$mid = $cy + $h / 2;

		$d->ellipse( $spine_x + 0.5, $mid, 9, $white );
		$d->ellipse( $spine_x + 0.5, $mid, 8, $gold );
		$d->ellipse( $spine_x + 0.5, $mid, 3.5, $white );

		for ( $dx = $card_x + $card_w + 8; $dx < $right_x - 14; $dx += 9 ) {
			$d->fill_rect( $dx, $mid - 0.75, 5, 1.5, $gold_l );
		}

		$d->ellipse( $right_x - 8, $mid, 5, $gold );

		$ry = $cy + ( $h - count( $measured[ $i ][1] ) * 19 ) / 2 + 14;
		foreach ( $measured[ $i ][1] as $l ) {
			$d->text( $right_x + 4, $ry, 13.5, $d->font['regular'], $body_c, $l );
			$ry += 19;
		}

		$cy += $h + 14;
	}

	$d->footer( $bar_y, $callout );

	return $d->save( rk_diagram_uploads_dir() . 'onpage_spec_architecture.png' );
}

/* =========================================== 4. JSON-LD to recommendation card */

function rk_diagram_schema_mapping( RK_Diagram $d ) {
	$title   = 'From JSON-LD Markup to the Generative Recommendation Card';
	$sub     = 'Structured fields on the left are the same fields the model repeats back to the buyer. Anything unmarked is left to inference.';
	$callout = 'Mark up price, material, rating and return window explicitly — these are the fields a generative answer quotes back to the buyer.';
	$quote   = '“Based on your budget and preference for 18k gold, this is the strongest match I found:”';
	$note    = 'Every field above is repeated from the markup — none of it was inferred from marketing copy.';

	// Each line is [indent, [[text, token type], ...], mapping key].
	// Token types: k = key, s = string, n = number, p = punctuation.
	$json = array(
		array( 0, array( array( '{', 'p' ) ), '' ),
		array( 1, array( array( '"@context"', 'k' ), array( ': ', 'p' ), array( '"https://schema.org"', 's' ), array( ',', 'p' ) ), '' ),
		array( 1, array( array( '"@type"', 'k' ), array( ': ', 'p' ), array( '"Product"', 's' ), array( ',', 'p' ) ), '' ),
		array( 1, array( array( '"name"', 'k' ), array( ': ', 'p' ), array( '"1.20ct Emerald-Cut Diamond Ring"', 's' ), array( ',', 'p' ) ), 'name' ),
		array( 1, array( array( '"material"', 'k' ), array( ': ', 'p' ), array( '"18K White Gold"', 's' ), array( ',', 'p' ) ), 'material' ),
		array( 1, array( array( '"brand"', 'k' ), array( ': { ', 'p' ), array( '"name"', 'k' ), array( ': ', 'p' ), array( '"Aurelis"', 's' ), array( ' },', 'p' ) ), '' ),
		array( 1, array( array( '"offers"', 'k' ), array( ': {', 'p' ) ), '' ),
		array( 2, array( array( '"@type"', 'k' ), array( ': ', 'p' ), array( '"Offer"', 's' ), array( ',', 'p' ) ), '' ),
		array( 2, array( array( '"price"', 'k' ), array( ': ', 'p' ), array( '"2840.00"', 's' ), array( ',', 'p' ) ), 'price' ),
		array( 2, array( array( '"priceCurrency"', 'k' ), array( ': ', 'p' ), array( '"USD"', 's' ) ), '' ),
		array( 1, array( array( '},', 'p' ) ), '' ),
		array( 1, array( array( '"aggregateRating"', 'k' ), array( ': {', 'p' ) ), '' ),
		array( 2, array( array( '"ratingValue"', 'k' ), array( ': ', 'p' ), array( '"4.8"', 's' ), array( ',', 'p' ) ), 'rating' ),
		array( 2, array( array( '"reviewCount"', 'k' ), array( ': ', 'p' ), array( '"213"', 's' ) ), '' ),
		array( 1, array( array( '},', 'p' ) ), '' ),
		array( 1, array( array( '"hasMerchantReturnPolicy"', 'k' ), array( ': {', 'p' ) ), '' ),
		array( 2, array( array( '"merchantReturnDays"', 'k' ), array( ': ', 'p' ), array( '30', 'n' ), array( ',', 'p' ) ), 'returns' ),
		array( 2, array( array( '"returnPolicyCategory"', 'k' ), array( ': ', 'p' ), array( '"FiniteReturnWindow"', 's' ) ), '' ),
		array( 1, array( array( '}', 'p' ) ), '' ),
		array( 0, array( array( '}', 'p' ) ), '' ),
	);

	$m      = RK_Diagram::MARGIN;
	$cw     = $d->content_width();
	$code_x = $m;
	$code_w = 520;
	$card_x = $m + 624;
	$card_w = $cw - 624;

	$code_lh = 22;
	$code_h  = 44 + 16 + count( $json ) * $code_lh + 18;

	$quote_lines = $d->wrap( $quote, 14, $d->font['regular'], $card_w - 44 );
	$title_lines = $d->wrap( '1.20ct Emerald-Cut Diamond Ring', 17, $d->font['medium'], $card_w - 72 );
	$note_lines  = $d->wrap( $note, 12.5, $d->font['regular'], $card_w - 44 );

	$inner_h = 18 + count( $title_lines ) * 23 + 10 + 20 + 34 + 28 + 26 + 16 + 34;
	$right_h = 64 + 14 + count( $quote_lines ) * 20 + 16 + $inner_h + 16 + count( $note_lines ) * 18 + 22;

	$body_y = $d->header_height( $title, $sub, 28 );
	$bar_y  = $body_y + max( $code_h, $right_h ) + 38;

	$d->begin( $bar_y + $d->footer_height( $callout ) );
	$d->header( $title, $sub, 28 );

	$ink    = $d->color( '#111111' );
	$body_c = $d->color( '#3C4043' );
	$muted  = $d->color( '#5F6368' );
	$line   = $d->color( '#E4E4E4' );
	$white  = $d->color( '#FFFFFF' );
	$gold   = $d->color( '#E8A013' );
	$gold_t = $d->color( '#9A6B0A' );

	// --- code panel ---
	$d->round_rect( $code_x, $body_y, $code_w, $code_h, 10, $d->color( '#16181B' ) );
	$d->round_rect( $code_x, $body_y, $code_w, 44, 10, $d->color( '#22262B' ) );
	$d->fill_rect( $code_x, $body_y + 34, $code_w, 10, $d->color( '#22262B' ) );

	foreach ( array( '#FF5F57', '#FEBC2E', '#28C840' ) as $i => $dot ) {
		$d->ellipse( $code_x + 20 + $i * 17, $body_y + 22, 9, $d->color( $dot ) );
	}

	$d->text( $code_x + 76, $body_y + 27, 12.5, $d->font['medium'], $d->color( '#9AA0A6' ), 'product.jsonld' );

	$tokens = array(
		'k' => $d->color( '#F2A365' ),
		's' => $d->color( '#9BD4A0' ),
		'n' => $d->color( '#A8C7FA' ),
		'p' => $d->color( '#9AA0A6' ),
	);

	$source_y = array();
	$ly       = $body_y + 73;

	foreach ( $json as $row ) {
		$x = $code_x + 22 + $row[0] * 15;

		foreach ( $row[1] as $segment ) {
			$x += $d->text( $x, $ly, 12.5, $d->font['regular'], $tokens[ $segment[1] ], $segment[0] );
		}

		if ( '' !== $row[2] ) {
			$source_y[ $row[2] ] = $ly - 4;
		}

		$ly += $code_lh;
	}

	// --- generative answer card ---
	$d->card( $card_x, $body_y, $card_w, $right_h, 10, $white, $line );

	$d->ellipse( $card_x + 36, $body_y + 34, 26, $d->color( '#0A0A0A' ) );
	$d->text( $card_x + 36, $body_y + 39, 11.5, $d->font['medium'], $gold, 'AI', 'c' );
	$d->text( $card_x + 60, $body_y + 30, 14.5, $d->font['medium'], $ink, 'Generative answer' );
	$d->text( $card_x + 60, $body_y + 48, 12, $d->font['regular'], $muted, 'Cited from your product page' );
	$d->fill_rect( $card_x + 1, $body_y + 64, $card_w - 2, 1, $d->color( '#EDEDED' ) );

	$qy = $body_y + 93;
	foreach ( $quote_lines as $l ) {
		$d->text( $card_x + 22, $qy, 14, $d->font['regular'], $muted, $l );
		$qy += 20;
	}

	$inner_y = $qy + 2;
	$d->card( $card_x + 16, $inner_y, $card_w - 32, $inner_h, 8, $d->color( '#FAFAFA' ), $d->color( '#E8E8E8' ) );

	$dest_y = array();
	$iy     = $inner_y + 36;

	foreach ( $title_lines as $l ) {
		$d->text( $card_x + 36, $iy, 17, $d->font['medium'], $ink, $l );
		$iy += 23;
	}
	$dest_y['name'] = $inner_y + 30;

	$iy += 4;
	$d->text( $card_x + 36, $iy, 13.5, $d->font['regular'], $muted, '18K White Gold' );
	$dest_y['material'] = $iy - 5;
	$iy                += 30;

	$price_w = $d->text( $card_x + 36, $iy, 22, $d->font['display'], $ink, '$2,840.00' );
	$d->text( $card_x + 36 + $price_w + 8, $iy, 12, $d->font['regular'], $muted, 'USD' );
	$dest_y['price'] = $iy - 8;
	$iy             += 28;

	for ( $i = 0; $i < 5; $i++ ) {
		$d->star( $card_x + 42 + $i * 17, $iy - 5, 7.5, $gold );
	}

	$rx = $card_x + 133;
	$rw = $d->text( $rx, $iy, 13.5, $d->font['regular'], $body_c, '4.8' );
	$d->text( $rx + $rw + 9, $iy, 13.5, $d->font['regular'], $muted, '(213 reviews)' );
	$dest_y['rating'] = $iy - 5;
	$iy              += 26;

	$d->check( $card_x + 37, $iy );
	$d->text( $card_x + 58, $iy, 13.5, $d->font['regular'], $body_c, '30-day returns' );
	$dest_y['returns'] = $iy - 5;
	$iy               += 16;

	$d->fill_rect( $card_x + 36, $iy, $card_w - 104, 1, $line );
	$iy += 22;

	$chip_w = $d->text_width( 12.5, $d->font['medium'], 'aurelis.com' );
	$d->round_rect( $card_x + 36, $iy - 14, $chip_w + 22, 24, 6, $d->color( '#FFF8EA' ) );
	$d->text( $card_x + 47, $iy + 3, 12.5, $d->font['medium'], $gold_t, 'aurelis.com' );

	$ny = $inner_y + $inner_h + 30;
	foreach ( $note_lines as $l ) {
		$d->text( $card_x + 22, $ny, 12.5, $d->font['regular'], $muted, $l );
		$ny += 18;
	}

	// --- field connectors, drawn last so they sit above the gap ---
	$mid  = $code_x + $code_w + ( ( $card_x - ( $code_x + $code_w ) ) / 2 );
	$keys = array( 'name', 'material', 'price', 'rating', 'returns' );

	foreach ( $keys as $i => $key ) {
		if ( ! isset( $source_y[ $key ], $dest_y[ $key ] ) ) {
			continue;
		}

		$sy = $source_y[ $key ];
		$dy = $dest_y[ $key ];
		$mx = $mid - 24 + $i * 11;

		$d->fill_rect( $code_x + $code_w + 6, $sy - 0.75, $mx - ( $code_x + $code_w + 6 ), 1.5, $gold );
		$d->fill_rect( $mx, min( $sy, $dy ) - 0.75, 1.5, abs( $dy - $sy ) + 1.5, $gold );
		$d->fill_rect( $mx, $dy - 0.75, $card_x - 8 - $mx, 1.5, $gold );
		$d->arrow_right( $card_x - 1, $dy, 8, $gold );
	}

	$d->footer( $bar_y, $callout );

	return $d->save( rk_diagram_uploads_dir() . 'chatgpt_schema_mapping.png' );
}

/* ==================================================================== runner */

function rk_diagram_generate_all() {
	$results = array();

	foreach ( array(
		'search_comparison_discovery' => 'rk_diagram_search_comparison',
		'rag_retrieval_pipeline'      => 'rk_diagram_rag_pipeline',
		'onpage_spec_architecture'    => 'rk_diagram_onpage_architecture',
		'chatgpt_schema_mapping'      => 'rk_diagram_schema_mapping',
	) as $name => $builder ) {
		$results[ $name ] = $builder( new RK_Diagram() );
	}

	// Attachment IDs on growwithrokibul.com for the four diagrams above.
	require_once ABSPATH . 'wp-admin/includes/image.php';

	foreach ( array( 705, 706, 707, 708 ) as $id ) {
		$file = get_attached_file( $id );
		clearstatcache( true, $file );
		wp_update_attachment_metadata( $id, wp_generate_attachment_metadata( $id, $file ) );
	}

	clean_post_cache( 703 );

	return $results;
}

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	print_r( rk_diagram_generate_all() );
}
