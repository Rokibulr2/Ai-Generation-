<?php
/**
 * Featured / OG image for the ChatGPT jewelry search article.
 * Output: featured_image.png (1200x630)
 */

require_once __DIR__ . '/rk-diagram-lib.php';

$c = new RKCanvas( 1200, 630, 2, '#0a0a0a' );

$INK_CARD = '#16181b';
$INK_EDGE = '#282c31';
$GREY     = '#a8a8a8';

/* Faint dot field behind the mock card. */
for ( $x = 690; $x <= 1180; $x += 26 ) {
	for ( $y = 56; $y <= 580; $y += 26 ) {
		$c->circle( $x, $y, 2, '#1b1e22' );
	}
}

/* Amber edge accent. */
$c->rect( 0, 0, 6, 630, RKCanvas::AMBER );

/* ------------------------------------------------------------ left copy --- */

$LX = 76;
$LW = 600;

$c->rect( $LX, 92, $c->tw( 'GENERATIVE ENGINE OPTIMIZATION', 12, 'med' ) + 26, 30, '#20180a', 15 );
$c->text( 'GENERATIVE ENGINE OPTIMIZATION', $LX + 13, 100, 12, RKCanvas::AMBER, 'med' );

$y = $c->wrap(
	'Optimizing Jewelry Brands for ChatGPT Search',
	$LX, 154, $LW, 43, 55, '#ffffff', 'display'
);

$c->rect( $LX, $y + 26, 54, 3, RKCanvas::AMBER );

$c->wrap(
	'How to capture buyer intent in generative recommendation engines.',
	$LX, $y + 52, $LW - 20, 17.5, 27, $GREY, 'reg'
);

/* Footer signature. */
$c->circle( $LX + 6, 552, 9, RKCanvas::AMBER );
$c->text( 'growwithrokibul.com', $LX + 22, 544, 14, '#e8e8e8', 'med' );

/* ------------------------------------------------------- mock answer card --- */

$KX = 744;
$KY = 132;
$KW = 384;
$KH = 366;

$c->card( $KX, $KY, $KW, $KH, $INK_CARD, $INK_EDGE, 16, 1 );

$c->circle( $KX + 30, $KY + 30, 20, RKCanvas::AMBER );
$c->text( 'AI', $KX + 30, $KY + 24, 10, '#0a0a0a', 'med', 'center' );
$c->text( 'Recommended for you', $KX + 52, $KY + 16, 12.5, '#ffffff', 'med' );
$c->text( 'Cited · aurelis.com', $KX + 52, $KY + 34, 11, '#7c8189', 'reg' );
$c->rect( $KX + 1, $KY + 66, $KW - 2, 1, '#23262b' );

$iy = $KY + 92;
$c->wrap( '1.20ct Emerald-Cut Diamond Ring', $KX + 26, $iy, $KW - 52, 16, 23, '#ffffff', 'med' );

$c->text( '18K White Gold', $KX + 26, $iy + 54, 12.5, '#8f959d', 'reg' );
$c->text( '$2,840.00', $KX + 26, $iy + 78, 20, '#ffffff', 'display' );

for ( $s = 0; $s < 5; $s++ ) {
	$c->star( $KX + 33 + $s * 18, $iy + 126, 8, RKCanvas::AMBER );
}
$c->text( '4.8  (213 reviews)', $KX + 132, $iy + 118, 12.5, '#8f959d', 'reg' );

$c->text( '✓', $KX + 26, $iy + 150, 13, '#4ec97e', 'med' );
$c->text( '30-day returns · Free resizing', $KX + 48, $iy + 151, 12.5, '#8f959d', 'reg' );

$c->rect( $KX + 26, $iy + 184, $KW - 52, 1, '#23262b' );
$c->text( 'Matched on budget, metal and sourcing', $KX + 26, $iy + 200, 11, '#6d7178', 'reg' );

return array( 'bytes' => $c->save( $ARGS['path'] ) );
