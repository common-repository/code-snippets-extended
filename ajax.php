<?php
//
/**
 * 
 * Ajax обработчики модуля
 * 
 */

# Защита от мудаков
if (!defined( 'ABSPATH' )){
	header('HTTP/1.0 403 Forbidden');
	exit(__('Access Denied.', 'acs'));
}

class AftCCAjax{
	# Конструктор
	function __construct() {
		add_action( 'wp_ajax_aftcb_show_form', array($this, 'aftcb_show_form') );
		add_action( 'wp_ajax_test_code', array($this, 'test_code') );

	}

	function aftcb_show_form(){
		if(!is_admin()) die();
		
		// Всплывающую форму формируем тут.
		global $wpdb;
		$table_name = $wpdb->base_prefix.'aft_cc';
		
		$arr = $wpdb->get_results("SELECT * FROM ".$table_name." WHERE mode='on' ORDER BY `id` DESC;", ARRAY_A);
		if(count($arr) == 0){ 
			die( "<div class='white_popup'>" . __('Snippets not creted Yet.','acs') . "<p></div>" );
		}
		// Тут формирует html нашего iframe
		$res  = "<div class='white_popup'>";
		$res .= "<table class='widefat' cellspacing='0' border='0'>";
		$res .= "<tbody><tr><th>Snippet</th><th>Name</th><th>Insert snippet</th></tr>";
		foreach($arr as $snippet){
			$code_data = explode(AFTCC__SEPARATOR,$snippet['code']);
			$args = explode(",", trim($code_data[1]));
			$att_str = "";
			$content = "";
			if(!empty($args)){
				foreach ($args as $key => $arg) {
					$arg = explode("=", trim($arg));
					if(isset($arg[1]) && strlen($arg[0]) > 0){
						$adata = $arg[1];
						if($arg[0] == "content") 
							$content = trim($adata);
						else
							$att_str .= trim($arg[0])."=\"".trim($adata)."\" ";
					}
				}
			}
			$scode = "[rsnippet ".$att_str."id=\"".$snippet['id']."\" name=\"".htmlspecialchars_decode($snippet['title'])."\"]";
			if($content!= ""){
				$scode .= __("your content","acs")."[/rsnippet]";
			}

			$res .= "<tr>";
			$res .= "<td class='st_id' data_str='".json_encode(array("code"=>$scode))."'><small><code>[rsnippet id=\"".$snippet['id']."\"]</code></small></td>";
			$res .= "<td class='st_title'>".$snippet['title']."</td>";
			$res .= "<td class='st_actions'><a id='select_snippet' class='button-primary' href='#''>".__('Select','acs'). "</a></td>";
			$res .= "</tr>";
		}

		$res .= "</tbody></table>";
		$res .= "</div>";

		die($res);
	}

	function test_code(){
		check_ajax_referer( 'FasdaEEr1123SAB><asdW', 'nonce', true );
		$code =stripslashes(htmlspecialchars_decode($_POST['code']));
		$args =htmlspecialchars_decode($_POST['args']);
		$args = explode(",", $args);
		$atts = array();
		$content = "";
		if(!empty($args)){
			foreach ($args as $key => $arg) {
				$arg = explode("=", trim($arg));
				if(isset($arg[1]) && strlen($arg[0]) > 0){
					$aname = preg_replace("/[^a-zA-Z0-9]/i", "_", $arg[0]);

					$adata = str_replace('"', '\"', $arg[1]);
					if($aname == "content") 
						$content = do_shortcode(trim($adata));
					else
						$atts[trim($aname)] = do_shortcode(trim($adata));
				}
			}
		}
		ob_start(); // Ловим вывод eval'а в основной буфер вывода
		/**
		 * 
		 * Если вдруг вас интересует - как работают вложенные друг в друга ob_start, то вот тема на стековерфлов -  
		 * http://stackoverflow.com/questions/10441410/what-happened-when-i-use-multi-ob-start-without-ob-end-clean-or-ob-end-flush
		 * 
		 */

		@eval("?> ".trim($code). " <?");
		$res = ob_get_contents();
		ob_clean();
		ob_end_flush();

		die($res);
	}
}

new AftCCAjax();
// end of file //