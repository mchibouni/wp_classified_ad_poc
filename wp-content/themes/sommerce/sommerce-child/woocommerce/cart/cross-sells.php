<?php
/**
 * Cross-sells
 */

global $woocommerce_loop, $woocommerce, $product;

$crosssells = $woocommerce->cart->get_cross_sells();

if ( sizeof( $crosssells ) == 0 ) return;

$args = array(
	'post_type'				=> 'product',
	'ignore_sticky_posts'	=> 1,
	'posts_per_page' 		=> apply_filters( 'woocommerce_upsells_posts_per_page_cart', 3 ),
	'no_found_rows' 		=> 1,
	'orderby' 				=> 'rand',
	'post__in' 				=> $crosssells
);

$products = new WP_Query( $args );

$woocommerce_loop['columns'] 	= apply_filters( 'woocommerce_upsells_columns_cart', 3 );

if ( $products->have_posts() ) : ?>

	<div class="cross-sells">
		
		<h2><?php _e('You may be interested in&hellip;', 'woocommerce') ?></h2>
	
		<ul class="products">
			
			<?php while ( $products->have_posts() ) : $products->the_post(); ?>
		
				<?php woocommerce_get_template_part( 'content', 'product' ); ?>
	
			<?php endwhile; // end of the loop. ?>
				
		</ul>
		
	</div>
	
<?php endif; 

wp_reset_query();
