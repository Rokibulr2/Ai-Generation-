<?php
/**
 * RKCanvas — small GD drawing helper for the growwithrokibul.com article diagrams.
 *
 * Everything is drawn on a supersampled canvas (scale factor S) and resampled
 * down on save, which is what gives the shapes and type their antialiased edges.
 * All public methods take logical (unscaled) coordinates.
 */

if ( ! class_exists( 'RKCanvas' ) ) {

class RKCanvas {

	/** Brand palette, lifted from the site design-system CSS. */
	const INK     = '#0a0a0a';
	const INK_2   = '#232323';
	const BODY    = '#555555';
	const MUTED   = '#8a8a8a';
	const LINE    = '#e2e2e0';
	const LINE_2  = '#efefed';
	const PAPER   = '#fafaf9';
	const WHITE   = '#ffffff';
	const AMBER   = '#e8a013';
	const AMBER_L = '#fdf6e7';
	const AMBER_B = '#f0cf85';
	const GREEN   = '#16a34a';
	const RED     = '#b91c1c';

	public $im;
	public $W;
	public $H;
	public $S;
	public $F = array();

	private $colors = array();

	public function __construct( $w, $h, $scale = 2, $bg = self::WHITE ) {
		$this->W = $w;
		$this->H = $h;
		$this->S = $scale;

		$this->im = imagecreatetruecolor( (int) ( $w * $scale ), (int) ( $h * $scale ) );
		imagealphablending( $this->im, true );

		$base = WP_CONTENT_DIR . '/plugins/google-site-kit/dist/assets/js/fonts/';
		$this->F = array(
			'display'  => $base . 'google-sans-display-medium-b41b7e0618b540110582.ttf',
			'displayr' => $base . 'google-sans-display-regular-67843392439f681fdbfd.ttf',
			'med'      => $base . 'google-sans-text-medium-53980445227ada4764a7.ttf',
			'reg'      => $base . 'google-sans-text-regular-0d01ceaea2903216c4ee.ttf',
		);

		$this->rect( 0, 0, $w, $h, $bg );
	}

	/* ---------------------------------------------------------- colour --- */

	public function c( $hex ) {
		if ( isset( $this->colors[ $hex ] ) ) {
			return $this->colors[ $hex ];
		}
		$h = ltrim( $hex, '#' );
		$r = hexdec( substr( $h, 0, 2 ) );
		$g = hexdec( substr( $h, 2, 2 ) );
		$b = hexdec( substr( $h, 4, 2 ) );
		$this->colors[ $hex ] = imagecolorallocate( $this->im, $r, $g, $b );
		return $this->colors[ $hex ];
	}

	/** Blend a colour toward another by $t (0..1) — used for soft tints. */
	public function mix( $hex, $toward, $t ) {
		$a = ltrim( $hex, '#' );
		$b = ltrim( $toward, '#' );
		$o = '#';
		for ( $i = 0; $i < 3; $i++ ) {
			$ca = hexdec( substr( $a, $i * 2, 2 ) );
			$cb = hexdec( substr( $b, $i * 2, 2 ) );
			$o .= str_pad( dechex( (int) round( $ca + ( $cb - $ca ) * $t ) ), 2, '0', STR_PAD_LEFT );
		}
		return $o;
	}

	/* ---------------------------------------------------------- shapes --- */

	public function rect( $x, $y, $w, $h, $fill, $r = 0 ) {
		$S   = $this->S;
		$x   = (int) round( $x * $S );
		$y   = (int) round( $y * $S );
		$w   = (int) round( $w * $S );
		$h   = (int) round( $h * $S );
		$r   = (int) round( $r * $S );
		$col = $this->c( $fill );

		if ( $r <= 0 ) {
			imagefilledrectangle( $this->im, $x, $y, $x + $w - 1, $y + $h - 1, $col );
			return;
		}
		$r = min( $r, (int) floor( min( $w, $h ) / 2 ) );
		imagefilledrectangle( $this->im, $x + $r, $y, $x + $w - $r - 1, $y + $h - 1, $col );
		imagefilledrectangle( $this->im, $x, $y + $r, $x + $w - 1, $y + $h - $r - 1, $col );
		$d = $r * 2;
		imagefilledellipse( $this->im, $x + $r, $y + $r, $d, $d, $col );
		imagefilledellipse( $this->im, $x + $w - $r - 1, $y + $r, $d, $d, $col );
		imagefilledellipse( $this->im, $x + $r, $y + $h - $r - 1, $d, $d, $col );
		imagefilledellipse( $this->im, $x + $w - $r - 1, $y + $h - $r - 1, $d, $d, $col );
	}

	/** Bordered panel: border-coloured rect with an inset fill. */
	public function card( $x, $y, $w, $h, $fill, $border, $r = 10, $t = 1 ) {
		$this->rect( $x, $y, $w, $h, $border, $r );
		$this->rect( $x + $t, $y + $t, $w - $t * 2, $h - $t * 2, $fill, max( 0, $r - $t ) );
	}

	public function circle( $cx, $cy, $d, $fill ) {
		$S = $this->S;
		imagefilledellipse(
			$this->im,
			(int) round( $cx * $S ),
			(int) round( $cy * $S ),
			(int) round( $d * $S ),
			(int) round( $d * $S ),
			$this->c( $fill )
		);
	}

	public function line( $x1, $y1, $x2, $y2, $color, $t = 1 ) {
		$S = $this->S;
		imagesetthickness( $this->im, max( 1, (int) round( $t * $S ) ) );
		imageline(
			$this->im,
			(int) round( $x1 * $S ),
			(int) round( $y1 * $S ),
			(int) round( $x2 * $S ),
			(int) round( $y2 * $S ),
			$this->c( $color )
		);
		imagesetthickness( $this->im, 1 );
	}

	public function dashed( $x1, $y1, $x2, $y2, $color, $t = 1, $dash = 6, $gap = 5 ) {
		$dx  = $x2 - $x1;
		$dy  = $y2 - $y1;
		$len = sqrt( $dx * $dx + $dy * $dy );
		if ( $len <= 0 ) {
			return;
		}
		$ux   = $dx / $len;
		$uy   = $dy / $len;
		$step = $dash + $gap;
		for ( $d = 0; $d < $len; $d += $step ) {
			$e = min( $d + $dash, $len );
			$this->line( $x1 + $ux * $d, $y1 + $uy * $d, $x1 + $ux * $e, $y1 + $uy * $e, $color, $t );
		}
	}

	public function poly( $points, $fill ) {
		$S   = $this->S;
		$pts = array();
		foreach ( $points as $i => $p ) {
			$pts[] = (int) round( $p * $S );
		}
		$col = $this->c( $fill );
		if ( PHP_VERSION_ID >= 80000 ) {
			imagefilledpolygon( $this->im, $pts, $col );
		} else {
			imagefilledpolygon( $this->im, $pts, count( $pts ) / 2, $col );
		}
	}

	/** Line with a solid arrowhead at (x2,y2). */
	public function arrow( $x1, $y1, $x2, $y2, $color, $t = 1.5, $head = 9 ) {
		$ang = atan2( $y2 - $y1, $x2 - $x1 );
		$bx  = $x2 - cos( $ang ) * $head;
		$by  = $y2 - sin( $ang ) * $head;
		$this->line( $x1, $y1, $bx, $by, $color, $t );
		$hw = $head * 0.5;
		$px = -sin( $ang ) * $hw;
		$py = cos( $ang ) * $hw;
		$this->poly( array( $x2, $y2, $bx + $px, $by + $py, $bx - $px, $by - $py ), $color );
	}

	public function dashedArrow( $x1, $y1, $x2, $y2, $color, $t = 1.2, $head = 8 ) {
		$ang = atan2( $y2 - $y1, $x2 - $x1 );
		$bx  = $x2 - cos( $ang ) * $head;
		$by  = $y2 - sin( $ang ) * $head;
		$this->dashed( $x1, $y1, $bx, $by, $color, $t, 5, 4 );
		$hw = $head * 0.5;
		$px = -sin( $ang ) * $hw;
		$py = cos( $ang ) * $hw;
		$this->poly( array( $x2, $y2, $bx + $px, $by + $py, $bx - $px, $by - $py ), $color );
	}

	/** Five-pointed star (Google Sans has no ★ glyph). */
	public function star( $cx, $cy, $r, $fill ) {
		$pts = array();
		for ( $i = 0; $i < 10; $i++ ) {
			$rad = ( $i % 2 === 0 ) ? $r : $r * 0.45;
			$a   = -M_PI / 2 + $i * M_PI / 5;
			$pts[] = $cx + cos( $a ) * $rad;
			$pts[] = $cy + sin( $a ) * $rad;
		}
		$this->poly( $pts, $fill );
	}

	/* ------------------------------------------------------------ type --- */

	public function tw( $str, $size, $font = 'reg' ) {
		$bb = imagettfbbox( $size * $this->S, 0, $this->F[ $font ], $str );
		return ( $bb[2] - $bb[0] ) / $this->S;
	}

	/**
	 * Draw a single line of text. $y is the TOP of the text box, not the
	 * baseline — offsets come from a fixed reference string so successive
	 * lines never jitter.
	 */
	public function text( $str, $x, $y, $size, $color, $font = 'reg', $align = 'left' ) {
		$f  = $this->F[ $font ];
		$s  = $size * $this->S;
		$bb = imagettfbbox( $s, 0, $f, $str );
		$w  = $bb[2] - $bb[0];
		$X  = $x * $this->S;

		if ( 'center' === $align ) {
			$X -= $w / 2;
		} elseif ( 'right' === $align ) {
			$X -= $w;
		}

		$ref = imagettfbbox( $s, 0, $f, 'Hg' );
		imagettftext(
			$this->im,
			$s,
			0,
			(int) round( $X ),
			(int) round( $y * $this->S - $ref[7] ),
			$this->c( $color ),
			$f,
			$str
		);
		return $w / $this->S;
	}

	/** Cap height of the reference glyphs, in logical px. */
	public function lineHeight( $size, $font = 'reg' ) {
		$ref = imagettfbbox( $size * $this->S, 0, $this->F[ $font ], 'Hg' );
		return ( $ref[1] - $ref[7] ) / $this->S;
	}

	/** Word-wrap into $maxw. Returns the y coordinate just past the last line. */
	public function wrap( $str, $x, $y, $maxw, $size, $lh, $color, $font = 'reg', $align = 'left', $maxLines = 0 ) {
		$words = preg_split( '/\s+/u', trim( $str ) );
		$lines = array();
		$cur   = '';
		foreach ( $words as $word ) {
			$try = ( '' === $cur ) ? $word : $cur . ' ' . $word;
			if ( $this->tw( $try, $size, $font ) <= $maxw || '' === $cur ) {
				$cur = $try;
			} else {
				$lines[] = $cur;
				$cur     = $word;
			}
		}
		if ( '' !== $cur ) {
			$lines[] = $cur;
		}
		if ( $maxLines > 0 && count( $lines ) > $maxLines ) {
			$lines   = array_slice( $lines, 0, $maxLines );
			$last    = count( $lines ) - 1;
			$lines[ $last ] = rtrim( $lines[ $last ], ',.;: ' ) . '...';
		}
		foreach ( $lines as $i => $l ) {
			$this->text( $l, $x, $y + $i * $lh, $size, $color, $font, $align );
		}
		return $y + count( $lines ) * $lh;
	}

	/** How many lines $str would occupy at this width. */
	public function wrapCount( $str, $maxw, $size, $font = 'reg' ) {
		$words = preg_split( '/\s+/u', trim( $str ) );
		$n     = 1;
		$cur   = '';
		foreach ( $words as $word ) {
			$try = ( '' === $cur ) ? $word : $cur . ' ' . $word;
			if ( $this->tw( $try, $size, $font ) <= $maxw || '' === $cur ) {
				$cur = $try;
			} else {
				$n++;
				$cur = $word;
			}
		}
		return $n;
	}

	/** Small pill label. Returns its width. */
	public function chip( $str, $x, $y, $size, $fg, $bg, $font = 'med', $padX = 9, $h = 0 ) {
		$w = $this->tw( $str, $size, $font ) + $padX * 2;
		$h = $h ?: $size + 12;
		$this->rect( $x, $y, $w, $h, $bg, $h / 2 );
		$this->text( $str, $x + $padX, $y + ( $h - $this->lineHeight( $size, $font ) ) / 2, $size, $fg, $font );
		return $w;
	}

	/* ------------------------------------------------------------ meta --- */

	/** Standard header block. Returns the y below the rule. */
	public function header( $title, $sub, $x = 56, $y = 46, $maxw = 1088 ) {
		$this->text( $title, $x, $y, 29, self::INK, 'display' );
		$yy = $y + 42;
		if ( $sub ) {
			$yy = $this->wrap( $sub, $x, $yy, $maxw, 15, 22, self::BODY, 'reg' );
			$yy += 14;
		}
		$this->rect( $x, $yy, $maxw, 1, self::LINE );
		return $yy + 1;
	}

	public function watermark( $color = '', $size = 12.5 ) {
		$color = $color ?: self::MUTED;
		$y     = $this->H - 30;
		$w     = $this->tw( 'growwithrokibul.com', $size, 'med' );
		$this->circle( $this->W - 56 - $w - 13, $y + $this->lineHeight( $size, 'med' ) / 2, 6, self::AMBER );
		$this->text( 'growwithrokibul.com', $this->W - 56, $y, $size, $color, 'med', 'right' );
	}

	public function save( $path ) {
		$o = imagecreatetruecolor( $this->W, $this->H );
		imagecopyresampled(
			$o, $this->im,
			0, 0, 0, 0,
			$this->W, $this->H,
			(int) ( $this->W * $this->S ), (int) ( $this->H * $this->S )
		);
		$ok = imagepng( $o, $path, 6 );
		imagedestroy( $o );
		imagedestroy( $this->im );
		return $ok ? filesize( $path ) : 0;
	}
}

}
