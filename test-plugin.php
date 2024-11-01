<?php 

/**
 * A script to test common issues with plugins
 */


/*
 * Constants
*/

$TEST_API = 'connectionTest.php';
$SELF_ERROR_MSG = 'Sorry, there was a problem running this test. Please contact us at Swipe support to let us know you got this error.';

function swipe_curl_call($url){
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	$html = curl_exec($ch);
	curl_close($ch);
	return $html;
}

function testResultHtml($result){
	if($result === true){
		return '<span style="color:green; font-weight: bold;">OK</span>';
	}else if($result === false){
		return '<span style="color:red; font-weight: bold;">Failed</span>';
	}else{
		return '<span style="color:gray">Did not run</span>';
	}
}





/*
 * Configuration
 */

$merchantId 		= isset($_REQUEST['merchant_id']) ? $_REQUEST['merchant_id'] : null;
$apiKey 			= isset($_REQUEST['api_key']) ? $_REQUEST['api_key'] : null;
$apiUrl 			= isset($_REQUEST['api_url']) ? $_REQUEST['api_url'] : null;
$paymentPageUrl 	= isset($_REQUEST['payment_page_url']) ? $_REQUEST['payment_page_url'] : null;
$currency 			= isset($_REQUEST['currency']) ? $_REQUEST['currency'] : null;



/*
 * Test start
 */
try{
	

	
	// Tests Init
	$minRequirementsOK 		= null;
	$basicConnectionTestOK 	= null;
	$credentialsOK 			= null;
	$merchantStatusOK 		= null;
	$pluginConfigOK 		= null;
	
	$basicConnectionTestUrl = $apiUrl . $TEST_API;
	$credentialsUrl = $apiUrl . $TEST_API . '?' . http_build_query(
		array(
			'mode' => 'credentials',
			'merchant_id' => $merchantId,
			'api_key' => $apiKey
		)
	);
	
	$merchantStatusUrl = $apiUrl . $TEST_API . '?' . http_build_query(
		array(
			'mode' => 'merchant_status',
			'merchant_id' => $merchantId,
			'api_key' => $apiKey
		)
	);
	$pluginConfigUrl = $apiUrl . $TEST_API . '?' . http_build_query(
		array(
			'mode' => 'plugin_config',
			'merchant_id' => $merchantId,
			'api_key' => $apiKey,
			'payment_page_url' => $paymentPageUrl,
			'currency' => $currency
		)
	);
		
	// Tests Run 
	$minRequirementsOK = function_exists('curl_version');
	if($minRequirementsOK){
		$basicConnectionTestOK = swipe_curl_call($basicConnectionTestUrl) == 'OK';
	}
	if($basicConnectionTestOK){
		$credentialsOK = swipe_curl_call($credentialsUrl) == 'OK';
	}
	if($credentialsOK){
		$merchantStatusOK = swipe_curl_call($merchantStatusUrl) == 'OK';
	}
	if($merchantStatusOK){
		$pluginConfigResponse = swipe_curl_call($pluginConfigUrl);
		$pluginConfigOK = $pluginConfigResponse == 'OK';
	}
	
	
	?>
	<style type="text/css">
		#test_config_table { font-family: sans-serif; }
		#test_config_table th { text-align: left; background: lightblue; }
		#test_config_table td { font-size: 12px; }
		#test_config_table table { border-collapse: collapse; }
		#test_config_table tr td { padding: 1em 0.5em; border: 1px solid #aaa; }
                #test_config_table tr th { padding: 1em 0.5em; border: 1px solid #aaa; }
		#test_config_table .test-title { white-space: nowrap; font-weight: bold; }
		#test_config_table .test-result { white-space: nowrap; }
		#test_config_table .technical-details { color: #888; margin: 1em; padding: 0.5em; border: 1px solid #aaa; }
	</style>
        
        <div id="test_config_table">
            <table>
                    <thead>
                            <tr>
                                    <th colspan="3">
                                            Swipe Plugin Test
                                    </th>
                            </tr>
                    </thead>
                    <tbody>
                            <tr>
                                    <td class="test-title">
                                            1. Minimum Requirements
                                    </td>
                                    <td class="test-result">
                                            <?php echo testResultHtml($minRequirementsOK); ?>
                                    </td>
                                    <td class="test_info">
                                            <?php if($minRequirementsOK === false){ ?>
                                                    Your shopping cart does not meet the minimum requirements to connect to Swipe. Please contact Swipe support. 
                                                    <div class="technical-details">Technical details: curl is required.</div>
                                            <?php } ?>
                                    </td>
                            </tr>
                            <tr>
                                    <td class="test-title">
                                            2. Connection to Swipe
                                    </td>
                                    <td class="test-result">
                                            <?php echo testResultHtml($basicConnectionTestOK); ?>
                                    </td>
                                    <td class="test_info">
                                            <?php if($basicConnectionTestOK === false){ ?>
                                                    Could not connect to Swipe. Check your API Url: "<?php echo $apiUrl; ?>", comparing it with the API Url 
                                                    in your Swipe Merchant login under Settings -> API Credentials. If you are sure it is correct, Swipe
                                                    may be temporarily unreachable, please try again later.
                                                    <div class="technical-details">
                                                            Technical details: could not connect to: <?php echo htmlentities($basicConnectionTestUrl); ?>
                                                    </div>
                                            <?php } ?>
                                    </td>
                            </tr>
                            <tr>
                                    <td class="test-title">
                                            3. Swipe Merchant Credentials
                                    </td>
                                    <td class="test-result">
                                            <?php echo testResultHtml($credentialsOK); ?>
                                    </td>
                                    <td class="test_info">
                                            <?php if($credentialsOK === false){ ?>
                                                    Your credentials are incorrect. Check your Merchant ID: "<?php echo $merchantId; ?>" and API Key: "<?php echo $apiKey; ?>", 
                                                    comparing them to the details you see in your Swipe Merchant login under Settings -> API Credentials. 
                                                    A common mistake is to have these two swapped around.
                                                    <div class="technical-details">
                                                            Technical details: called: <?php echo $credentialsUrl; ?>
                                                    </div>
                                            <?php } ?>
                                    </td>
                            </tr>
                            <tr>
                                    <td class="test-title">
                                            4. Swipe Merchant Status
                                    </td>
                                    <td class="test-result">
                                            <?php echo testResultHtml($merchantStatusOK); ?>
                                    </td>
                                    <td class="test_info">
                                            <?php if($merchantStatusOK === false){ ?>
                                                    Your Swipe Merchant account is inactive, or does not have the Payment Page enabled. Please contact Swipe support.
                                                    <div class="technical-details">
                                                            Technical details: called: <?php echo $merchantStatusUrl; ?>
                                                    </div>
                                            <?php } ?>
                                    </td>
                            </tr>
                            <tr>
                                    <td class="test-title">
                                            5. Plugin Configuration
                                    </td>
                                    <td class="test-result">
                                            <?php echo testResultHtml($pluginConfigOK); ?>
                                    </td>
                                    <td class="test_info">
                                            <?php if($pluginConfigOK === false){ ?>
                                                    Your plugin configuration is incorrect. Merchant ID, API Key, and API Url are OK, but something else is incorrectly configured.
                                                    Please double check your configuration, if the problem persists please contact Swipe support.
                                                    <div class="technical-details">
                                                            Technical details: <?php echo $pluginConfigResponse; ?> 
                                                            Request: <?php echo htmlentities($pluginConfigUrl); ?>
                                                    </div>
                                            <?php } ?>
                                    </td>
                            </tr>



                    </tbody>	
            </table>
        </div>
	<?php 

}catch(Exception $e){
	echo self::$selfErrorMsg;
}

