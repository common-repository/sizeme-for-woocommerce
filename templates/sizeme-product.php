<?php
/**
 * SizeMe for WooCommerce JavaScript
 *
 * Adds the necessary JavaScript for creating the product and UI options.
 *
 * @package SizeMe for WooCommerce
 * @since   1.0.0
 *
 * @var WC_SizeMe_for_WooCommerce $sizeme  The SizeMe for WooCommerce object.
 * @var WC_Product_Variable    $product The variable product object.
 */

/**
 * SizeMe for WooCommerce is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 *
 * SizeMe for WooCommerce is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with SizeMe for WooCommerce. If not, see http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>

<script type="text/javascript" data-no-optimize="1">
	//<![CDATA[
	var sizeme_options = {
		serviceStatus: "<?php echo esc_js( $sizeme->get_service_status() ); ?>",
		pluginVersion: "WC-<?php echo WC_SizeMe_for_WooCommerce::VERSION; ?>",
		shopType: "woocommerce",
		clientKey: "<?php echo esc_js( $sizeme->get_client_key() ); ?>",
		uiOptions: {}
	};

	<?php
	// TEST MODE
	if ( $sizeme->is_service_test() ) {
		echo 'sizeme_options.debugState = "true";'.PHP_EOL;
	}

	// UI OPTIONS
	$uiOptions = array(
		'appendContentTo' => WC_SizeMe_for_WooCommerce::APPEND_CONTENT_TO,
		'invokeElement' => WC_SizeMe_for_WooCommerce::INVOKE_ELEMENT,
		'sizeSelectorType' => WC_SizeMe_for_WooCommerce::SIZE_SELECTION_TYPE,
		'addToCartElement' => WC_SizeMe_for_WooCommerce::ADD_TO_CART_ELEMENT,
		'addToCartEvent' => WC_SizeMe_for_WooCommerce::ADD_TO_CART_EVENT,
		'lang' => WC_SizeMe_for_WooCommerce::LANG_OVERRIDE,
		'measurementUnit' => WC_SizeMe_for_WooCommerce::MEASUREMENT_UNIT,
	);

	foreach ($uiOptions as $key => $value) {
		if ( $sizeme->get_ui_option( $value, '' ) ) {
			printf('sizeme_options.uiOptions.%s = "%s";'.PHP_EOL, $key, esc_js($sizeme->get_ui_option( $value, '' )));
		}
	}

	// TOGGLER
	if ( $sizeme->is_toggler_yes( WC_SizeMe_for_WooCommerce::ADD_TOGGLER ) ) {
		echo 'sizeme_options.uiOptions.toggler = true;'.PHP_EOL;
	}

	// ADDITIONAL TRANSLATIONS (from the UI OPTIONS array)
	$trans = trim( $sizeme->get_ui_option( WC_SizeMe_for_WooCommerce::ADDITIONAL_TRANSLATIONS, '' ) );
	if ( !empty($trans) ) echo 'sizeme_options.additionalTranslations = {' . trim( $trans ) . '};'.PHP_EOL;

	// possible MAX_RECOMMENDATION_DISTANCE (from the UI OPTIONS array)
	$dist = intval( $sizeme->get_ui_option( WC_SizeMe_for_WooCommerce::MAX_RECOMMENDATION_DISTANCE, '' ) );
	if ( $dist > 0 ) printf('sizeme_options.uiOptions.maxRecommendationDistance = %d;'.PHP_EOL, $dist );

	// possible measurementUnitChoiceDisallowed
	if ( $sizeme->is_toggler_yes( WC_SizeMe_for_WooCommerce::MEASUREMENT_UNIT_CHOICE_DISALLOWED ) ) {
		echo 'sizeme_options.uiOptions.measurementUnitChoiceDisallowed = true;'.PHP_EOL;
	}

	// possible flat measurement setting
	if ( $sizeme->is_toggler_no( WC_SizeMe_for_WooCommerce::FLAT_MEASUREMENTS ) ) {
		echo 'sizeme_options.uiOptions.flatMeasurements = false;'.PHP_EOL;
	}

	// possible string
	$gender_string = trim( $sizeme->get_ui_option( WC_SizeMe_for_WooCommerce::MATCH_GENDER_FROM_NAME_MALE, '' ) );
	if ( !empty($gender_string) ) echo 'sizeme_options.uiOptions.matchGenderFromNameMale = "' . htmlspecialchars( $gender_string ) . '";'.PHP_EOL;

	?>

	var sizeme_product = {
		name: "<?php echo esc_js( $product->get_formatted_name() ); ?>",
		SKU: "<?php echo esc_js( strtoupper( ($product->get_sku() ? $product->get_sku() : substr($sizeme->get_client_key(), 0, 16).'-'.$product->get_id() ) ) ); ?>",
		item: {
<?php
	$items = $sizeme->get_variation_sizeme_skus( $product );
	if ($items):
		foreach ( $items as $size_attribute => $sku ) :
?>
			"<?php echo esc_js( strtoupper( $sku ) ); ?>" : "<?php echo esc_js( $size_attribute ); ?>",
<?php
		endforeach;
	endif;
?>
		}
	};
<?php
	//
	// Add listener for sizemeChange and trigger local jQuery change event
	// Requires that jQuery is defined before this (footer is not ok)
	//
	// @since 2.0.3
	//
	$el = '.variations select';
	if ( $sizeme->get_ui_option( WC_SizeMe_for_WooCommerce::INVOKE_ELEMENT, '' ) ) $el = $sizeme->get_ui_option( WC_SizeMe_for_WooCommerce::INVOKE_ELEMENT, '' );
	echo 'if (window.jQuery) { jQuery(function() { if (document.querySelector("'.$el.'")) document.querySelector("'.$el.'").addEventListener("sizemeChange", function(e) { jQuery("'.$el.'").trigger("change"); }) } ); }';

	//
	// Also add support for possible clear size selection button
	// Includes a timeout to let Woo do it's thing (clear the selection)
	//
	// @since 2.1.2
	//
	echo 'if (window.jQuery) jQuery(function() { jQuery(".reset_variations").click(function() { if (document.querySelector("'.$el.'")) { setTimeout(function() { document.querySelector("'.$el.'").dispatchEvent(new Event("change")); }, 200); } } ); } );';
?>
	//]]>
</script>

<?php
// write possible custom css (placement questionable)
$css = trim( $sizeme->get_ui_option( WC_SizeMe_for_WooCommerce::CUSTOM_CSS, '' ) );
if ( !empty($css) ) echo '<style type="text/css">' . trim( $css ) . '</style>'.PHP_EOL;

