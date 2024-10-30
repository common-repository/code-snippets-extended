<?php
//
/**
 * 
 * Главная страница модуля
 * 
 */

# Защита от мудаков
if (!defined( 'ABSPATH' )){
	header('HTTP/1.0 403 Forbidden');
	exit(__('Access Denied.', 'acs'));
}

require_once( AFTCC__PLUGIN_DIR . "proc/index.proc.php");
$items_table = new CCListTable();

if(isset($_POST['snippets']) && $_POST['action']){
	global $wpdb;
	$table_name = $wpdb->base_prefix.'aft_cc';
	if($_POST['action'] == "delete"){
		foreach($_POST['snippets'] as $id){
			$wpdb->delete( $table_name, array('id'=>$id));
		}
	}
}

# Действия - включить, выключить и удалить
if(isset($_GET['action'])){
	global $wpdb;
	$id = intval($_GET['snippet_id']);
	$table_name = $wpdb->base_prefix.'aft_cc';
	if($_GET['action'] == "on")
		$wpdb->update( $table_name, array('mode'=>'on'), array('id'=>$id));	// $wpdb->update( $table, $data, $where, $format = null, $where_format = null );
	if($_GET['action'] == "off")
		$wpdb->update( $table_name, array('mode'=>'off'), array('id'=>$id));
	if($_GET['action'] == "delete")
		$wpdb->delete( $table_name, array('id'=>$id)); 	// $wpdb->delete( $table, $where, $where_format = null ); 
}

# Инициализация таблицы
$items_table->prepare_items();
?>


<div class="wrap">
	<h2><?php _e('Code snippets.','acs'); ?>
		<a href="?page=aft_snippets/new_snippet" class="add-new-h2"><?php _e('Add New Snippet','acs'); ?></a>
	</h2>
	<div class="aft_info">
		<p><img style="width:60px;" src='<?php echo AFTCC__PLUGIN_URL . "img/icon.png";?>' id="main_i"></img></p>
		<p>
			<?php _e('Code Snippets Extended id powerful plugin, that allows you to create code snippets & embed it into posts or pages easily.','acs'); ?>
		</p>
		<?php if(is_paypal_locale()){ ?>
			<p><a class="acs-donate-btn" target="_blank" href="https://www.paypal.me/rkudashev"><img src="<?= AFTCC__PLUGIN_URL ?>/img/donate.en.png"></a></p>
		<?php } else { ?>
			<p>Webmoney: Z395586459766, R343924802694</p>
		<?php } ?>
	</div>
	<form method="post">
		<input type="hidden" name="page" value="clt_page" /> <!-- Этот параметр нужен для $_REQUEST['page'] --> 
		<?php
			$items_table->search_box(__('Search by name','acs'), 'search_by_title');
			$items_table->display(); 
		?>
	 </form>
</div>