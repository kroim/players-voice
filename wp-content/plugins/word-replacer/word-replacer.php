<?php
/*
Plugin Name: Word Replacer
Plugin URI: http://takien.com/587/word-replacer-wordpress-plugin.php
Description: Replace word in post, page, title or comment and bbPress.
Author: Takien
Version: 0.4
Author URI: http://takien.com/
*/

/*  Copyright 2013 takien.com

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    For a copy of the GNU General Public License, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

if(!defined('ABSPATH')) die();

if (!class_exists('WordReplacer')) {
	class WordReplacer {
	
	var $name       = 'Word Replacer';
	var $version    = '0.4';
	var $table_name = 'word_replacer';
	var $base_name  = 'wordreplacer';
	var $word_replacer_hook = '';
	
	function WordReplacer(){
		$this->__construct();
	}
	
	function __construct(){
		register_activation_hook(__FILE__,array(&$this,'word_replacer_install'));
		$plugin = plugin_basename(__FILE__);
		add_filter("plugin_action_links_$plugin",  array(&$this,'word_replacer_settings_link' ));
		add_filter('comment_text',      array(&$this,'word_replacer_comment'), 200, 2);
		add_filter('the_content',       array(&$this,'word_replacer_postpage'),200);
		add_filter('bbp_get_reply_content',   array(&$this,'word_replacer_bbpress'),200);
		add_filter('the_title',         array(&$this,'word_replacer_title'), 200);
		add_filter('wp_title',          array(&$this,'word_replacer_title'),200);
		add_action('admin_head',        array(&$this,'word_replacer_script'));
		add_action('admin_menu',        array(&$this,'word_replacer_add_page'));
		add_filter('contextual_help',   array(&$this,'word_replacer_help') ,200, 3);
	}
	
	var $fields = Array(
		'original'     => 'Original',
		'replacement'  => 'Replacement',
		'in_posts'     => 'Posts',
		'in_comments'  => 'Comments',
		'in_pages'     => 'Pages',
		'in_titles'    => 'Titles',
		'in_bbpress'   => 'bbPress',
		'in_sensitive' => 'Insensitive',
		'in_wordonly'  => 'Whole Word',
		'in_regex'     => 'Regex'
	);
	
	
	static function word_replacer_install () {
	global $wpdb;
	$wordreplacer = new WordReplacer;
	$tablename    = $wpdb->prefix . $wordreplacer->table_name;
	
		$sql = "CREATE TABLE " . $tablename . " (
		  id mediumint(9) NOT NULL AUTO_INCREMENT,
		  original TEXT NOT NULL,
		  replacement TEXT NOT NULL,
		  in_posts VARCHAR(3) NOT NULL,
		  in_comments VARCHAR(3) NOT NULL,
		  in_pages VARCHAR(3) NOT NULL,
		  in_titles VARCHAR(3) NOT NULL,
		  in_sensitive VARCHAR(3) NOT NULL,
		  in_wordonly VARCHAR(3) NOT NULL,
		  in_regex VARCHAR(3) NOT NULL,
		  in_bbpress VARCHAR(3) DEFAULT '0' NOT NULL,
		  UNIQUE KEY id (id)
		);";
		
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		  
		if($wpdb->get_var('show tables like "'.$tablename.'"') !== $tablename) {
			dbDelta($sql);
			add_option("word_replacer_ver", $wordreplacer->version);
		}
		elseif (get_option("word_replacer_ver") !== $wordreplacer->version) {
			dbDelta($sql);
			update_option("word_replacer_ver", $wordreplacer->version);
		}
		delete_transient('word_replacer_db');
	} /* end word_replacer_install*/
	
	function word_replacer_script(){
	$page = isset($_GET['page']) ? $_GET['page'] : false;
	if($page == $this->base_name) { ?>
	<script type="text/javascript">
	//<![CDATA[
		jQuery(document).ready(function($){
		$('#add_more_field').click(function(){
			var count = $("input[name='count']").length;
			$('#word-replacer-list').append("<tr><td><input type='checkbox' name='delete["+count+"]' value='1' /></td><?php
				foreach($this->fields as $field=>$name) {
					if( 'original' == $field ) {
						echo "<td><input type='hidden' name='id[\"+count+\"]' value='' /><input type='hidden' name='count' value='' /><input style='width:100%' name='".$field."[\"+count+\"]' value='' type='text' /></td><td> &raquo; </td>";
					}
					else if ( 'replacement' == $field ) {
						echo "<td><textarea style='resize:vertical;width:100%' name='".$field."[\"+count+\"]'></textarea><label class='strip_backslash'><input type='checkbox' value='1' name='strip_backslash[\"+count+\"]'/> Strip backslash?</label></td>";
					}
					else {
						echo "<td class='replacer_expandable'><input value='yes' name='".$field."[\"+count+\"]' type='checkbox' /></td>";
					}
				}
			?><td></td>\n</tr>\n");
			cektkp_growtextarea($('#word-replacer-list textarea'));
			return false;
		});
		$('#show_hide_help').click(function(){
			$('#contextual-help-link').trigger('click');
			return false;
		});
		$('.replacer_expandall a').click(function(){
		$('.replacer_expandable').toggle();
		return false;
		});
		
		
		function cektkp_growtextarea(textarea){
			textarea.each(function(index){
				textarea = $(this);
				textarea.css({'overflow':'hidden','word-wrap':'break-word'});
				var pos = textarea.position();
				var growerid = 'textarea_grower_'+index;
				textarea.after('<div style="position:absolute;z-index:-1000;visibility:hidden;top:'+pos.top+';height:'+textarea.outerHeight()+'" id="'+growerid+'"></div>');
				var growerdiv = $('#'+growerid);
				growerdiv.css({'min-height':'20px','font-size':textarea.css('font-size'),'width':textarea.width(),'word-wrap':'break-word'});
				growerdiv.html($('<div/>').text(textarea.val()).html().replace(/\n/g, "<br />."));
				if(textarea.val() == ''){
					growerdiv.text('.');
				}
		
				textarea.height(growerdiv.height()+10);
				
				textarea.keyup(function(){
					growerdiv.html($('<div/>').text($(this).val()).html().replace(/\n/g, "<br />."));
					if($(this).val() == ''){
						growerdiv.text('.');
					}
					$(this).height(growerdiv.height()+10);
				});
			});
		}
		cektkp_growtextarea($('#word-replacer-list textarea'));
		
		/* show hide strip backslash*/
		
		$('.toggle_strip_backslash').click(function(e) {
			$('.strip_backslash').toggle();
			e.preventDefault();
		});
		});
	//]]>
		</script>
		<style type="text/css">
			.strip_backslash {
				display:none
			}
		</style>
	<?php
	}
	} 
	
	function word_replacer_post(){
		global $wpdb;
		$message = false;
		if(isset($_POST['submit-word-replacer'])) {
		if(!wp_verify_nonce($_POST['word_replacer_nonce'],'word_replacer_nonce_action') ){
		wp_die('Not allowed');
		}
			$id           = isset($_POST['id'])           ? $_POST['id']                             : Array();
			$original     = isset($_POST['original'])     ? $_POST['original']                       : Array();
			$replacement  = isset($_POST['replacement'])  ? stripslashes_deep($_POST['replacement']) : Array();
			$in_posts     = isset($_POST['in_posts'])     ? $_POST['in_posts']                       : Array();
			$in_comments  = isset($_POST['in_comments'])  ? $_POST['in_comments']                    : Array();
			$in_pages     = isset($_POST['in_pages'])     ? $_POST['in_pages']                       : Array();
			$in_titles    = isset($_POST['in_titles'])    ? $_POST['in_titles']                      : Array();
			$in_bbpress   = isset($_POST['in_bbpress'])   ? $_POST['in_bbpress']                     : Array();
			$in_sensitive = isset($_POST['in_sensitive']) ? $_POST['in_sensitive']                   : Array();
			$in_wordonly  = isset($_POST['in_wordonly'])  ? $_POST['in_wordonly']                    : Array();
			$in_regex     = isset($_POST['in_regex'])     ? $_POST['in_regex']                       : Array();
			$delete       = isset($_POST['delete'])       ? $_POST['delete']                         : Array();
			$strip_backslash = isset($_POST['strip_backslash'])  ? $_POST['strip_backslash']    : Array();
			
			if(is_array($original) && !empty($original)) {
			$numfield = array_diff($original,Array(''));
			$numfield = count($numfield);
			
			for ($i = 0; $i <= $numfield; $i++) {
			
			$in_posts[$i] 		= (empty($in_posts[$i])     ? 0 : $in_posts[$i]);
			$in_comments[$i] 	= (empty($in_comments[$i])  ? 0 : $in_comments[$i]);
			$in_pages[$i] 		= (empty($in_pages[$i])     ? 0 : $in_pages[$i]);
			$in_titles[$i] 		= (empty($in_titles[$i])    ? 0 : $in_titles[$i]);
			$in_bbpress[$i] 	= (empty($in_bbpress[$i])   ? 0 : $in_bbpress[$i]);
			$in_sensitive[$i] 	= (empty($in_sensitive[$i]) ? 0 : $in_sensitive[$i]);
			$in_wordonly[$i] 	= (empty($in_wordonly[$i])  ? 0 : $in_wordonly[$i]);
			$in_regex[$i] 		= (empty($in_regex[$i])     ? 0 : $in_regex[$i]);
			
			if(!empty($strip_backslash[$i])) {
				$replacement[$i] = str_replace('\\','',$replacement[$i]);
			}
			
			if(!empty($original[$i]) && empty($id[$i])) {
			
			$wpdb->query($wpdb->prepare("
				INSERT INTO ".$wpdb->prefix . $this->table_name." 
				(original, replacement, in_posts, in_comments, in_pages, in_titles, in_sensitive, in_wordonly, in_regex, in_bbpress)
				VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s)",
				array(
				esc_sql(base64_encode(trim($original[$i]))),
				esc_sql(trim($replacement[$i])),
				esc_sql($in_posts[$i]),
				esc_sql($in_comments[$i]),
				esc_sql($in_pages[$i]),
				esc_sql($in_titles[$i]),
				esc_sql($in_sensitive[$i]),
				esc_sql($in_wordonly[$i]),
				esc_sql($in_regex[$i]),
				esc_sql($in_bbpress[$i])
				))); 
				
				$message =  '<div id="message" class="updated fade"><p><strong>Word Inserted.</strong></p></div>';

			}
			elseif((empty($original[$i]) && !empty($id[$i])) OR (!empty($delete[$i]) && !empty($id[$i]))) {
				$wpdb->query("
				DELETE FROM ".$wpdb->prefix . $this->table_name." WHERE id = '".$id[$i]."'");
				$message =  '<div id="message" class="updated fade"><p><strong>Word Deleted.</strong></p></div>';
			}
			elseif(!empty($original[$i]) && !empty($id[$i])) {
				$wpdb->update($wpdb->prefix . $this->table_name, 
				array('original' 	=> esc_sql(base64_encode(trim($original[$i]))),
				'replacement' 		=> esc_sql(trim($replacement[$i])),
				'in_posts'			=> esc_sql($in_posts[$i]),
				'in_comments'		=> esc_sql($in_comments[$i]),
				'in_pages'			=> esc_sql($in_pages[$i]),
				'in_titles'			=> esc_sql($in_titles[$i]),
				'in_sensitive'		=> esc_sql($in_sensitive[$i]),
				'in_wordonly'		=> esc_sql($in_wordonly[$i]),
				'in_regex'			=> esc_sql($in_regex[$i]),
				'in_bbpress'		=> esc_sql($in_bbpress[$i]),
				),
				array('id' => $id[$i]), array('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s'), array('%d'));
				$message = '<div id="message" class="updated fade"><p><strong>Word Updated.</strong></p></div>';
			}
			}
			}
			else{
				$message = '<div id="message" class="updated fade"><p><strong>Please add a field first.</strong></p></div>';
			}
			delete_transient('word_replacer_db'); /*update transient each time update data*/
	}
	echo $message;
	} /*end word_replacer_post*/
	
	private function esc_textarea($string) {
		//return htmlspecialchars(stripcslashes($string));
		$string = stripcslashes($string);
		return htmlspecialchars($string,ENT_QUOTES);
	}


	private function word_replacer_db() {
		global $wpdb;
		

		$word_replacer_db = get_transient( 'word_replacer_db' );
		if( empty($word_replacer_db)) {
			$word_replacer_db = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix . $this->table_name." ORDER BY id", ARRAY_A);
			set_transient( 'word_replacer_db', $word_replacer_db );
		}
		return $word_replacer_db;
	}
	
	
	private function replace_replace ($content, $type = '') {
		$original = $replacement = Array();
		$b = $i = '';
		$n = 1;
		
		foreach($this->word_replacer_db() as $wrdb) { $n++;
			$b = ($wrdb['in_wordonly']  == 'yes') ? '\b' : '';
			$i = ($wrdb['in_sensitive'] == 'yes') ? 'i'  : '';
			
			$replace = false;
			
			switch ($type) :
				case 'comment' : 
					if ( 'yes' == $wrdb['in_comments'] ) {
						$replace = 1;
					}
				break;
				
				case 'title' :
					if( 'yes' == $wrdb['in_titles'] ) {
						$replace = 1;
					}
				break;
				
				case 'bbpress' :
					if( 'yes' == $wrdb['in_bbpress'] ) {
						$replace = 1;
					}
				break;
				
				default: 
					if ( is_page() AND ( 'yes' == $wrdb['in_pages'] ) ){
						$replace = 1;
					}
					if ( !is_page() AND ('yes' == $wrdb['in_posts']) ) {
						$replace = 1;
					}
				
			endswitch;
			
			if( $replace ) {
				$ori = $this->base64($wrdb['original']);
				$ori = ( 'yes' !== $wrdb['in_regex'] ) ? preg_quote( $ori )  : $ori ;
				$original[$n]    = "/$b".$ori."$b/$i";
				$replacement[$n] = htmlspecialchars_decode($this->esc_textarea($wrdb['replacement']));
			}
		}
		
		$content = preg_replace($original,$replacement,$content); 
		return $content;
	}
	
	function base64($string) {
		return base64_decode($string,true) ? base64_decode($string) : $string;
	}
	
	function word_replacer_postpage($content) {
		return $this->replace_replace( $content );
	} 
	
	function word_replacer_bbpress( $content ) {
		return $this->replace_replace( $content, 'bbpress' );
	}
	
	function word_replacer_comment($content,$comment='') {
		if($comment) {
			if($comment->comment_approved == '1') {
				$content = $this->replace_replace( $content, 'comment' );
			}
		}
		return $content;
	} 
	
	function word_replacer_title($content) {
		return $this->replace_replace( $content, 'title' );
	} 

	function word_replacer_settings_link($links) {
		  $settings_link = '<a href="options-general.php?page='.$this->base_name.'">Settings</a>';
		  array_unshift($links, $settings_link);
		  return $links;
	}  
	
	function word_replacer_help( $help, $screen_id, $screen ) {
			if ( $screen_id == $this->word_replacer_hook ) {
				$help = '
						<h2>'.$this->name.' '.$this->version.'</h2>
						<h5>Instruction:</h5>
						<ol>
						<li>To <strong>Add New Word</strong> click Add More Fields  add your word, replacement and filter, then hit Add/Update Words.</li>
						<li>To <strong>Update</strong> a word, just replace/retype in it\'s place an hit Add/Update Words.</li>
						<li>To <strong>Delete</strong> a word, leave blank/clear word in "original" field and hit Add/Update Words. Or check on the Delete column</li></ol>
						<h5>Regex:</h5>
						<ol>
						<li>Do not use delimiter "/" in the begining and the end of each REGEX pattern. It will be added when it\'s processed.</li>
						<li>The REGEX options <em>i</em> also will be added automatically if you check Case Insensitive on</li>
						</ol>
						<h5>Example:</h5>
						<ol>
							<li>BASIC: To replace word "<strong>foo</strong>" in a post with "bar", put "foo" in the original field, and "bar" in the replacement field, tick on the <em>Post</em> column and Save.</li>
							<li>BASIC REGEX: To replace words "ipsum, dolor, amet" become bold "<strong>ipsum, dolor, amet</strong>", put "(lorem|dolor|amet)" in the original field, and "&lt;strong&gt;$1&lt;/strong&gt;" in the replacement field, tick on the <em>Post</em>, <em>Insensitive</em>, and <em>Regex</em> column and Save/Update. This will replace sentence "Lorem ipsum dolor sit amet" become "<strong>Lorem</strong> ipsum <strong>dolor</strong> sit <strong>amet</strong>" in your posts.</li>
						</ol>
						<h5>Note:</h5>
						<ol>
							<li>Be wise to put word in the original field, if you made a mistake may cause unexpected output. If so, just delete it and your post will back to normal.</li>
							<li>Replacement fields accept HTML tag, make sure you do not replace <em>title</em> with HTML tag.</li>
						</ol>
						
						<p>Further question, suggestion, comment, or help please <a href="http://takien.com/587/word-replacer-wordpress-plugin.php" target="_blank">go here.</a></p>
						<p>Support is limited to plugins usage and feature only, for advanced info about RegEx and preg_replace please
						refers to the following resources:</p>
						<ol>
						<li>Regex info <a href="http://www.regular-expressions.info/" target="_blank">http://www.regular-expressions.info/</a></li>
						<li>preg_replace function <a href="http://www.php.net/manual/en/function.preg-replace.php" target="_blank">http://www.php.net/manual/en/function.preg-replace.php</a></li>
						</ol>
						';
					
			}
			
		return $help;
	} 
	
	function word_replacer_add_page() {
		$plugin_hook = add_options_page($this->name, $this->name, 'activate_plugins', $this->base_name, array(&$this,'word_replacer_page'));
		$this->word_replacer_hook = $plugin_hook;
	} 

	function word_replacer_page() {
	echo '<div class="wrap">
	<h2>'.$this->name.'</h2>';
	
	$this->word_replacer_post();


		$basefield = '<tr>
		<td><input type="checkbox" name="delete[]" value="1" /></td>';
		foreach($this->fields as $field=>$name) {
			if( 'original' == $field ) {
				$basefield .= '<td><input type="hidden" name="id[]" value="" /><input type="hidden" name="id[]" value="" />
				<input style="width:100%" name="'.$field.'[]" type="text" /></td><td> &raquo; </td>';
			}
			else if ( 'replacement' == $field ) {
				$basefield .= '<td><textarea style="resize:none;width:100%" name="'.$field.'[]"></textarea><label class="strip_backslash"><input type="checkbox" value="1" name="strip_backslash[]"/> Strip backslash?</label></td>';
			}
			else {
				$basefield .= '<td class="replacer_expandable"><input value="yes" name="'.$field.'[]" type="checkbox" /></td>';
			}
		}
		$basefield .= '<td></td></tr>';
		
		$action_url = admin_url('options-general.php?page=' . $this->base_name);
		
		?>

		<p>Put the word to be replaced on the left, and what to change it to on the right. <a id="show_hide_help" href="#">Help?</a></p>
		<form method="post" action="<?php echo $action_url;?>">
		<?php wp_nonce_field('word_replacer_nonce_action','word_replacer_nonce'); ?>
		<table class="widefat fixed" width="650" align="center" width="100%" id="word-replacer-list">
		<thead>
		<tr>
		<th width="40">Delete</th>
		<th>Original</th><th width="5">&nbsp;</th><th>Replacement</th>
		<th class="replacer_expandable" width="40">Posts</th>
		<th class="replacer_expandable" width="70">Comments</th>
		<th class="replacer_expandable" width="40">Pages</th>
		<th class="replacer_expandable" width="40">Titles</th>
		<th class="replacer_expandable" width="60">bbPress</th>
		<th class="replacer_expandable" width="80">Insensitive</th>
		<th class="replacer_expandable" width="80">Whole Word</th>
		<th class="replacer_expandable" width="40">Regex</th>
		<th class="replacer_expandall" width="20"><a style="color:black" href="#" title="Expand/Collapse">&laquo;&raquo;</a></th>
		</tr>
		</thead>
		<?php 
		$i = -1;
		$word_replacer_db = $this->word_replacer_db();
		if(is_array($word_replacer_db) AND !empty($word_replacer_db)) {
		foreach($word_replacer_db as $wrdb) { $i++ ?>
		<?php $alternate = (empty($alternate) ? 'class="alternate"' : '');?>
		<tr <?php echo $alternate;?>>
		<td><input type="checkbox" name="delete[<?php echo $i;?>]" value="1" /></td>
		<td>
		<input type="hidden" name="id[<?php echo $i;?>]" value="<?php echo $wrdb['id']; ?>" />
		<input type="hidden" name="count" value="" />
		
		<?php
			foreach($this->fields as $field=>$name) {
				if( 'original' == $field ) {
					?>
					<input style="width:100%" type="text" name="original[<?php echo $i;?>]" id="original_<?php echo $i;?>" value="<?php echo htmlspecialchars($this->base64($wrdb['original'])); ?>" /></td><td> &raquo; </td>
					<?php
				}
				else if ( 'replacement' == $field ) {
					?>
					<td>
						<textarea style="resize:vertical;width:100%" name="replacement[<?php echo $i;?>]"><?php echo $this->esc_textarea($wrdb['replacement']); ?></textarea><label class="strip_backslash"><input type="checkbox" value="1" name="strip_backslash[<?php echo $i;?>]"/> Strip backslash?</label>
					</td>
					<?php
				}
				else {
					?>
					<td class="replacer_expandable">
					<input value="yes" name="<?php echo $field.'['.$i.']';?>" <?php echo (($wrdb[$field] == 'yes') ? 'checked="checked"' : ''); ?> type="checkbox" />
					
					</td>
					<?php
				}
			}
		?>
		<td></td>
		</tr>

		<?php }
		}
		else {
			echo $basefield;
		}
		?>
		</table>
		<p>
			<a href="#" class="toggle_strip_backslash">Toggle Strip Backslash Option</a>
		</p>
		<input type="button" id="add_more_field" value="+ Add More Fields" style="cursor:pointer" />
		<input type="hidden" name="action" value="update" /> 
		<input name="submit-word-replacer" class="button-primary" type="submit" value="<?php _e('Add/Update Words') ?>" />
		</form>
		</div>
		<?php
		}
			
} /* end class */
}
if (class_exists('WordReplacer')) {
	new WordReplacer();
}