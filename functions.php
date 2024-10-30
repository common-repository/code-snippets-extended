<?php
//
/**
 * User custom functions file
 */
if (!defined( 'ABSPATH' )){
	header('HTTP/1.0 403 Forbidden');
	exit(__('Access Denied.', 'acs'));
}

// Check eval for availability
if(!function_exists("is_eval_enabled")){
	function is_eval_enabled(){
		$tmp = 2;
		@eval(" \$tmp=3; "); // eval should change $tmp to 3, if not then show error

		if(intval($tmp) == 3) 
			return true;
		else 
			return false;
	}
}

// check locale

if(!function_exists("is_paypal_locale")){
	function is_paypal_locale(){ // russian users not use paypal

		$locale = get_locale();
		if(stripos($locale, "ru") !== false) 
			return false;
		else 
			return true;
	}
}