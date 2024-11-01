<?php
/*
    Plugin Name: Woo Product Boxes
    Description: Product Boxes is an extension of WooCommerce, that allows to select products, and create a personalized bundles.
    Version: 2.1.1
    Author: Silverwings
*/

if ( ! defined( 'ABSPATH' ) ) exit;

class productsBasketClass
{

    public function __construct()
    {
        add_filter( 'woocommerce_product_data_tabs', 	array( &$this, 'tabBasket') );
        add_action( 'woocommerce_product_data_panels', 	array( &$this, 'productsBasket') );
        add_action( 'woocommerce_process_product_meta', array( &$this, 'productBasketSave') );
        add_filter( 'woocommerce_product_tabs', 		array( &$this, 'tabProductsBasket') );

    }

    function test(){
    	return null;
    }

    // admin function
    function tabBasket( $product_data_tabs ) {
	    $product_data_tabs['products_box'] = array(
	        'label' => __( 'Included products', 'products_box' ),
	        'target' => 'products_basket',
	    );
	    return $product_data_tabs;
	}

	function productsBasket() {
	    global $woocommerce, $post;

	    ?>
	    <div id="products_basket" class="panel woocommerce_options_panel">


	    <p class="form-field product_field_type">
	    	<label for="products_box"><?php _e( 'Included products:', 'woocommerce' ); ?></label>
	        <input type="hidden" class="wc-product-search" style="width: 50%;" id="products_box" name="products_box" data-placeholder="<?php esc_attr_e( 'Search for a product&hellip;', 'woocommerce' ); ?>" data-action="woocommerce_json_search_products" data-multiple="true" data-exclude="<?php echo intval( $post->ID ); ?>" data-selected="<?php
	        $product_ids = array_filter( array_map( 'absint', (array) get_post_meta( $post->ID, 'products_box', true ) ) );
	        $json_ids    = array();

	        foreach ( $product_ids as $product_id ) {
	            $product = wc_get_product( $product_id );
	            if ( is_object( $product ) ) {
	                $json_ids[ $product_id ] = wp_kses_post( html_entity_decode( $product->get_formatted_name(), ENT_QUOTES, get_bloginfo( 'charset' ) ) );
	            }
	        }

	        echo esc_attr( json_encode( $json_ids ) );
	    ?>" value="<?php echo implode( ',', array_keys( $json_ids ) ); ?>" /> <?php echo wc_help_tip( __( 'Select products in the box.', 'woocommerce' ) ); ?>
	    </p>


	    </div>
	    <?php
	}

	function productBasketSave($post_id) {
		$my_product_ids    = isset( $_POST['products_box'] ) ? array_filter( array_map( 'intval', explode( ',', $_POST['products_box'] ) ) ) : array();
    	update_post_meta( $post_id, 'products_box', $my_product_ids  );
	}

	// end


	// front function
	function tabProductsBasket( $tabs ) {
		global $post;
		
		$prodottiBasket = get_post_meta($post->ID,'products_box');

		if (count($prodottiBasket[0]) > 0){		
			$tabs['product_basket'] = array(
				'title' 	=> __( 'Included products ('.count($prodottiBasket[0]).')', 'woocommerce' ),
				'priority' 	=> 50,
				'callback' 	=> array($this, 'tabProductsBasketContent' )
			);

			return $tabs;

		}

	}

	function tabProductsBasketContent() {
		global $post;

		echo '<ul>';
		$prodottiBasket = get_post_meta($post->ID,'products_box');
		$prodotti = '';
		foreach ($prodottiBasket[0] as $key => $value) {
			$prodottiBasketValue = wc_get_product( $value );
			$prodotti .= '<li><a href="'.get_permalink($value).'" target="_new">'.$prodottiBasketValue->get_title().'</a></li>';
		}
		echo $prodotti = preg_replace('/ - $/', '', $prodotti);
		
		echo '</ul>';
		
	}
	// end

}


$cdog_product_options = new productsBasketClass();



?>