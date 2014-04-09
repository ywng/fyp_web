<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

include(APPPATH . 'libraries/SNS.php');

if (!function_exists('sns_apple_register_endpoint')) {

	function sns_apple_register_endpoint($type, $amazon_key, $amazon_secret, $deviceName, $token) {

		$CI =& get_instance();
		$CI->config->load('amazon');

		$AmazonSNS = new AmazonSNS($amazon_key, $amazon_secret);
		$AmazonSNS->setRegion('AP-SE-1');
		if (strtolower($type) == 'passenger') {
			$applePlatformArn = $CI->config->item('sns_passenger_applicationArn');
		} else if (strtolower($type) == 'driver') {
			$applePlatformArn = $CI->config->item('sns_driver_applicationArn');	
		}
		
		try {

			$deviceEndpointArn = $AmazonSNS->createPlatformEndpoint($deviceName, $applePlatformArn, $token);
			return $deviceEndpointArn;

		} catch (SNSException $e) {
			// Amazon SNS returned an error
		    error_log('SNS returned the error "' . $e->getMessage() . '" and code ' . $e->getCode());
		    return "";
		} catch (APIException $e) {
		    // Problem with the API
		    error_log('There was an unknown problem with the API, returned code ' . $e->getCode());
		    return "";
		} catch (Exception $e) {
			// General Exception
			error_log('General Exception ' . $e->getMessage() . " code " . $e->getCode());
			return "";
		}
	}

}


if (!function_exists('sns_apple_push_notification_message')) {

	function sns_apple_push_notification_message($amazon_key, $amazon_secret, $targetEndpointARN, $message, $additional_detail = NULL, $sound = 'default', $badge_number = 1) {
		$AmazonSNS = new AmazonSNS($amazon_key, $amazon_secret);
		$AmazonSNS->setRegion('AP-SE-1');

		$json_payload = array( 
			'aps'=> array(
				'alert' => $message,
				'sound' => $sound,
				'badge' => $badge_number,
				)
			);
		if ($additional_detail != NULL) {
			$json_payload['d'] = $additional_detail;
		}

		$body['APNS'] = json_encode($json_payload);
		$body['default'] = $message;

		try {
			$jsonBody =  json_encode($body);
			$AmazonSNS->publishToEndpoint($jsonBody, $targetEndpointARN);

		} catch(SNSException $e) {
		    // Amazon SNS returned an error
		    error_log('SNS returned the error "' . $e->getMessage() . '" and code ' . $e->getCode());
		    return FALSE;
		} catch(APIException $e) {
		    // Problem with the API
		    error_log('There was an unknown problem with the API, returned code ' . $e->getCode());
		    return FALSE;
		} catch (Exception $e) {
			// General Exception
			error_log('General Exception ' . $e->getMessage() . " code " . $e->getCode());
			return FALSE;
		}

		return TRUE;

	}
}