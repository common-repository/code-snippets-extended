<?php
//
/**
 * 
 * Основной файл плагина. Отвечает за пункты меню и создание таблиц.
 * 
 */

# Защита от мудаков
if (!defined( 'ABSPATH' )){
	exit(__('Access Denied.', 'acs'));
}

class AftCCMain{
	# Конструктор
	function __construct(){
		add_action('media_buttons', array($this, 'get_media_button'), 999);		// Хук, добавляющий в редактор новую кнопку. 999 - порядковый номер элемента. Это значит что он будет добавлен после всех остальных кнопок
		add_action('wp_enqueue_media', array($this, 'get_media_scripts'));		// Скрипты, необходимые для media кнопок
		add_action('admin_menu', array($this, 'get_plugin_menu'));				// Тут создаем пункт меню для нашего плагина
		add_action('admin_init', array($this, 'hook_admin_init'));				// Хук инициализации админ. интерфейса
		add_action('init', array($this,'load_textdomain'));			// Загрузка языка

		// проверяем досупность eval
		if(!is_eval_enabled()){	
			add_action( 'admin_notices', array($this,'is_eval_enabled_notice') );
		}

		register_activation_hook  ( AFTCC__MAIN_FILE , array(&$this, 'hook_activate'));		// Хук активации плагина. Тут создаем дб таблицы для плагина.
		register_deactivation_hook( AFTCC__MAIN_FILE , array(&$this, 'hook_deactivate'));	// При деактивации плагина - удалям таблицы из базы
	}

	# notice
	function is_eval_enabled_notice() {
	    ?>
	    <div class="notice notice-error is-dismissible">
	        <h4><?php _e( 'Code Snippets extended plugin error', 'acs' ); ?></h4>
	        <p><?php _e( 'We re sorry, but in your system configurations our plugin will not work!', 'acs' ); ?></p>
	        <p><em><?php _e( 'Please enable `eval` function in your server!', 'acs' ); ?></em></p>
	        <p><?php _e( 'We underestand that it is not safe for work, but witout `eval` our plugin cannot run your snippets.', 'acs' ); ?></p>
	    </div>
	    <?php
	}

	# Загрузка перевода
	function load_textdomain(){
		load_plugin_textdomain( 'acs', false,  AFTCC__PLUGIN_DIR. '/languages/' ); 
	}

	# Хук активации
	function hook_activate(){
		$this->create_plugin_tables();
	}

	# Создание таблиц плагина в базе
	function create_plugin_tables(){
		global $wpdb;	// Интерфейс, а проще говоря класс для работы с базой данных
		$charset_collate = '';	// Автоматическое определение кодировки в бд пользователя.
		if ( ! empty( $wpdb->charset ) ) {
			$charset_collate = "DEFAULT CHARACTER SET {$wpdb->charset}";
		}
		if ( ! empty( $wpdb->collate ) ) {
			$charset_collate .= " COLLATE {$wpdb->collate}";
		}
		
		$table_name = $wpdb->base_prefix . 'aft_cc';

		// Запрос. 
		// mode - режим - on/off/init/enqueue_scripts etc...
		// code - код сниппета
		$sql = "CREATE TABLE IF NOT EXISTS `{$table_name}`(
			`id` int(11) NOT NULL AUTO_INCREMENT,
			`title` varchar(70) DEFAULT NULL,
			`mode` varchar(15) DEFAULT NULL,
			`code` LONGTEXT DEFAULT NULL,
			PRIMARY KEY(`id`),
			UNIQUE KEY(`title`)
		) ".$charset_collate.";";

		$wpdb->query($sql);
	}

	# Хук деактивации
	function hook_deactivate(){
		//$this->delete_plugin_tables();
	}
	
	# Удаляем таблицы плагина
	function delete_plugin_tables(){
		global $wpdb;
		$table_name = $wpdb->base_prefix . "aft_cc";
		$sql = "DROP TABLE IF EXISTS $table_name;";
		$wpdb->query($sql);
	}

	# Добавляем js и css файлы
	function get_media_scripts() {
		 wp_enqueue_script('media_main', AFTCC__PLUGIN_URL.'js/media_main.js', array('jquery'));
	}

	# Кнопка, показывающая всплывающее окно
	function get_media_button(){
		global $post_ID, $temp_ID;
		$iframe_post_id = (int) (0 == $post_ID ? $temp_ID : $post_ID);
		$ajax_nonce = wp_create_nonce( "FasdaEEr1123SAB><asdW" ); // Похуй пляшем

		$url = admin_url( "/admin-ajax.php?post_id=" . $iframe_post_id . "&action=aftcb_show_form&nonce=" . $ajax_nonce . "&TB_iframe=true&width=768" );	// Ajax обработчик мжно создавать просто формирую GET запрос к файлу admin-ajax.php
		
		echo '<a id="show_code_snippets" class="button" title="'.__('Select Snippet','acs').'" data-editor="content" href="'.$url.'"><span class="wp-media-aft-snippets"></span>'.__('Insert Snippet', 'acs').'</a>';
	}

	# Подключение встроенных скриптов
	function hook_admin_init() {
    	wp_enqueue_script('cse-admin', AFTCC__PLUGIN_URL . 'js/admin.js', array( 'jquery' ),"210217");
    	wp_enqueue_script('magnific-popup', AFTCC__PLUGIN_URL . 'js/jquery.magnific-popup.min.js', array( 'jquery' ));
    	wp_enqueue_script('jq_base64', AFTCC__PLUGIN_URL . 'js/jquery_base64.js', array( 'jquery' ));
    	wp_enqueue_style( 'magnific-popup-styles', AFTCC__PLUGIN_URL."css/magnific-popup.css" );
    	wp_enqueue_style( 'admin-styles', AFTCC__PLUGIN_URL."css/admin.css" );
		if (isset($_GET['page']) && strpos($_GET['page'], 'aft_snippets/') === 0) {
			// media
			wp_enqueue_style ('thickbox');
			wp_enqueue_script('thickbox');
			wp_enqueue_script('media-upload');
			wp_enqueue_media();
			// иконки
			wp_enqueue_style( 'dashicons' );
			// редактор
			wp_enqueue_script('ace-code-editor', AFTCC__PLUGIN_URL . 'js/ace_code_editor/ace.js', array( 'jquery' ));
			wp_enqueue_script('ace-code-mode-php', AFTCC__PLUGIN_URL . 'js/ace_code_editor/mode-php.js', array( 'jquery' ));
		}
	}

	# Формируем меню
	function get_plugin_menu(){
		$page_title = __("Snippets",'acs');
		$menu_title = __("Snippets",'acs');
		$capability = "manage_options";
		$menu_slug 	= "aft_snippets/index";
		$icon_url 	= AFTCC__PLUGIN_URL . "/img/admin-icon.png";
		$position 	= null;
		add_menu_page( $page_title, $menu_title, $capability, $menu_slug, array($this,'get_dashboard_page'), $icon_url, $position );	//Главный пункт меню админки				
		//Подпункты
		add_submenu_page($menu_slug, $page_title, __('Snippets','acs') , $capability,  $menu_slug , array($this,'get_dashboard_page'));
		
		add_submenu_page($menu_slug, __('Add Snippet','acs') , __('Add Snippet','acs') , $capability,  'aft_snippets/new_snippet' , array($this,'add_new_snippet'));
	}

	# Пункт меню - Главная страница
	function get_dashboard_page(){
		include_once(AFTCC__PLUGIN_DIR.'pages/index.php');
	}

	# Пункут меню - Добавить сниппет
	function add_new_snippet(){
		include_once(AFTCC__PLUGIN_DIR.'pages/add_snippet.php');
	}
}

new AftCCMain();
// end of file //