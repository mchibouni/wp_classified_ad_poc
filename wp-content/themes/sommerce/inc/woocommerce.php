<?php 
/**
 * All functions and hooks for jigoshop plugin  
 *
 * @package WordPress
 * @subpackage YIW Themes
 * @since 1.4
 */    
 
include 'shortcodes-woocommerce.php';   

remove_action( 'woocommerce_pagination', 'woocommerce_catalog_ordering', 20 );
add_action( 'woocommerce_before_main_content' , create_function( '', 'if ( ! is_single() ) woocommerce_catalog_ordering();' ) );    

// add theme support
add_theme_support('woocommerce');

// active the price filter
if(version_compare($woocommerce->version,"2.0.0") < 0 ) {
    add_action('init', 'woocommerce_price_filter_init');
}
add_filter('loop_shop_post_in', 'woocommerce_price_filter');

function yiw_set_posts_per_page( $cols ) {                 
    return yiw_get_option( 'shop_products_per_page', $cols );
}
add_filter('loop_shop_per_page', 'yiw_set_posts_per_page');

function yiw_estimate_n_cols() {
    global $content_width;
    
    return floor( $content_width / ( yiw_shop_thumbnail_w() + 35 ) ); 
}
add_filter( 'loop_shop_columns', 'yiw_estimate_n_cols' );

function yiw_add_style_woocommerce() {
    wp_enqueue_style( 'jquery-ui-style', (is_ssl()) ? 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css' : 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css' );
}
add_action( 'init', 'yiw_add_style_woocommerce' );

function yiw_add_to_cart_success_ajax( $datas ) {
    global $woocommerce;       
	
	// quantity
	$qty = 0;
	if (sizeof($woocommerce->cart->get_cart())>0) : foreach ($woocommerce->cart->get_cart() as $item_id => $values) :
	
		$qty += $values['quantity'];  
	
	endforeach; endif;                     
	
	if ( $qty == 1 )
	   $label = __( 'item', 'yiw' );
	else             
	   $label = __( 'items', 'yiw' );
		
    $datas['#linksbar .widget_shopping_cart'] = '<a class="widget_shopping_cart trigger" href="' . $woocommerce->cart->get_cart_url() . '">' . $qty . ' ' . $label . ' &ndash; ' . $woocommerce->cart->get_cart_total() . '</a>';
    $datas['span.minicart'] = $qty . ' ' . $label . ' &ndash; ' . $woocommerce->cart->get_cart_total();
    $datas['#linksbar .widget_shopping_cart .amount'] = $woocommerce->cart->get_cart_total();

    return $datas;
}
add_filter( 'add_to_cart_fragments', 'yiw_add_to_cart_success_ajax' );

function yiw_woocommerce_javascript_scripts() {
    ?>
    <script type="text/javascript">
    jQuery(document).ready(function($){   
        $('body').bind('added_to_cart', function(){
            $('.add_to_cart_button.added').text('ADDED');
        });               
    });
    </script>
    <?php
}
add_action( 'wp_head', 'yiw_woocommerce_javascript_scripts' );


/** SHOP
-------------------------------------------------------------------- */

// add the sale icon inside the product detail image container
remove_action( 'woocommerce_before_single_product_summary', 'woocommerce_show_product_sale_flash', 10);
add_action( 'woocommerce_product_thumbnails', 'woocommerce_show_product_sale_flash');

// decide the layout for the shop pages
function yiw_shop_layouts( $default_layout ) {    
    if ( get_post_type() == 'product' && is_single() )          
        return yiw_get_option( 'shop_layout_page_single', 'sidebar-no' ); 
    elseif ( is_post_type_archive( 'product' ) || ( get_post_type() == 'product' && is_search() ) || is_tax( 'product_cat' ) )
        return ( $l=get_post_meta( get_option( 'woocommerce_shop_page_id' ), '_layout_page', true )) ? $l : YIW_DEFAULT_LAYOUT_PAGE;
    else  
        return $default_layout;
}
add_filter( 'yiw_layout_page', 'yiw_shop_layouts' );

// generate the main width for content and sidebar
function yiw_layout_widths() {
    global $content_width;
    
    $sidebar = YIW_SIDEBAR_WIDTH;
    
    if ( get_post_type() == 'product' || get_post_meta( get_the_ID(), '_sidebar_choose_page', true ) == 'Shop Sidebar' || is_tax( 'product_cat' ) )
        $sidebar = YIW_SIDEBAR_SHOP_WIDTH;
    
    $content_width = YIW_MAIN_WIDTH - ( $sidebar + 40 );
    
    ?>
        #content { width:<?php echo $content_width ?>px; }
        #sidebar { width:<?php echo $sidebar ?>px; }        
        #sidebar.shop { width:<?php echo YIW_SIDEBAR_SHOP_WIDTH ?>px; }
    <?php
}
add_action( 'yiw_custom_styles', 'yiw_layout_widths' );

function yiw_minicart() {
    global $woocommerce;
	
	// quantity
	$qty = 0;
	if (sizeof($woocommerce->cart->get_cart())>0) : foreach ($woocommerce->cart->get_cart() as $item_id => $values) :
	
		$qty += $values['quantity'];
	
	endforeach; endif;
	
	if ( $qty == 1 )
	   $label = __( 'item', 'yiw' );
	else             
	   $label = __( 'items', 'yiw' );
	   
	
	echo '<a class="widget_shopping_cart trigger" href="' . $woocommerce->cart->get_cart_url() . '">
			<span class="minicart">' . $qty . ' ' . $label . ' &ndash; ' . $woocommerce->cart->get_cart_total() . ' </span>
		</a> | ';
}     

// Decide if show the price and/or the button add to cart, on the product detail page
function yiw_remove_ecommerce() {
    if ( ! yiw_get_option( 'shop_show_button_add_to_cart_single_page', 1 ) )                         
        remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30 ); 
    if ( ! yiw_get_option( 'shop_show_price_single_page', 1 ) )                       
        remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 10);
}
add_action( 'wp_head', 'yiw_remove_ecommerce', 1 );

/**
 * LAYOUT
 */
function yiw_shop_layout_pages_before() {     
    $layout = yiw_layout_page();
    if ( get_post_type() == 'product' && is_tax( 'product-category' ) )
        $layout = 'sidebar-no';                     
    elseif ( get_post_type() == 'product' && is_single() )          
        $layout = yiw_get_option( 'shop_layout_page_single', 'sidebar-no' ); 
    elseif ( get_post_type() == 'product' && ! is_single() )
        $layout = ( $l=get_post_meta( get_option( 'woocommerce_shop_page_id' ), '_layout_page', true )) ? $l : YIW_DEFAULT_LAYOUT_PAGE;  
    ?><div class="layout-<?php echo $layout ?> group"><?php
} 

function yiw_shop_layout_pages_after() { 
    ?></div><?php    
}                                                                   
  
//add_action( 'woocommerce_before_main_content', 'yiw_shop_layout_pages_before', 1 );
//add_action( 'woocommerce_sidebar', 'yiw_shop_layout_pages_after', 99 );
                    
/**
 * SIZES
 */ 

// shop small
function yiw_shop_small_w() { global $woocommerce; $size = $woocommerce->get_image_size('shop_thumbnail'); return $size['width']; }	
function yiw_shop_small_h() { global $woocommerce; $size =$woocommerce->get_image_size('shop_thumbnail'); return $size['height']; }   
// shop thumbnail
function yiw_shop_thumbnail_w() { global $woocommerce; $size = $woocommerce->get_image_size('shop_catalog'); return $size['width']; }	
function yiw_shop_thumbnail_h() { global $woocommerce; $size =$woocommerce->get_image_size('shop_catalog'); return $size['height']; }   
// shop large
function yiw_shop_large_w() { global $woocommerce; $size = $woocommerce->get_image_size('shop_single'); return $size['width']; }	
function yiw_shop_large_h() { global $woocommerce; $size =$woocommerce->get_image_size('shop_single'); return $size['height']; }   

/*
function yit_shop_small_w() { global $woocommerce; $size = $woocommerce->get_image_size('shop_catalog'); return $size['width']; }	
function yit_shop_small_h() { global $woocommerce; $size =$woocommerce->get_image_size('shop_catalog'); return $size['height']; }   
// shop thumbnail
function yit_shop_thumbnail_w() { global $woocommerce; $size = $woocommerce->get_image_size('shop_thumbnail'); return $size['width']; }	
function yit_shop_thumbnail_h() { global $woocommerce; $size = $woocommerce->get_image_size('shop_thumbnail'); return $size['height']; } 
// shop large
function yit_shop_large_w() { global $woocommerce; $size = $woocommerce->get_image_size('shop_single'); return $size['width']; }	
function yit_shop_large_h() { global $woocommerce; $size = $woocommerce->get_image_size('shop_single'); return $size['height']; } 
 */

// print style for small thumb size
function yiw_size_images_style() {
	?>
	.products li { width:<?php echo yiw_shop_thumbnail_w() + ( yiw_get_option( 'shop_border_thumbnail' ) ? 14 : 0 ) ?>px !important; }
	.products li a strong { width:<?php echo yiw_shop_thumbnail_w() - 30 ?>px !important; }
	.products li a strong.inside-thumb { top:<?php echo yiw_shop_thumbnail_h() - 41 ?>px !important; }
	.products li.border a strong.inside-thumb { top:<?php echo yiw_shop_thumbnail_h() + 7 - 41 ?>px !important; }
	.products li a img { width:<?php echo yiw_shop_thumbnail_w() ?>px !important;height:<?php echo yiw_shop_thumbnail_h() ?>px !important; }
	div.product div.images { width:<?php echo ( yiw_shop_large_w() + 14 ) / 750 * 100 ?>%; }
	.layout-sidebar-no div.product div.images { width:<?php echo ( yiw_shop_large_w() + 14 ) / 960 * 100 ?>%; }
	div.product div.images img { width:<?php echo yiw_shop_large_w() ?>px; }
	.layout-sidebar-no div.product div.summary { width:<?php echo ( 960 - ( yiw_shop_large_w() + 14 ) - 20 ) / 960 * 100 ?>%; }
	.layout-sidebar-right div.product div.summary, .layout-sidebar-left div.product div.summary { width:<?php echo ( 750 - ( yiw_shop_large_w() + 14 ) - 20 ) / 750 * 100 ?>%; }
	<?php
}
add_action( 'yiw_custom_styles', 'yiw_size_images_style' );

/**
 * PRODUCT PAGE
 */     

function yiw_related_products_tab( $current_tab ) {      
	    if ( ! yiw_if_related() ) {
	        return;
        } else {
		 	return array( 'related' => array (
		 					'title' => __('Related Products', 'yiw'),
							'priority' => '5',
							'callback' => 'woocommerce_related_products_panel'
						)
			);
        }
}                

function yiw_if_related() {
    global $product;

    $related = $product->get_related();

    if ( !empty( $related ) ) {
        return true;
    }

    return false;
}     

function woocommerce_related_products_panel() { 
    if ( ! yiw_if_related() ) {
        return;
    } else { ?>
		<div class="group">
			<h2><?php _e('Related Products', 'yiw') ?></h2>
			<?php if ( yiw_get_option('shop_show_related_single_product') )
				woocommerce_related_products( apply_filters( 'related_products_products_per_page', yiw_get_option('shop_number_related_single_product') ), apply_filters( 'related_products_columns', yiw_get_option( 'shop_columns_related_single_product' ) ) );
			else
				woocommerce_related_products( apply_filters( 'related_products_products_per_page', 5 ), apply_filters( 'related_products_columns', 5 ) ); ?>
		</div>
		<?php
	}	
}                                                            
//add_action( 'woocommerce_product_tab_panels', 'woocommerce_related_products_panel', 1 );
if ( ! is_admin() ) {
    add_action( 'woocommerce_product_tabs', 'yiw_related_products_tab', 1 ); 
    remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20);
}

if ( ! isset( $_COOKIE["current_tab"] ) ) {
    setcookie( 'current_tab', '#related-products' );
    $_COOKIE["current_tab"] = '#related-products';
}

// product thumbnail
// function woocommerce_get_product_thumbnail( $size = 'shop_small', $placeholder_width = 0, $placeholder_height = 0 ) {
// 	global $post, $woocommerce;
// 
// 	if (!$placeholder_width) $placeholder_width = $woocommerce->get_image_size('shop_catalog_image_width');
// 	if (!$placeholder_height) $placeholder_height = $woocommerce->get_image_size('shop_catalog_image_height');
// 	
// 	if ( has_post_thumbnail() ) 
// 	   $thumb = get_the_post_thumbnail($post->ID, $size);
// 	else
// 	   $thumb = '';
// 	
// 	if ( empty( $thumb ) )
//         $thumb = '<img src="'.woocommerce::plugin_url(). '/assets/images/placeholder.png" alt="Placeholder" width="'.$placeholder_width.'" height="'.$placeholder_height.'" />';
// 	
//     return $thumb;
// }

// number of products
function yiw_items_list_pruducts() {
    return 8;
}
//add_filter( 'loop_shop_per_page', 'yiw_items_list_pruducts' );



/** NAV MENU
-------------------------------------------------------------------- */

add_action('admin_init', array('yiwProductsPricesFilter', 'admin_init'));

class yiwProductsPricesFilter {
	// We cannot call #add_meta_box yet as it has not been defined,
    // therefore we will call it in the admin_init hook
	function admin_init() {
		if ( ! is_plugin_active( 'woocommerce/woocommerce.php' ) || basename($_SERVER['PHP_SELF']) != 'nav-menus.php' ) 
			return;
			                                                    
		wp_enqueue_script('nav-menu-query', get_template_directory_uri() . '/inc/admin_scripts/metabox_nav_menu.js', 'nav-menu', false, true);
		add_meta_box('products-by-prices', 'Prices Filter', array(__CLASS__, 'nav_menu_meta_box'), 'nav-menus', 'side', 'low');
	}

	function nav_menu_meta_box() { ?>
	<div class="prices">        
		<input type="hidden" name="woocommerce_currency" id="woocommerce_currency" value="<?php echo get_woocommerce_currency_symbol( get_option('woocommerce_currency') ) ?>" />
		<input type="hidden" name="woocommerce_shop_url" id="woocommerce_shop_url" value="<?php echo get_option('permalink_structure') == '' ? site_url() . '/?post_type=product' : get_permalink( get_option('woocommerce_shop_page_id') ) ?>" />
		<input type="hidden" name="menu-item[-1][menu-item-url]" value="" />
		<input type="hidden" name="menu-item[-1][menu-item-title]" value="" />
		<input type="hidden" name="menu-item[-1][menu-item-type]" value="custom" />
		
		<p>
		    <?php _e( sprintf( 'The values are already expressed in %s', get_woocommerce_currency_symbol( get_option('woocommerce_currency') ) ), 'yiw' ) ?>
		</p>
		
		<p>
			<label class="howto" for="prices_filter_from">
				<span><?php _e('From'); ?></span>
				<input id="prices_filter_from" name="prices_filter_from" type="text" class="regular-text menu-item-textbox input-with-default-title" title="<?php esc_attr_e('From'); ?>" />
			</label>
		</p>

		<p style="display: block; margin: 1em 0; clear: both;">
			<label class="howto" for="prices_filter_to">
				<span><?php _e('To'); ?></span>
				<input id="prices_filter_to" name="prices_filter_to" type="text" class="regular-text menu-item-textbox input-with-default-title" title="<?php esc_attr_e('To'); ?>" />
			</label>
		</p>

		<p class="button-controls">
			<span class="add-to-menu">
				<img class="waiting" src="<?php echo esc_url( admin_url( 'images/wpspin_light.gif' ) ); ?>" alt="" />
				<input type="submit" class="button-secondary submit-add-to-menu" value="<?php esc_attr_e('Add to Menu'); ?>" name="add-custom-menu-item" />
			</span>
		</p>

	</div>
<?php
	}
}     

/**
 * Add 'On Sale Filter to Product list in Admin
 */
add_filter( 'parse_query', 'on_sale_filter' );
function on_sale_filter( $query ) {
    global $pagenow, $typenow, $wp_query;

    if ( $typenow=='product' && isset($_GET['onsale_check']) && $_GET['onsale_check'] ) :

        if ( $_GET['onsale_check'] == 'yes' ) :
            $query->query_vars['meta_compare']  =  '>';
            $query->query_vars['meta_value']    =  0;
            $query->query_vars['meta_key']      =  '_sale_price';
        endif;

        if ( $_GET['onsale_check'] == 'no' ) :
            $query->query_vars['meta_value']    = '';
            $query->query_vars['meta_key']      =  '_sale_price';
        endif;

    endif;
}

add_action('restrict_manage_posts','woocommerce_products_by_on_sale');
function woocommerce_products_by_on_sale() {
    global $typenow, $wp_query;
    if ( $typenow=='product' ) :

        $onsale_check_yes = '';
        $onsale_check_no  = '';

        if ( isset( $_GET['onsale_check'] ) && $_GET['onsale_check'] == 'yes' ) :
            $onsale_check_yes = ' selected="selected"';
        endif;

        if ( isset( $_GET['onsale_check'] ) && $_GET['onsale_check'] == 'no' ) :
            $onsale_check_no = ' selected="selected"';
        endif;

        $output  = "<select name='onsale_check' id='dropdown_onsale_check'>";
        $output .= '<option value="">'.__('Show all products (Sale Filter)', 'woothemes').'</option>';
        $output .= '<option value="yes"'.$onsale_check_yes.'>'.__('Show products on sale', 'woothemes').'</option>';
        $output .= '<option value="no"'.$onsale_check_no.'>'.__('Show products not on sale', 'woothemes').'</option>';
        $output .= '</select>';

        echo $output;

    endif;
}




add_action( 'admin_init', 'yit_woocommerce_update' ); //update image names after woocommerce update
/**
 * Update woocommerce options after update from 1.6 to 2.0
 */
function yit_woocommerce_update() {
	global $woocommerce; 
	
	$field = 'yit_woocommerce_update_' . get_template();
	
	if( get_option($field) == false && version_compare($woocommerce->version,"2.0.0",'>=') ) {
		update_option($field, time());

		//woocommerce 2.0
		update_option( 
			'shop_thumbnail_image_size', 
			array( 
				'width' => get_option('woocommerce_thumbnail_image_width', 90), 
				'height' => get_option('woocommerce_thumbnail_image_height', 90),
				'crop' => get_option('woocommerce_thumbnail_image_crop', 1)
			)
		);
		
		update_option( 
			'shop_single_image_size', 
			array( 
				'width' => get_option('woocommerce_single_image_width', 530 ), 
				'height' => get_option('woocommerce_single_image_height', 345 ),
				'crop' => get_option('woocommerce_single_image_crop', 0)
			) 
		); 
		
		update_option( 
			'shop_catalog_image_size', 
			array( 
				'width' => get_option('woocommerce_catalog_image_width', 150 ), 
				'height' => get_option('woocommerce_catalog_image_height', 150 ),
				'crop' => get_option('woocommerce_catalog_image_crop', 1)
			) 
		);
	}
}

remove_action( 'woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30 );
remove_action( 'woocommerce_before_shop_loop', 'woocommerce_result_count', 20 );
remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_rating', 5 );