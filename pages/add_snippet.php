<?php
//
/**
 * 
 * Страница добавления сниппета
 * 
 */

# Защита от мудаков
if (!defined( 'AFTCC__MAIN_FILE' )){
	header('HTTP/1.0 403 Forbidden');
	exit(__('Access Denied.', 'acs'));
}

include_once(AFTCC__PLUGIN_DIR . "proc/add_snippet.proc.php");

?>



<div class="wrap">
	<h2><?php _e('Add New Snippet','acs'); ?></h2>
	<!-- В этом блоке разместим результаты теста через js обработчик -->
	<div id="test_res" style="display:<?php echo ($snipp->p_msg == "") ? "none" : "block"; ?>">
		<?php if($snipp->p_msg != ""): ?>
		<div class="aft_info">
			<?php echo $snipp->p_msg; ?>
		</div>
		<?php endif; ?>
		<span class="close-button">X</span>

	</div>
	
	<?php if($snipp->is_new){ ?>
		<script type="text/javascript">
			alert("<?php _e('Successful!','acs') ?>");
			window.location = "<?= admin_url("admin.php?page=aft_snippets/new_snippet&action=edit&snippet_id=".$snipp->id) ?>";
			
		</script>
	<?php } ?>
	
	<form method="post">
		<?php if( $snipp->id != null) { ?>
		<input type="hidden" value="<?php echo $snipp->id; ?>" name="snippet_id" />
		<?php } ?>
		<table id="acs-table-main" class="form-table">
			<tr style="display: none;"><!-- for future -->
				<th></th>
				<td></td>
			</tr>
			<tr>
				<td colspan="2">
					<label for="title"><b><?php _e('Snippet Name','acs'); ?></b></label>
					<br>
					<input size="55" type="text" id="title" name="title" placeholder="title" value="<?php echo $snipp->title; ?>"  <?php echo isset($snipp->id) ? "readonly" : "" ; ?>>
					
					<h4><?php _e('Snippet Code','acs'); ?></h4>
					<p>
						<a href="#" id="acs-insert-media-button" class="button-primary" data-editor="content" title="Add Media">
							<span class="dashicons dashicons-welcome-write-blog" style="margin-top:2px;"></span>
							<?php _e("Pick media","acs"); ?>
						</a>
						<br>
						<label for="thtml"><b>Media HTML</b></label><br>
						<input id="thtml" size="55" name="img_url" placeholder="url" type="text">
						<br>
						<label for="turl"><b>Media URL</b></label><br>
						<input id="turl" size="55" name="img_url" placeholder="url" type="text">
						<br>
						<br>
						<label for="test_args"><b><?php _e("Test args:","acs") ?></b></label><br>
						<span><?php _e("You can pass custom arguments to shortcode and test them here","acs"); ?></span>
						<br>
						<input id="test_args" value="<?php echo htmlspecialchars($snipp->test_args); ?>" size="55" name="test_args" placeholder="content=test,num=12,str=hello" type="text">
						<div id="arg-info"></div>
					</p>
					<br />
					<small><em>ctrl+shift+d</em> - <?php _e("duplicate line","acs"); ?>
					<br>
					<label>
						<input type="checkbox" name="use-wordwrap" value="use" id="use-wordwrap" <?php 
							$opt = get_option("acs-use-wordwrap"); 
							if($opt == "yes") echo "checked"; 
						?>> <?php _e("Wrap Words",'acs'); ?>
					</label>
					<br>
					<br>
					</small>
					<div class="editor-section">
					<textarea name="snippet_code" rows="15" class="cse-editor"><?php 
						if($snipp->code == ""){
							echo "<?php\r\n\techo 'hello world!';\r\n?>";
						}else{
							echo stripslashes($snipp->code); 
						}
					?></textarea>
					</div>
					<br>
					<?php if(is_paypal_locale()){ ?>
						<p>Like this plugin? Help me to improve it!</p>
						<p><a class="acs-donate-btn" target="_blank" href="https://www.paypal.me/rkudashev"><img src="<?= AFTCC__PLUGIN_URL ?>/img/donate.en.png"></a></p>
					<?php } else { ?>
						<p>Поддержать автора вы можете перечислив пару рублей на webmoney: Z395586459766, R343924802694</p>
					<?php } ?>
				</td>
			</tr>
			<tr>
				<td colspan="2">
					<h4><?php _e("Auto execution settings:","acs"); ?></h4>
					<div class="aft_info">
						<p><?php _e("You can run snippet code automatically.","acs"); ?></p>
						<p><?php _e("<b>Be careful with these settings! It may crash your wordpress! Test code before save!</b>", "acs"); ?></p>
					</div>
					<label for="hook"><?php _e("Auto run hook :","acs"); ?></label>
					<select id="hook" name="mode">
						<option value="on" <?php echo ($snipp->mode == "on")? "selected" : "";?>><?php _e("Use as Shortcode","acs"); ?></option>
						<option value="footer" <?php echo ($snipp->mode == "footer")? "selected" : "";?>><?php _e("Run before footer","acs"); ?></option>
						<option value="foot" <?php echo ($snipp->mode == "foot")? "selected" : "";?>><?php _e("Run after footer","acs"); ?></option>
						<option value="header" <?php echo ($snipp->mode == "header")? "selected" : "";?>><?php _e("Run in header","acs"); ?></option>
						<option value="loaded" <?php echo ($snipp->mode == "loaded")? "selected" : "";?>><?php _e("Run in wp loaded","acs"); ?></option>
						<option value="init" <?php echo ($snipp->mode == "init")? "selected" : "";?>><?php _e("Run on Init","acs"); ?></option>
						<option value="head" <?php echo ($snipp->mode == "head")? "selected" : "";?>><?php _e("Run in head","acs"); ?></option>
						<option value="off" <?php echo ($snipp->mode == "off")? "selected" : "";?>><?php _e("Disabled","acs"); ?></option>
					</select>
				</td>
			</tr>

			<tr>
				<td colspan="2">
					<div class="aft_info">
						<strong><?php _e('Small info:','acs'); ?></strong>
						<p><?php _e('Snippet Code field allows you to write js, html or css code.','acs'); ?></p>
						<ul>
							<li class="aft_data_info">
								<b><?php _e('Plase CSS like that:','acs'); ?></b><br>
								<code>&lt;style&gt; #main{ display:block; } &lt;/style&gt;</code>
							</li>
							<li class="aft_data_info">
								<b><?php _e('Plase PHP like that:','acs'); ?></b><br>
								<code>&lt;?php echo "123"; ?&gt;</code>
								<br>
								<br>
								<?php _e("You can pass content inside your snipet and work with it, by using variable <code>\$content</code>", 'acs'); ?>
								<br><?php _e('EXAMPLE: insert this into post content [rsnippet name="somename"]1+2=[/rsnippet]', 'acs'); ?>
								<br><?php _e('And this insert into snippet code', 'acs'); ?>
								<br><br><p><code>
									&lt;?php
										$content .= "3";
										echo $content;
									?&gt;
								</code></p>
							</li>
							<li class="aft_data_info">
								<b><?php _e('Plase JS like that:','acs'); ?></b>
								<p><code>&lt;script type="text/javascript"&gt; alert("Hello World!"); &lt;/script&gt;</code></p>
							</li>
							<li class="aft_data_info">
								<b><?php _e('For JQuery:', 'acs'); ?></b><br>
<pre><code>&lt;script type="text/javascript"&gt; jQuery(function($) {
		$(document).ready(function(){
			// write your code here
		});
	});
&lt;/script&gt;</code></pre>
							</li>
						</ul>
					</div>
				</td>
			</tr>
		</table>
		<p class="submit" style="position: relative;">
			<?php
			/**
			 * Режим тестирования
			 */ 
			$ajax_nonce = wp_create_nonce( "FasdaEEr1123SAB><asdW" );
			?>
			<script type="text/javascript">
				var nonce_data = "<?php echo $ajax_nonce; ?>";
			</script>
			<a id="test_code" class="button-primary" href="#"><?php _e('Test','acs');  ?></a>

			<?php
			/**
			 * Режим сохранения
			 */
			?>

			<input type="submit" class="button-primary" name="submit" value="<?php 
				if($snipp->id == null) _e('Save Snippet','acs'); 
				else _e('Update Snippet','acs');  
			?>">
		</p>
		

	</form>
</div>

<?php // end of file // ?>