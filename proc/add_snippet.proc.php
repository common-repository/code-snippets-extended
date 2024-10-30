<?php
//
/**
 * 
 * Обработчик добавления сниппета
 * 
 */
if (!defined( 'AFTCC__MAIN_FILE' )){
	header('HTTP/1.0 403 Forbidden');
	exit(__('Access Denied.', 'acs'));
}


class AddSnippetProc{

	public $title 		= ""; 		//Значения полей по умолчанию
	public $code 		= "";
	public $p_msg 		= "";
	public $is_edit 	= 0;		//1 - режим редактирования
	public $id 			= null;
	public $mode		= "on";
	public $test_args 	= "";
	public $is_new		= false;

	function __construct(){
		// При созранении
		if(isset($_POST['submit'])){
			$this->add_snippet_proc();
		}

		// Режим редактирования
		if( (isset($_GET['action']) && $_GET['action'] == "edit") || isset($_REQUEST['snippet_id'])){
			global $wpdb;
			$this->id = intval($_REQUEST['snippet_id']);
			$table_name = $wpdb->base_prefix.'aft_cc';
			$query = $wpdb->prepare("SELECT * FROM {$table_name}
											WHERE `id` = '%d'", 
												array(
													$this->id,
												)
											);
			$data = $wpdb->get_results($query, ARRAY_A);
			if($data){
				$this->title = $data[0]['title'];
				$code = $data[0]['code'];
				$code_parts = explode(AFTCC__SEPARATOR, $code);

				$this->test_args = isset($code_parts[1]) ? $code_parts[1] : "";
				$this->code = base64_decode($code_parts[0]);
				$this->mode = $data[0]['mode'];
			}else{
				$this->p_msg = __("Error, plugin can't edit this","acs" ); 
			}
		}

	}

	# Добавляем сниппет в базу
	function add_snippet_proc(){
		// save сode in database. I encode your snippets using base64_encode to protcet your database from wrong code
		$this->code = isset($_POST['snippet_code']) ? trim($_POST['snippet_code']) : "";
		$this->title = isset($_POST['title']) ? htmlspecialchars(urldecode(trim($_POST['title']))) : "";
		$this->mode = isset($_POST['mode']) ? htmlspecialchars(trim($_POST['mode'])) : "";
		$this->test_args = isset($_POST['test_args']) ? htmlspecialchars_decode(trim($_POST['test_args'])) : "";
		if(isset($_POST["use-wordwrap"]) && $_POST["use-wordwrap"] == "use"){
			update_option("acs-use-wordwrap","yes");
		}else{
			update_option("acs-use-wordwrap","no");
		}

		// prepare arg names
		if($this->test_args != ""){
			$args = explode(",", $this->test_args);
			$atts = array();
			if(!empty($args)){
				foreach ($args as $key => $arg) {
					$arg = explode("=", trim($arg));
					if(isset($arg[1]) && strlen($arg[0]) > 0){
						$aname = preg_replace("/[^a-zA-Z0-9]/i", "_", $arg[0]);
						$adata = $arg[1];
						$atts[trim($aname)] = trim($adata);
					}
				}

				if(!empty($atts)){
					$this->test_args = "";
					foreach ($atts as $j => $att) {
						$this->test_args = $this->test_args.trim($j)."=".trim($att).",";
					}
				}
				$this->test_args = trim($this->test_args,",");
			}
		}

		if(empty($this->title)){ 
			$this->p_msg = __('Error: Please set snippet name before save.', 'acs' );
			return;
		}

		if(empty($this->code)){ 
			$this->p_msg = __('Error: Snippet code not exist', 'acs' );
			return;
		}

		global $wpdb;
		$table_name = $wpdb->base_prefix . "aft_cc";

		$ret = false;
		
		$cnt = 0;
		$i = 0;
		if(!isset($_REQUEST['snippet_id'])){
			do{
				$ex = explode("_", $this->title);
				$on = 0;
				if(isset($ex[1]))
					$on = intval($ex[1]);
				if($i != 0)
					$this->title = $ex[0]."_".($on+$i);

				$query = $wpdb->prepare("SELECT COUNT(*) FROM {$table_name}
										WHERE `title` = '%s' AND `mode`='on'", 
										array($this->title)
										);
				$i++;
				$cnt = $wpdb->get_var($query);
			}while($cnt !=0 );
		}
		if (get_magic_quotes_gpc() == FALSE){
			$this->code = stripslashes($this->code);
		}
		if(!isset($_REQUEST['snippet_id'])){ // Сохранение нового сниппета
			
			
			$ret = $wpdb->insert( 
				$table_name, 
				array( 
					'id'			=> NULL, 
					'title'			=> $this->title,
					'mode'			=> $this->mode,
					'code'			=> base64_encode($this->code).AFTCC__SEPARATOR.$this->test_args,
					)
				);
			$this->id = $wpdb->insert_id;
			$this->is_new = true;
		}else{ // Редактирование уже существующего сниппета

			$this->id = intval($_REQUEST['snippet_id']);
			$ret = $wpdb->update( 
				$table_name, 
				array( 
					'title'			=> $this->title,
					'code'			=> base64_encode($this->code).AFTCC__SEPARATOR.$this->test_args,
					'mode'			=> $this->mode,
					),
				array('id'=>$this->id)
				);
		}

		if($ret != false || $this->id != null) $this->p_msg = __('Successful!','acs');
		else $this->p_msg = __('Unfortunately we cannot save this snippet. Some problems with database.','acs');

	}

}

$snipp = new AddSnippetProc();
// end of file //