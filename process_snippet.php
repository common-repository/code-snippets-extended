<?php
//
/**
 * 
 * Файл обработчика сниппета
 * 
 */

# Защита от мудаков
if (!defined( 'ABSPATH' )){
	header('HTTP/1.0 403 Forbidden');
	exit(__('Access Denied.', 'acs'));
}

# Регистрируем свой обработчик шорткодов в системе.
# Наш шорткод выглядит так: [rsnippet id="1"]
add_shortcode( 'code_snippet', 'aft_proc_shortcode');
add_shortcode( 'rsnippet', 'aft_proc_shortcode');

# Автозапуск сниппетов
add_action("init","process_acs_init", 1);
add_action('wp_head','process_acs_head', 1);
add_action('get_header','process_acs_header', 1);
add_action('wp_loaded','process_acs_loaded', 1);
add_action('get_footer','process_acs_footer', 1);
add_action('wp_footer','process_acs_foot', 1);

if(!function_exists("aft_proc_shortcode")){
	function aft_proc_shortcode($atts, $content){
		/*$atts = shortcode_atts( array(
				"id"		 => false,
				"name"		 => false
			), $atts );*/
		$content = do_shortcode($content);
		
		/*foreach ($atts as $key => $att) {
			$att[$key] = do_shortcode($att);
		}*/

		$id = ( $atts['id'] ) ? intval(trim($atts['id'])) : "none";
		$name = ( $atts['name'] ) ? trim($atts['name']) : "none";

		global $wpdb;
		$table_name = $wpdb->base_prefix.'aft_cc';
		
		if($id != "none"){
			$query = $wpdb->prepare("SELECT * FROM {$table_name}
										WHERE `id` = '%d' AND `mode`='on'", 
										array($id,)
										);
		}else
		if($name != "none"){
			$query = $wpdb->prepare("SELECT * FROM {$table_name}
										WHERE `title` = '%s' AND `mode`='on'", 
										array(htmlspecialchars($name),)
										);
		}
		$arr = $wpdb->get_results($query, ARRAY_A);
		if($arr == false) return "";
		$code_data = explode(AFTCC__SEPARATOR,$arr[0]['code']);
		/*if(isset($code_data[1]) && @trim($code_data[1]) != ""){
			$args = explode(",", trim($code_data[1]));
			if(!empty($args)){
				foreach ($args as $key => $arg) {
					$arg = explode("=", trim($arg));
					if(isset($arg[1]) && strlen($arg[0]) > 0){
						if($arg[0] == "content") 
							$content = trim($adata);
						else
							$atts[trim($arg[0])] = trim($adata);
					}
				}
			}
		}*/
		$code = base64_decode(trim($code_data[0]));
		
		if(function_exists("iconv")) $code = iconv("UTF-8","UTF-8//IGNORE",$code);
		//if(function_exists("mb_convert_encoding")) $code = mb_convert_encoding($code,"UTF-8//IGNORE", "UTF-8");
		ob_start();
		@eval("?> ".$code. " <?php ");
		$res = trim(ob_get_contents());
		ob_clean();
		ob_end_flush();
		
		return $res;
	}
}

if(!function_exists("dp_acs_snippet")){
	function dp_acs_snippet($mode){
		global $wpdb;
		$table_name = $wpdb->base_prefix.'aft_cc';

		$query = "SELECT * FROM {$table_name} WHERE `mode`='{$mode}'";
		$arr = $wpdb->get_results($query, ARRAY_A);
		if( $arr == false || !isset($arr[0]) ) return;

		// Запускаем все сниппеты по очереди
		foreach ($arr as $key => $snippet) {
			$code_data = explode(AFTCC__SEPARATOR,$snippet['code']);
			$code = base64_decode(trim($code_data[0]));
			if(function_exists("iconv")) $code = iconv("UTF-8","UTF-8//IGNORE",$code);
			@eval("?> ".trim($code)." <?php ");
		}
	}
}

if(!function_exists("process_acs_footer")){
	function process_acs_footer(){

		dp_acs_snippet("footer");
		
	}
}

if(!function_exists("process_acs_foot")){
	function process_acs_foot(){

		dp_acs_snippet("foot");
		
	}
}

if(!function_exists("process_acs_head")){
	function process_acs_head(){

		dp_acs_snippet("head");
		
	}
}

if(!function_exists("process_acs_header")){
	function process_acs_header(){

		dp_acs_snippet("header");
		
	}
}

if(!function_exists("process_acs_loaded")){
	function process_acs_loaded(){

		dp_acs_snippet("loaded");
		
	}
}

if(!function_exists("process_acs_init")){
	# run on init
	function process_acs_init(){

		dp_acs_snippet("init");
		
	}
}

// end of file //