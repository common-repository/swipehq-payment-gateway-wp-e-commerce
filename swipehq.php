<?php
    /*
        Plugin Name: Swipe Checkout for WP e-Commerce
        Plugin URI: http://www.swipehq.com
        Description: A payment plugin for Wordpress e-Commerce
        Version:  3.1.0
        Author: Swipe
        Author URI: http://www.swipehq.com
        License: GPL2
    */
	error_reporting(E_ALL);
	
	define('SWIPEHQ_WPEC_NAME', 'swipehq_wpec');

	
	
	
	add_filter('plugin_action_links', 'swipehq_wpec_action_links', 10, 2 );
	add_action('parse_request', 'swipehq_wpec_parserequest' );
	add_action('init', 'swipehq_wpec_check_lpn_response');
	add_action('admin_notices', 'swipehq_wpec_admin_notices' );
	
	function swipehq_wpec_action_links( $links, $pluginLink ){
		if(strpos($pluginLink, 'swipehq.php') === false) return $links;
		$plugin_links = array(
				'<a href="' . admin_url( 'admin.php?page=wpsc-settings&tab=gateway&payment_gateway_id=swipehq_wpec' ) . '">' . __( 'Settings', 'Optimizer' ) . '</a>',
		);
		return array_merge( $plugin_links, $links );
	}
	
	
	
	
	
	/*
	 * This is the gateway variable $nzshpcrt_gateways, it is used for displaying gateway information on the wp-admin pages and also
	 * for internal operations.
	 */
	global $nzshpcrt_gateways;
	global $num;
	
    $nzshpcrt_gateways[$num] = array(
        'name'              => 'Swipe Checkout',
        'internalname'      => SWIPEHQ_WPEC_NAME,
        'function'          => 'gateway_'.SWIPEHQ_WPEC_NAME,
        'form'              => 'form_'.SWIPEHQ_WPEC_NAME,
        'submit_function'   => 'submit_'.SWIPEHQ_WPEC_NAME,
        'payment_type'      => 'credit_card',
        'display_name'      => __( 'Swipe Checkout', 'swipehq' ),
        'image'             => plugins_url( 'checkout-logo.png', __FILE__ )
    );
    

    //Handler for swipehq=redirect
    function swipehq_wpec_parserequest( &$wp ) {
        if(isset($_REQUEST['swipehq']) && $_REQUEST['swipehq'] == 'redirect'){
           switch($_REQUEST['result']){
                case 'accepted':
                    sleep(2);
                    unset( $_SESSION['WpscGatewayErrorMessage'] );
                    $transaction_url_with_sessionid = add_query_arg( 'sessionid', $_REQUEST['user_data'], get_option( 'transact_url' ) );
                    wp_redirect( $transaction_url_with_sessionid );
                break;
                case 'test-accepted':
                    sleep(2);
                    unset( $_SESSION['WpscGatewayErrorMessage'] );
                    $transaction_url_with_sessionid = add_query_arg( 'sessionid', $_REQUEST['user_data'], get_option( 'transact_url' ) );
                    wp_redirect( $transaction_url_with_sessionid );
                break;
                case 'declined':
                    sleep(2);
                    $_SESSION['WpscGatewayErrorMessage'] = __( 'Transaction Declined. We\'re sorry, but the transaction has failed.');
                    wp_redirect( get_option( 'transact_url' ) );
                break;
                default:
                    sleep(2);
                    $_SESSION['WpscGatewayErrorMessage'] = __( 'Transaction Declined. We\'re sorry, but the transaction has failed.');
                    wp_redirect( get_option( 'transact_url' ) );
                break;
           }
           exit();
        }
    }



    function swipehq_wpec_post_to_url($url, $body) {
         $ch = curl_init ($url);
         curl_setopt ($ch, CURLOPT_POST, 1);
         curl_setopt ($ch, CURLOPT_POSTFIELDS, $body);
         curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
         $html = curl_exec ($ch);
         curl_close ($ch);
         return $html;
    }

    function form_swipehq_wpec(){
        return '
        <tr>
        	<td>Merchant ID:</td>
        	<td>
        		<input type="text" name="'.SWIPEHQ_WPEC_NAME.'_merchant_id" value="'.get_option(SWIPEHQ_WPEC_NAME.'_merchant_id').'" >
				<p class="description">Find this in your Swipe Merchant login under Settings -> API Credentials</p>
        	</td>
        </tr>
        <tr>
        	<td>API Key:</td>
        	<td>
        		<input type="text" name="'.SWIPEHQ_WPEC_NAME.'_api_key" value="'.get_option(SWIPEHQ_WPEC_NAME.'_api_key').'" >
        		<p class="description">Find this in your Swipe Merchant login under Settings -> API Credentials</p>
        	</td>
        </tr>
        <tr>
        	<td>Api Url:</td>
        	<td>
        		<input type="text" name="'.SWIPEHQ_WPEC_NAME.'_api_url" value="'.get_option(SWIPEHQ_WPEC_NAME.'_api_url').'" >
        		<p class="description">Find this in your Swipe Merchant login under Settings -> API Credentials</p>
        	</td>
        </tr>
        <tr>
        	<td>Payment Page Url:</td>
        	<td>
        		<input type="text" name="'.SWIPEHQ_WPEC_NAME.'_payment_page_url" value="'.get_option(SWIPEHQ_WPEC_NAME.'_payment_page_url').'" >
        		<p class="description">Find this in your Swipe Merchant login under Settings -> API Credentials</p>
        	</td>
        </tr>
        
        <script>
            function check_config(){
                var elementToRemove = jQuery("#check_config_results");
                if(elementToRemove!=null && typeof(elementToRemove)!="undefined"){
                    elementToRemove.remove();
                }            

                var formDiv = jQuery("#gateway_settings_swipehq_wpec_form");
                
                var elementToInsert = document.createElement("div");
                elementToInsert.setAttribute("id", "check_config_results");
                elementToInsert.setAttribute("style", "width:100%;height:100%");
                elementToInsert.innerHTML = "<p style=\"line-height:1;font-size:50px\">Checking config, please wait...</p>";
                jQuery(formDiv).append(elementToInsert);



                var merchantId = jQuery("input[name=\"swipehq_wpec_merchant_id\"]").val();
                var apiKey = jQuery("input[name=\"swipehq_wpec_api_key\"]").val();
                var apiURL = jQuery("input[name=\"swipehq_wpec_api_url\"]").val();
                var paymentURL = jQuery("input[name=\"swipehq_wpec_payment_page_url\"]").val();


                var currencySelected = "'.swipehq_wpec_get_currency().'";


                var testUrl = "'.plugins_url( 'test-plugin.php', __FILE__ ).'";


                var urlToLoad = testUrl+"?merchant_id="+merchantId+"&api_key="+apiKey+"&api_url="+apiURL+"&payment_page_url="+paymentURL+"&currency="+currencySelected;

                
                jQuery("#check_config_results").load(urlToLoad);

           }        

            jQuery(document).ready(function(){
                var formDiv = jQuery("#gateway_settings_swipehq_wpec_form");
                

                if(formDiv!=null && typeof(formDiv)!="undefined"){
                    if(formDiv.length==1){
                        var buttonToInsert = document.createElement("input");
                        buttonToInsert.setAttribute("type", "button");
                        buttonToInsert.setAttribute("style", "margin-top: 20px;font-size:20px;");
                        buttonToInsert.setAttribute("value", "Check Config");
                        buttonToInsert.setAttribute("name", "checkconfig");
                        buttonToInsert.setAttribute("onclick", "check_config();");


                        formDiv.append(buttonToInsert);
                    }
                }
            });
        </script>
        ';
        return $output;
    }

    function submit_swipehq_wpec(){
        if(trim($_POST[SWIPEHQ_WPEC_NAME.'_merchant_id']) <> ''){
            update_option(SWIPEHQ_WPEC_NAME.'_merchant_id', $_POST[SWIPEHQ_WPEC_NAME.'_merchant_id']);
        }
        if(trim($_POST[SWIPEHQ_WPEC_NAME.'_api_key']) <> ''){
            update_option(SWIPEHQ_WPEC_NAME.'_api_key', $_POST[SWIPEHQ_WPEC_NAME.'_api_key']);
        }
        if(trim($_POST[SWIPEHQ_WPEC_NAME.'_api_url']) <> ''){
            update_option(SWIPEHQ_WPEC_NAME.'_api_url', trim($_POST[SWIPEHQ_WPEC_NAME.'_api_url']));
        }
        if(trim($_POST[SWIPEHQ_WPEC_NAME.'_payment_page_url']) <> ''){
            update_option(SWIPEHQ_WPEC_NAME.'_payment_page_url', trim($_POST[SWIPEHQ_WPEC_NAME.'_payment_page_url']));
        }
        
        return true;
    }

    function gateway_swipehq_wpec($seperator, $sessionid){
        global $wpdb, $wpsc_cart;

        //This grabs the purchase log id from the database that refers to the $sessionid
        $purchase_log = $wpdb->get_row("SELECT * FROM `".WPSC_TABLE_PURCHASE_LOGS."` WHERE `sessionid`= ".$sessionid." LIMIT 1",ARRAY_A);

        //This grabs the users info using the $purchase_log from the previous SQL query
        $usersql = "SELECT `".WPSC_TABLE_SUBMITED_FORM_DATA."`.value,`".WPSC_TABLE_CHECKOUT_FORMS."`.`name`,`".WPSC_TABLE_CHECKOUT_FORMS."`.`unique_name` FROM `".WPSC_TABLE_CHECKOUT_FORMS."` LEFT JOIN `".WPSC_TABLE_SUBMITED_FORM_DATA."` ON `".WPSC_TABLE_CHECKOUT_FORMS."`.id = `".WPSC_TABLE_SUBMITED_FORM_DATA."`.`form_id` WHERE `".WPSC_TABLE_SUBMITED_FORM_DATA."`.`log_id`=".$purchase_log['id']." ORDER BY `".WPSC_TABLE_CHECKOUT_FORMS."`.`order`";
        $userinfo = $wpdb->get_results($usersql, ARRAY_A);

        $data = array();

        $product_details = '';
        foreach($wpsc_cart->cart_items as $i => $Item) {
            $product_details .= $Item->quantity . ' x ' . $Item->product_name . '<br/>';
        }
        
        $params = array (
                'merchant_id'           => get_option(SWIPEHQ_WPEC_NAME.'_merchant_id'),
                'api_key'               => get_option(SWIPEHQ_WPEC_NAME.'_api_key'),
                'td_item'               => $sessionid,
                'td_description'        => $product_details,
                'td_amount'             => $wpsc_cart->total_price,
                'td_default_quantity'   => 1,
                'td_user_data'          => $sessionid,
                'td_currency'           => swipehq_wpec_get_currency()
        );
        
        $response = swipehq_wpec_post_to_url(trim(get_option(SWIPEHQ_WPEC_NAME.'_api_url'),'/').'/createTransactionIdentifier.php', $params);
        $response_data = json_decode($response);
        
        switch($response_data->response_code){
            case 400:
                 wp_die( __( 'API Access Denied', 'swipehq' ) );
            break;
            case 402:
                wp_die( __( 'API System Error', 'swipehq' ) );
            break;
            case 403:
                wp_die( __( 'Not Enough Parameters', 'swipehq' ) );
            break;
            case 404:
                wp_die( __( 'API result missing', 'swipehq' ) );
            break;
            case 407:
                wp_die( __( 'Inactive Swipe HQ Checkout Account', 'swipehq' ) );
            break;
            case 200:
                if( headers_sent( ) )
                    echo '<script type="text/javascript">location.href="'.trim(get_option(SWIPEHQ_WPEC_NAME.'_payment_page_url'),'/').'/?checkout=true&identifier_id='.$response_data->data->identifier.'";</script>';
                else
                    header( 'Location: '.trim(get_option(SWIPEHQ_WPEC_NAME.'_payment_page_url'),'/').'/?checkout=true&identifier_id='.$response_data->data->identifier );
		exit;
            break;
            default:
                wp_die( __( 'There has been a problem connecting to API server', 'swipehq' ) );
            break;
        }

        exit();
        
    }

    function swipehq_wpec_check_lpn_response(){
        if($_REQUEST){
            global $wpdb, $wpsc_cart;
            $posted = $_REQUEST;
            
            if(isset($posted['status']) && isset($posted['identifier_id']) && isset($posted['transaction_id']) && isset($posted['td_user_data'])){

                //Validate Transaction
                $params = array(
                    'merchant_id'       => get_option(SWIPEHQ_WPEC_NAME.'_merchant_id'),
                    'api_key'           => get_option(SWIPEHQ_WPEC_NAME.'_api_key'),
                    'transaction_id'    => $posted['transaction_id'],
                    'identifier_id'     => $posted['identifier_id']
                );
                $response = swipehq_wpec_post_to_url(trim(get_option(SWIPEHQ_WPEC_NAME.'_api_url'),'/').'/verifyTransaction.php', $params);
                $response_data = json_decode($response);
                if($response_data->response_code == 200){
                    if(($response_data->data->status == 'accepted' || $response_data->data->status == 'test-accepted') && $response_data->data->transaction_approved == 'yes'){
                        $is_test = ($response_data->data->status == 'test-accepted')? ' Test ' : ' ';
                        $sql = "UPDATE `".WPSC_TABLE_PURCHASE_LOGS."` SET `processed`= '3',notes = 'Swipe".$is_test."Payment has been accepted.".$is_test."Transaction ID: ".$posted['transaction_id']."' WHERE `sessionid`=".$posted['td_user_data'];
                        $wpdb->query($sql);
                    }
                    else{
                        $sql = "UPDATE `".WPSC_TABLE_PURCHASE_LOGS."` SET `processed`= '6',notes = 'Swipe Payment has been declined. Transaction ID: ".$posted['transaction_id']."' WHERE `sessionid`=".$posted['td_user_data'];
                        $wpdb->query($sql);
                    }
                }

            }
        }
    }
    
    function swipehq_wpec_get_accepted_currencies(){
    	$api_url = trim(get_option(SWIPEHQ_WPEC_NAME.'_api_url'), '/');
    	$merchant_id = get_option(SWIPEHQ_WPEC_NAME.'_merchant_id');
    	$api_key = get_option(SWIPEHQ_WPEC_NAME.'_api_key');
    	
    	if($api_url && $api_key && $merchant_id){
    		$params = array(
    				'merchant_id'       => $merchant_id,
    				'api_key'           => $api_key,
    		);
    		$response = swipehq_wpec_post_to_url($api_url.'/fetchCurrencyCodes.php', $params);
    		$response_data = json_decode($response, true);
    		return $response_data['data'];
    	}else{
    		return null;
    	}
    }
    
    function swipehq_wpec_admin_notices(){
    	// check currency
    	$currency = swipehq_wpec_get_currency();
    	$acceptedCurrencies = swipehq_wpec_get_accepted_currencies();
    	if($acceptedCurrencies && !in_array($currency, $acceptedCurrencies)){
    		echo '<div class="error"><p>' .
    				__('3 Swipe Checkout does not support currency: '.$currency.'. Swipe supports these currencies: '.join(', ', $acceptedCurrencies).'.') .
    			 '</p></div>';
    	}
    }
    
    function swipehq_wpec_get_currency(){
    	global $wpdb;
    	
    	$currency_id = get_option('currency_type');
    	if(!$currency_id) return null;
    	
    	$currency_data = $wpdb->get_results( "SELECT * FROM `" . WPSC_TABLE_CURRENCY_LIST . "` ORDER BY `country` ASC", ARRAY_A );
    	foreach($currency_data as $c){
    		if($c['id'] == $currency_id){
    			return $c['code'];
    		}
    	}
    	
    	return null;
    }
    


