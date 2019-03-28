<?php
/**
 * Plugin Name: Give - DotPay płatności
 * Plugin URI: https://www.awwwesome.online
 * Description: Integracja Give i DotPay
 * Version: 1.0
 * Author: awwwesome
 * Author URI: https://www.awwwesome.online
 **/

if (!defined('ABSPATH')) {
    exit;
}

add_filter('give_payment_gateways', 'gp_dotpay_add_to_gateways_list');

function gp_dotpay_add_to_gateways_list($gateways)
{
    
    $gateways['dotpay'] = array(
        'admin_label' => __('DotPay', 'give-dotpay'),
        'checkout_label' => __('DotPay', 'give-dotpay')
    );
    
    return $gateways;
}

add_filter('give_get_sections_gateways', 'gp_dotpay_add_to_tab');

function gp_dotpay_add_to_tab($sections)
{
    $sections['dotpay'] = 'DotPay';
    return $sections;
}

add_filter('give_get_settings_gateways', 'gp_dotpay_settings');

function dotpay_url_api()
{
    
    if (give_get_option('give_dotpay_test') == 'enabled') {
        //tryb testowy
        
        return 'https://ssl.dotpay.pl/test_payment/';
        
    } else {
        
        return 'https://ssl.dotpay.pl/t2/';
        
    }
    
}

function gp_dotpay_settings($settings)
{
    
    $current_section = give_get_current_setting_section();
    
    
    
    if ('dotpay' === $current_section) {
        $settings = array(
            array(
                'id' => 'give_dotpay_payments_setting',
                'type' => 'title'
            ),
            array(
                'title' => __('DotPay ID', 'give-dotpay'),
                'id' => 'give_dotpay_id',
                'type' => 'text',
                'desc' => __('ID konta DotPay', 'give-dotpay')
            ),
            array(
                'title' => __('DotPay PIN', 'give-dotpay'),
                'id' => 'give_dotpay_pin',
                'type' => 'text',
                'desc' => __('DotPay PIN', 'give-dotpay')
            ),
            array(
                'title' => __('DotPay ID (test)', 'give-dotpay'),
                'id' => 'give_dotpay_id_test',
                'type' => 'text',
                'desc' => __('ID konta testowego DotPay', 'give-dotpay')
            ),
            array(
                'title' => __('DotPay PIN (test)', 'give-dotpay'),
                'id' => 'give_dotpay_pin_test',
                'type' => 'text',
                'desc' => __('DotPay PIN testowy', 'give-dotpay')
            ),
            array(
                'title' => __('Testowanie płatności', 'give-dotpay'),
                'id' => 'give_dotpay_test',
                'type' => 'radio_inline',
                'options' => array(
                    'enabled' => esc_html__('Włączone', 'give-dotpay'),
                    'disabled' => esc_html__('Wyłączone', 'give-dotpay')
                ),
                'default' => 'disabled',
                'description' => __('Funkcja testowania płatności dotpay. Wymagane jest konto do testowania w serwisie dotpay.pl. <a href="https://ssl.dotpay.pl/test_seller/test/registration/" target="_blank">Załóż konto tutaj</a>', 'give-dotpay')
            ),
            
            array(
                'id' => 'give_dotpay_payments_setting',
                'type' => 'sectionend'
            )
        );
    }
    
    return $settings;
    
}

function give_dotpay_cc_form_callback($form_id)
{
    return false;
}
add_action('give_dotpay_cc_form', 'give_dotpay_cc_form_callback');

/** This action will run the function attached to it when it's time to process the donation submission. **/

add_action('give_gateway_dotpay', function($purchase_data)
{
    $payment_data = array(
        'price' => $purchase_data['price'],
        'give_form_title' => $purchase_data['post_data']['give-form-title'],
        'give_form_id' => intval($purchase_data['post_data']['give-form-id']),
        'give_price_id' => isset($purchase_data['post_data']['give-price-id']) ? $purchase_data['post_data']['give-price-id'] : '',
        'date' => $purchase_data['date'],
        'user_email' => $purchase_data['user_email'],
        'purchase_key' => $purchase_data['purchase_key'],
        'currency' => give_get_currency(),
        'user_info' => $purchase_data['user_info'],
        'status' => 'pending',
        'gateway' => 'dotpay'
    );
    $payment      = give_insert_payment($payment_data);
 if(give_get_option('give_dotpay_test') == 'enabled'){
	$dotpay_pin  = give_get_option('give_dotpay_pin_test');
} else {
	$dotpay_pin  = give_get_option('give_dotpay_pin');
}
    $api_version = 'dev';
if(give_get_option('give_dotpay_test') == 'enabled'){
	$dotpay_id   = give_get_option('give_dotpay_id_test');
} else {
	$dotpay_id   = give_get_option('give_dotpay_id');
}
    $amount      = $purchase_data['price'];
    $currency    = give_get_currency();
    $desc        = 'Darowizna-Form:'.intval($purchase_data['post_data']['give-form-id']).'-Give:'.$payment.'';
    $url         = get_permalink(give_get_option('success_page'));
    $urlc        = '' . plugin_dir_url(__FILE__) . 'check-dotpay.php';
    $control     = $purchase_data['purchase_key'];
    $type        = 0;
    
    $to_chk = $dotpay_pin . $api_version . $dotpay_id . $amount . $currency . $desc . $control . $url . $type . $urlc . $purchase_data['user_info']['first_name'] . $purchase_data['user_info']['last_name'] . $purchase_data['user_email'];
    $chk    = hash('sha256', $to_chk);
    
    $dotapy_payment_url = dotpay_url_api() . '?api_version=' . $api_version . '&id=' . $dotpay_id . '&amount=' . $amount . '&currency=' . $currency . '&description=' . $desc . '&URL=' . $url . '&URLC=' . $urlc . '&control=' . $control . '&type=0&firstname='.$purchase_data['user_info']['first_name'].'&lastname='.$purchase_data['user_info']['last_name'].'&email='.$purchase_data['user_email'].'&chk=' . $chk . '';
    
    if ($payment) {
        give_update_payment_status($payment, 'pending');
        wp_redirect($dotapy_payment_url);
    } else {
        give_send_back_to_checkout('?payment-mode=' . $purchase_data['post_data']['give-gateway']);
    }
});