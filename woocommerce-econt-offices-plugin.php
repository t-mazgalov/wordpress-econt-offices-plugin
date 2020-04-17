<?php
/*
Plugin Name:    Woocommerce Econt Offices
Plugin URI:
Description:    The plugin allows easy and fast to select city and Econt office or shipping to address.
Version:        1.0
Author:         Todor Mazgalov
License:        GPLv2
*/
define('WEO_URL', WP_PLUGIN_URL."/".dirname( plugin_basename( __FILE__ ) ) );

// Remove default checkout fields
add_filter( 'woocommerce_checkout_fields' , 'weo_remove_default_checkout_fields' );
function weo_remove_default_checkout_fields( $fields ) {
     unset($fields['billing']['billing_first_name']);
     unset($fields['billing']['billing_last_name']);
     unset($fields['billing']['billing_company']);
     unset($fields['billing']['billing_address_1']);
     unset($fields['billing']['billing_address_2']);
     unset($fields['billing']['billing_city']);
     unset($fields['billing']['billing_postcode']);
     //unset($fields['billing']['billing_country']);
     unset($fields['billing']['billing_state']);
     unset($fields['billing']['billing_phone']);

     return $fields;
}


// Add required custom fields
add_action( 'woocommerce_after_checkout_billing_form',
    'weo__add_custom_checkout_field' );
function weo__add_custom_checkout_field( $checkout ) {
    woocommerce_form_field( 'name', array(
        'type'          => 'text',
        'class'         => array('my-field-class form-row-wide'),
        'label'         => __('Име и фамилия'),
        'placeholder'   => __(''),
        'required'      => true,
    ), $checkout->get_value( 'name' ));
    woocommerce_form_field( 'phone', array(
        'type'          => 'text',
        'class'         => array('my-field-class form-row-wide'),
        'label'         => __('Телефон'),
        'placeholder'   => __(''),
        'required'      => true,
    ), $checkout->get_value( 'phone' ));

    // Shipping to custom address
    echo '<div id="to_custom_address">';
    woocommerce_form_field( 'custom_city_address', array(
        'type'          => 'text',
        'class'         => array('hide_when_office'),
        'label'         => __('Град'),
        'placeholder'   => __(''),
        'required'      => true,
    ), $checkout->get_value( 'custom_city_address' ));
    woocommerce_form_field( 'custom_address', array(
        'type'          => 'text',
        'class'         => array('hide_when_office'),
        'label'         => __('Адрес'),
        'placeholder'   => __(''),
        'required'      => true,
    ), $checkout->get_value( 'custom_address' ));
    echo '</div>';

    // Shipping to office
    echo '<div id="to_office">';
    woocommerce_form_field( 'city_office', array(
        'type'          => 'text',
        'class'         => array('my-field-class form-row-wide'),
        'label'         => __('Град'),
        'placeholder'   => __(''),
        'required'      => true,
    ), $checkout->get_value( 'city_office' ));

    woocommerce_form_field( 'office', array(
        'type'          => 'select',
        'class'         => array('my-field-class form-row-wide'),
        'label'         => __('Офис <span style="color:red;">(Първо въведете град на кирилица)</span>'),
        'placeholder'   => __(''),
        'required'      => true,
        'options'       => array('Въведете град'),
    ), $checkout->get_value( 'office' ));
    echo '</div>';
}

// Office or custom address radio buttons
add_action('woocommerce_before_checkout_billing_form', 'weo_shipping_mode_selector',1);
function weo_shipping_mode_selector() {
?>
<span>Изберете, доставка до <strong>адрес</strong> или <strong>офис на еконт</strong></span><br />
<style>
#to_office, #to_custom_address {
    display: none;
}

#office li,
.ui-autocomplete li,
.ui-autocomplete option,
.ui-autocomplete ul {
    background-color: #f0f0f0 !important;
    list-style-type: none;
    max-width: 450px !important;
    line-height: 2em;
}

.shipping_mode_selector {
    width: 100%;
}

.shipping_mode_input {
  display: none;
}

.shipping_mode_input + label {
    display: inline-block;
    padding: 10px 0;
    position: relative;
    text-align: center;
    transition: background 600ms ease, color 600ms ease;
    cursor: pointer;
    min-width: 46%;
}

.shipping_mode_input + label:after {
    background: #00858C;
    content: "";
    height: 100%;
    position: absolute;
    top: 0;
    transition: left 200ms cubic-bezier(0.77, 0, 0.175, 1);
    width: 100%;
    z-index: -1;
}

.shipping_mode_input.toggle-left + label:after {
    left: 100%;
}

.shipping_mode_input.toggle-right + label:after {
    left: -100%;
}

.shipping_mode_input:checked + label {
    cursor: default;
    color: #fff;
    transition: color 200ms;
}

.shipping_mode_input:checked + label:after {
    left: 0;
}

.shipping_mode_input:not(:checked) + label{
    background-color: #f0f0f0;
}
</style>
<br />
<div
    class="shipping_mode_selector">
    <input
        type="radio"
        class="shipping_mode_input toggle-left"
        name="shipping_mode"
        checked="checked"
        value="office"
        id="radio_office"/>
    <label
        for="radio_office"
        class="shipping_mode_label">
        Офис на Еконт
    </label>

    <input
        type="radio"
        class="shipping_mode_input toggle-right"
        name="shipping_mode"
        value="address"
        id="radio_address"/>
    <label
        for="radio_address"
        class="shipping_mode_label">
        Личен адрес
    </label>
</div>
<br/>
<?php
}

// Update the order meta with field value
add_action( 'woocommerce_checkout_update_order_meta', 'weo_custom_checkout_field_update_order_meta' );
function weo_custom_checkout_field_update_order_meta( $order_id ) {
    if ( ! empty( $_POST['name'] ) ) {
        update_post_meta( $order_id, '_billing_first_name', sanitize_text_field( $_POST['name'] ) );
    }
    if ( ! empty( $_POST['phone'] ) ) {
        update_post_meta( $order_id, '_billing_phone', sanitize_text_field( $_POST['phone'] ) );
    }
    if ( ! empty( $_POST['custom_city_address'] ) ) {
        update_post_meta( $order_id, '_billing_city', sanitize_text_field( $_POST['custom_city_address'] ) );
    }
    if ( ! empty( $_POST['custom_address'] ) ) {
        update_post_meta( $order_id, '_billing_address_1', sanitize_text_field( $_POST['custom_address'] ) );
    }
    if ( ! empty( $_POST['city_office'] ) ) {
        update_post_meta( $order_id, '_billing_city', sanitize_text_field( $_POST['city_office'] ) );
    }
    if ( ! empty( $_POST['office'] ) ) {
        update_post_meta( $order_id, '_billing_address_1', sanitize_text_field( $_POST['office'] ) );
    }
    if( $_POST['shipping_mode'] != ''){
        update_post_meta( $order_id, 'shipping_mode',sanitize_text_field( $_POST['shipping_mode']) );
    }
}

// Add validation errors
add_action( 'woocommerce_checkout_process', 'weo_woocommerce_add_error' );
function weo_woocommerce_add_error(  ) {
    if( empty($_POST['shipping_mode']) ) {
        wc_add_notice(
            __('Изберете начин на доставка: офис или личен адрес',
            'woocommerce'),
            'error' );
    }
    if( $_POST['shipping_mode'] == 'office' ) {
        if( empty( $_POST['name']) ){
            wc_add_notice( __('Попълнете име и фамилия','woocommerce'), 'error' );
        }
        if( empty( $_POST['city_office']) ){
            wc_add_notice( __('Попълнете град','woocommerce'), 'error' );
        }
        if( empty($_POST['office']) ){
            wc_add_notice( __('Изберете Офис','woocommerce'),  'error' );
        }
        if( empty($_POST['phone']) ){
            wc_add_notice( __('Попълнете телефон','woocommerce'),  'error' );
        }
    }
    if( $_POST['shipping_mode'] == 'address' ) {
        if( empty( $_POST['phone']) ){
            wc_add_notice( __('Попълнете телефон','woocommerce'), 'error' );
        }
        if( empty($_POST['custom_address']) ){
            wc_add_notice( __('Попълнете адрес','woocommerce'),  'error' );
        }
        if( empty($_POST['custom_city_address']) ){
            wc_add_notice( __('Попълнете град','woocommerce'),  'error' );
        }
    }
}

// Add jQuery-Autocomplete library
add_action('wp_enqueue_scripts', 'weo_scripts');
function weo_scripts() {
  wp_register_script('custom_script',
      WEO_URL.'/js/auto-complete.js',
      array( 'jquery' ),
      false,
      true);
  wp_enqueue_script('custom_script');
}

add_action('wp_enqueue_scripts', 'jquery_library');
function jquery_library() {
   wp_register_script('library_script',
       'https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/jquery-ui.min.js');
   wp_enqueue_script('library_script');
}
?>
