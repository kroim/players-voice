<?php

function relevanssi_form_api_key($api_key) {
	if (!empty($api_key)) :
?>
	<tr>
		<th scope="row">
			<?php _e("API key", "relevanssi"); ?>
		</th>
		<td>
			<strong><?php _e('API key is set', 'relevanssi'); ?></strong>.<br />
			<input type='checkbox' id='relevanssi_remove_api_key' name='relevanssi_remove_api_key' /> <label for='relevanssi_remove_api_key'><?php _e('Remove the API key.', 'relevanssi'); ?></label>
			<p class="description"><?php _e('A valid API key is required to use the automatic update feature and the PDF indexing. Otherwise the plugin will work just fine without an API key. Get your API key from Relevanssi.com.', 'relevanssi'); ?></p>
		</td>
	</tr>
<?php
	else :
?>
	<tr>
		<th scope="row">
			<?php _e("API key", "relevanssi"); ?>
		</th>
		<td>
			<label for='relevanssi_api_key'><?php _e('Set the API key:', 'relevanssi'); ?>
			<input type='text' id='relevanssi_api_key' name='relevanssi_api_key' value='' /></label>
			<p class="description"><?php _e('A valid API key is required to use the automatic update feature and the PDF indexing. Otherwise the plugin will work just fine without an API key. Get your API key from Relevanssi.com.', 'relevanssi'); ?></p>
		</td>
	</tr>
<?php
	endif;
}

function relevanssi_form_internal_links($intlinks_noindex, $intlinks_strip, $intlinks_nostrip) {
?>
	<tr>
		<th scope="row">
			<label for='relevanssi_internal_links'><?php _e("Internal links", "relevanssi"); ?></label>
		</th>
		<td>
			<select name='relevanssi_internal_links' id='relevanssi_internal_links'>
				<option value='noindex' <?php echo $intlinks_noindex ?>><?php _e("No special processing for internal links", "relevanssi"); ?></option>
				<option value='strip' <?php echo $intlinks_strip ?>><?php _e("Index internal links for target documents only", "relevanssi"); ?></option>
				<option value='nostrip' <?php echo $intlinks_nostrip ?>><?php _e("Index internal links for both target and source", "relevanssi"); ?></option>
			</select>
			<p class="description"><?php _e("Internal link anchor tags can be indexed for target document, both target and source or source only. See Help for more details.", 'relevanssi'); ?></p>
		</td>
	</tr>
<?php
}

function relevanssi_form_hide_post_controls($hide_post_controls, $show_post_controls) {
	$show_post_controls_class = "class='screen-reader-text'";
	if (!empty($hide_post_controls)) $show_post_controls_class = "";
?>
	<tr>
		<th scope="row">
			<label for='relevanssi_hide_post_controls'><?php _e("Hide Relevanssi", 'relevanssi'); ?></label>
		</th>
		<td>
		<fieldset>
			<legend class="screen-reader-text"><?php _e("Hide Relevanssi on edit pages", "relevanssi"); ?></legend>
			<label for='relevanssi_hilite_title'>
				<input type='checkbox' name='relevanssi_hide_post_controls' id='relevanssi_hide_post_controls' <?php echo $hide_post_controls ?> />
				<?php _e("Hide Relevanssi on edit pages", "relevanssi"); ?>
			</label>
		</fieldset>
		<p class="description"><?php _e("Enabling this option hides Relevanssi on all post edit pages.", "relevanssi"); ?></p>
		</td>
	</tr>
	<tr id="show_post_controls" <?php echo $show_post_controls_class; ?>>
		<th scope="row">
			<label for='relevanssi_show_post_controls'><?php _e("Show Relevanssi for admins", 'relevanssi'); ?></label>
		</th>
		<td>
		<fieldset>
			<legend class="screen-reader-text"><?php _e("Show Relevanssi for admins on edit pages", "relevanssi"); ?></legend>
			<label for='relevanssi_hilite_title'>
				<input type='checkbox' name='relevanssi_show_post_controls' id='relevanssi_show_post_controls' <?php echo $show_post_controls ?> />
				<?php _e("Show Relevanssi on edit pages for admins", "relevanssi"); ?>
			</label>
		</fieldset>
		<p class="description"><?php printf(__("If Relevanssi is hidden on post edit pages, enabling this option will show Relevanssi features for admin-level users. Admin-level users are those with %s capabilities, but if you want to use a different capability, you can use the %s filter to modify that.", "relevanssi"), '<code>manage_options</code>', '<code>relevanssi_options_capability</code>'); ?></p>
		</td>
	</tr>
<?php
}

function relevanssi_form_link_weight($link_boost) {
	global $relevanssi_variables;
?>
	<tr>
		<td>
			<?php _e('Internal links', 'relevanssi'); ?>
		</td>
		<td class="col-2">
			<input type='text' id='relevanssi_link_boost' name='relevanssi_link_boost' size='4' value='<?php echo $link_boost ?>' />
		</td>
	</tr>
<?php
}

function relevanssi_form_post_type_weights($post_type_weights) {
	$post_types = get_post_types();
	foreach ($post_types as $type) {
		if ('nav_menu_item' === $type) continue;
		if ('revision' === $type) continue;
		if (isset($post_type_weights[$type])) {
			$value = $post_type_weights[$type];
		}
		else {
			$value = 1;
		}
		$label = sprintf(__("Post type '%s':", 'relevanssi'), $type);

		echo <<<EOH
	<tr>
		<td>
			$label
		</td>
		<td class="col-2">
			<input type='text' id='relevanssi_weight_$type' name='relevanssi_weight_$type' size='4' value='$value' />
		</td>
	</tr>
EOH;
	}
}

function relevanssi_form_taxonomy_weights($post_type_weights) {
	$taxonomies = get_taxonomies('', 'names');
	foreach ($taxonomies as $type) {
		if ('nav_menu' === $type) continue;
		if ('post_format' === $type) continue;
		if ('link_category' === $type) continue;
		if (isset($post_type_weights[$type])) {
			$value = $post_type_weights[$type];
		}
		else {
			$value = 1;
		}
		$label = sprintf(__("Taxonomy '%s':", 'relevanssi'), $type);

		echo <<<EOH
	<tr>
		<td>
			$label
		</td>
		<td class="col-2">
			<input type='text' id='relevanssi_weight_$type' name='relevanssi_weight_$type' size='4' value='$value' />
		</td>
	</tr>
EOH;
	}
}

function relevanssi_form_recency_weight($recency_bonus) {
	?>
		<tr>
			<td>
				<label for='relevanssi_recency_bonus'><?php _e("Recent posts bonus weight:", "relevanssi"); ?></label>
			</td>
			<td class="col-2">
				<input type='text' id='relevanssi_recency_bonus' name='relevanssi_recency_bonus' size='4' value="<?php echo $recency_bonus ?>" />
			</td>
		</tr>
	<?php
}
	
function relevanssi_form_recency_cutoff($recency_bonus_days) {
?>
	<tr>
		<th scope="row">
			<label for='relevanssi_recency_days'><?php _e("Recent posts bonus cutoff", "relevanssi"); ?></label>
		</th>
		<td>
			<input type='text' id='relevanssi_recency_days' name='relevanssi_recency_days' size='4' value="<?php echo $recency_bonus_days ?>" /> <?php _e("days", "relevanssi"); ?>
			<p class="description"><?php _e('Posts newer than the day cutoff specified here will have their weight multiplied with the bonus above.', 'relevanssi'); ?></p>
		</td>
	</tr>
<?php
}

function relevanssi_form_hide_branding($hide_branding) {
?>
	<tr>
		<th scope="row">
			<label for='relevanssi_hide_branding'><?php _e("Hide Relevanssi branding", "relevanssi"); ?></label>
		</th>
		<td>
		<fieldset>
			<legend class="screen-reader-text"><?php printf(__("Don't show Relevanssi branding on the '%s' screen.", "relevanssi"), __('User Searches', 'relevanssi')); ?></legend>
			<label for='relevanssi_hide_branding'>
				<input type='checkbox' name='relevanssi_hide_branding' id='relevanssi_hide_branding' <?php echo $hide_branding ?> />
				<?php printf(__("Don't show Relevanssi branding on the '%s' screen.", "relevanssi"), __('User Searches', 'relevanssi')); ?>
			</label>
		</fieldset>
		</td>
	</tr>
<?php
}

function relevanssi_form_highlight_external($highlight_docs_ext, $excerpts) {
?>
	<tr>
		<th scope="row">
			<label for='relevanssi_highlight_docs_external'><?php _e("Highlight from external searches", 'relevanssi'); ?></label>
		</th>
		<td>
		<fieldset>
			<legend class="screen-reader-text"><?php _e("Highlight query terms in documents from external searches", "relevanssi"); ?></legend>
			<label for='relevanssi_hilite_title'>
				<input type='checkbox' name='relevanssi_highlight_docs_external' id='relevanssi_highlight_docs_external' <?php echo $highlight_docs_ext ?> <?php if (empty($excerpts)) echo "disabled='disabled'"; ?>/>
				<?php _e("Highlight query terms in documents from external searches", "relevanssi"); ?>
			</label>
		</fieldset>
		<p class="description"><?php _e("Highlights hits when user arrives from an external search. Currently supports Bing, Ask, Yahoo and AOL Search. Google hides the keyword information.", "relevanssi"); ?></p>
		</td>
	</tr>
<?php
}

function relevanssi_form_thousep($thousand_separator) {
?>
	<tr>
		<th scope="row">
			<label for='relevanssi_thousand_separator'><?php _e("Thousands separator", "relevanssi"); ?></label>
		</th>
		<td>
			<input type='text' name='relevanssi_thousand_separator' id='relevanssi_thousand_separator' size='3' value='<?php echo $thousand_separator ?>' />
			<p class="description"><?php _e("If Relevanssi sees this character between numbers, it'll stick the numbers together no matter how the character would otherwise be handled. Especially useful if a space is used as a thousands separator.", "relevanssi"); ?></p>
		</td>
	</tr>
<?php
}

function relevanssi_form_disable_shortcodes($disable_shortcodes) {
?>
	<tr>
		<th scope="row">
			<label for='relevanssi_disable_shortcodes'><?php _e("Disable these shortcodes", "relevanssi"); ?></label>
		</th>
		<td>
			<input type='text' name='relevanssi_disable_shortcodes' id='relevanssi_disable_shortcodes' size='60' value='<?php echo $disable_shortcodes ?>' />
			<p class="description"><?php _e("Enter a comma-separated list of shortcodes. These shortcodes will not be expanded if expand shortcodes above is enabled. This is useful if a particular shortcode is causing problems in indexing.", "relevanssi"); ?></p>
		</td>
	</tr>
<?php
}

function relevanssi_form_mysql_columns($mysql_columns) {
	global $wpdb;
	$column_list = $wpdb->get_results("SHOW COLUMNS FROM $wpdb->posts");
	$columns = array();
	foreach ($column_list as $column) {
		array_push($columns, $column->Field);
	}
	$columns = implode(', ', $columns);

?>
	<tr>
		<th scope="row">
			<label for='relevanssi_mysql_columns'><?php _e("MySQL columns", "relevanssi"); ?></label>
		</th>
		<td>
			<input type='text' name='relevanssi_mysql_columns' id='relevanssi_mysql_columns' size='60' value='<?php echo $mysql_columns ?>' />
			<p class="description"><?php printf(__("A comma-separated list of %s MySQL table columns to include in the index. Following columns are available: ", "relevanssi"), '<code>wp_posts</code>');
			echo $columns; ?>.</p>
		</td>
	</tr>
<?php
}

function relevanssi_form_searchblogs_setting($searchblogs_all, $searchblogs) {
	if (is_multisite()) : 
?>
	<tr>
		<th scope="row">
			<label for='relevanssi_searchblogs_all'><?php _e("Search all subsites", "relevanssi"); ?></label>
		</th>
		<td>
			<fieldset>
				<legend class="screen-reader-text"><?php _e("Search all subsites.", "relevanssi"); ?></legend>
				<label for='relevanssi_searchblogs_all'>
					<input type='checkbox' name='relevanssi_searchblogs_all' id='relevanssi_searchblogs_all' <?php echo $searchblogs_all ?> />
					<?php _e("Search all subsites", "relevanssi"); ?>
				</label>
				<p class="description"><?php _e("If this option is checked, multisite searches will include all subsites. Warning: if you have dozens of sites in your network, the searches may become too slow. This can be overridden from the search form.", "relevanssi"); ?></p>
			</fieldset>
		</td>
	</tr>
	<tr>
		<th scope="row">
			<label for='relevanssi_searchblogs'><?php _e("Search some subsites", "relevanssi"); ?></label>
		</th>
		<td>
			<input type='text' name='relevanssi_searchblogs' id='relevanssi_searchblogs' size='60' value='<?php echo $searchblogs ?>' <?php if (!empty($searchblogs_all)) echo "disabled='disabled'"; ?> />
			<p class="description"><?php _e("Add a comma-separated list of blog ID values to have all search forms on this site search these multisite subsites. This can be overridden from the search form.", "relevanssi");?></p>
		</td>
	</tr>
<?php
	endif;
}

function relevanssi_form_index_users($index_users, $index_subscribers, $index_user_fields) {
	$fields_display = 'class="screen-reader-text"';
	if (!empty($index_users)) $fields_display = "";
?>

	<h2><?php _e("Indexing user profiles", "relevanssi"); ?></h2>

	<table class="form-table">
	<tr>
		<th scope="row">
			<label for='relevanssi_index_users'><?php _e('Index user profiles', 'relevanssi'); ?></label>
		</th>
		<td>
		<fieldset>
			<legend class="screen-reader-text"><?php _e("Index user profiles.", "relevanssi"); ?></legend>
			<label for='relevanssi_index_users'>
				<input type='checkbox' name='relevanssi_index_users' id='relevanssi_index_users' <?php echo $index_users ?> />
				<?php _e("Index user profiles.", "relevanssi"); ?>
			</label>
			<p class="description"><?php _e("Relevanssi will index user profiles. This includes first name, last name, display name and user description.", "relevanssi"); ?></p>
			<p class="description important screen-reader-text" id="user_profile_notice"><?php _e("This may require changes to search results template, see the contextual help.", "relevanssi"); ?></p>
		</fieldset>
		</td>
	</tr>
	<tr id="index_subscribers" <?php echo $fields_display; ?>>
		<th scope="row">
			<label for='relevanssi_index_subscribers'><?php _e('Index subscribers', 'relevanssi'); ?></label>
		</th>
		<td>
		<fieldset>
			<legend class="screen-reader-text"><?php _e("Index also subscriber profiles.", "relevanssi"); ?></legend>
			<label for='relevanssi_index_subscribers'>
				<input type='checkbox' name='relevanssi_index_subscribers' id='relevanssi_index_subscribers' <?php echo $index_subscribers ?> />
				<?php _e("Index also subscriber profiles.", "relevanssi"); ?>
			</label>
			<p class="description"><?php _e("By default, Relevanssi indexes authors, editors, contributors and admins, but not subscribers. You can change that with this option.", "relevanssi"); ?></p>
		</fieldset>
		</td>
	</tr>

	<tr id="user_extra_fields" <?php echo $fields_display; ?>>
		<th scope="row">
			<label for='relevanssi_index_user_fields'><?php _e("Extra fields", "relevanssi"); ?></label>
		</th>
		<td>
			<input type='text' name='relevanssi_index_user_fields' id='relevanssi_index_user_fields' size='60' value='<?php echo $index_user_fields ?>' /></label><br />
			<p class="description"><?php _e("A comma-separated list of extra user fields to include in the index. These can be user fields or user meta.", "relevanssi"); ?></p>
		</td>
	</tr>
	</table>
<?php
}

function relevanssi_form_index_synonyms($index_synonyms) {
?>
	<h3><?php _e("Indexing synonyms", "relevanssi"); ?></h3>
	<table class="form-table">
	<tr>
		<th scope="row">
			<label for='relevanssi_index_synonyms'><?php _e("Index synonyms", "relevanssi"); ?></label>
		</th>
		<td>
		<fieldset>
			<legend class="screen-reader-text"><?php _e("Index synonyms for AND searches.", "relevanssi"); ?></legend>
			<label for='relevanssi_index_synonyms'>
				<input type='checkbox' name='relevanssi_index_synonyms' id='relevanssi_index_synonyms' <?php echo $index_synonyms ?> />
				<?php _e("Index synonyms for AND searches.", "relevanssi"); ?>
			</label>
		</fieldset>
		<p class="description"><?php _e("If checked, Relevanssi will use the synonyms in indexing. If you add <code>dog = hound</code> to the synonym list and enable this feature, every time the indexer sees <code>hound</code> in post content or post title, it will index it as <code>hound dog</code>. Thus, the post will be found when searching with either word. This makes it possible to use synonyms with AND searches, but will slow down indexing, especially with large databases and large lists of synonyms. This only works for post titles and post content. You can use multi-word keys and values, but phrases do not work.", 'relevanssi'); ?></p>
		</td>
	</tr>
	</table>

	<br /><br />
<?php
}

function relevanssi_form_index_pdf_parent($index_pdf_parent, $index_post_types) {
?>
	<h2><?php _e("Indexing PDF content", "relevanssi"); ?></h2>

	<table class="form-table">
	<tr>
	<th scope="row">
		<label for='relevanssi_index_pdf_parent'><?php _e("Index for parent", "relevanssi"); ?></label>
	</th>
	<td>
	<fieldset>
		<legend class="screen-reader-text"><?php _e("Index PDF contents for parent post", "relevanssi"); ?></legend>
		<label for='relevanssi_index_pdf_parent'>
			<input type='checkbox' name='relevanssi_index_pdf_parent' id='relevanssi_index_pdf_parent' <?php echo $index_pdf_parent; ?> />
			<?php _e("Index PDF contents for parent post", "relevanssi"); ?>
		</label>
		<p class="description"><?php printf(__("If checked, Relevanssi indexes the PDF content both for the attachment post and the parent post. You can control the attachment post visibility by indexing or not indexing the post type %s.", "relevanssi"), "<code>attachment</code>"); ?></p>
		<?php if (!in_array('attachment', $index_post_types) && empty($index_pdf_parent)) : ?>
		<p class="description important"><?php printf(__("You have not chosen to index the post type %s. You won't see any PDF content in the search results, unless you check this option.", "relevanssi"), "<code>attachment</code>"); ?></p>
		<?php endif; ?>
		<?php if (in_array('attachment', $index_post_types) && !empty($index_pdf_parent)) : ?>
		<p class="description important"><?php printf(__("Searching for PDF contents will now return both the attachment itself and the parent post. Are you sure you want both in the results?", "relevanssi"), "<code>attachment</code>"); ?></p>
		<?php endif; ?>
	</fieldset>
	</td>
	</tr>
	</table>
<?php
}

function relevanssi_form_index_taxonomies($index_taxonomies, $index_terms) {
	$fields_display = 'class="screen-reader-text"';
	if (!empty($index_taxonomies)) $fields_display = "";

?>
	<h2><?php _e("Indexing taxonomy terms", "relevanssi"); ?></h2>

	<table class="form-table">
	<tr>
		<th scope="row">
			<label for='relevanssi_index_taxonomies'><?php _e('Index taxonomy terms', 'relevanssi'); ?></label>
		</th>
		<td>
		<fieldset>
			<legend class="screen-reader-text"><?php _e("Index taxonomy terms.", "relevanssi"); ?></legend>
			<label for='relevanssi_index_taxonomies'>
				<input type='checkbox' name='relevanssi_index_taxonomies' id='relevanssi_index_taxonomies' <?php echo $index_taxonomies ?> />
				<?php _e("Index taxonomy terms.", "relevanssi"); ?>
			</label>
			<p class="description"><?php _e("Relevanssi will index taxonomy terms (categories, tags and custom taxonomies). Searching for taxonomy term name will return the taxonomy term page.", "relevanssi"); ?></p>
		</fieldset>
		</td>
	</tr>
	<tr id="taxonomies" <?php echo $fields_display; ?>>
		<th scope="row">
			<?php _e("Taxonomies", "relevanssi"); ?>
		</th>
		<td>
			<table class="widefat" id="index_terms_table">
			<thead>
				<tr>
					<th><?php _e('Taxonomy', 'relevanssi'); ?></th>
					<th><?php _e('Index', 'relevanssi'); ?></th>
					<th><?php _e('Public?', 'relevanssi'); ?></th>
				</tr>
			</thead>
	<?php
		$taxos = get_taxonomies('', 'objects');
		foreach ($taxos as $taxonomy) {
			if ($taxonomy->name === 'nav_menu') continue;
			if ($taxonomy->name === 'link_category') continue;
			if (in_array($taxonomy->name, $index_terms)) {
				$checked = 'checked="checked"';
			}
			else {
				$checked = '';
			}
			$label = sprintf(__("%s", 'relevanssi'), $taxonomy->name);
			$taxonomy->public ? $public = __('yes', 'relevanssi') : $public = __('no', 'relevanssi');
			$type = $taxonomy->name;

			echo <<<EOH
	<tr>
		<td>
			$label
		</td>
		<td>
			<input type='checkbox' name='relevanssi_index_terms_$type' id='relevanssi_index_terms_$type' $checked />
		</td>
		<td>
			$public
		</td>
	</tr>
EOH;
		}
	?>
			</table>


		</td>

	</tr>
	</table>
<?php
}

function relevanssi_form_importexport($serialized_options) {
?>
	<h2 id="options"><?php _e("Import or export options", "relevanssi"); ?></h2>

	<p><?php _e("Here you find the current Relevanssi Premium options in a text format. Copy the contents of the text field to make a backup of your settings. You can also paste new settings here to change all settings at the same time. This is useful if you have default settings you want to use on every system.", "relevanssi"); ?></p>

	<table class="form-table">
	<tr>
		<th scope="row"><?php _e("Current Settings", "relevanssi"); ?></th>
		<td>
			<p><textarea name='relevanssi_settings' rows='4' cols='80'><?php echo $serialized_options; ?></textarea></p>

			<input type='submit' name='import_options' id='import_options' value='<?php _e("Import settings", 'relevanssi'); ?>' class='button' />
		</td>
	</tr>
	</table>

	<p><?php _e("Note! Make sure you've got correct settings from a right version of Relevanssi. Settings from a different version of Relevanssi may or may not work and may or may not mess your settings.", "relevanssi"); ?></p>
<?php
}

function relevanssi_form_attachments($index_post_types, $index_pdf_parent) {
    global $wpdb;
    $read_new_files = ('on' === get_option('relevanssi_read_new_files') ? 'checked="checked"' : '');
    $send_pdf_files = ('on' === get_option('relevanssi_send_pdf_files') ? 'checked="checked"' : '');
	$link_pdf_files = ('on' === get_option('relevanssi_link_pdf_files') ? 'checked="checked"' : '');
	$indexing_attachments = false;
	if (in_array('attachment', $index_post_types)) $indexing_attachments = true;

?>
    <table class="form-table">
	<tr>
		<th scope="row">
			<input type='button' id='index' value='<?php _e('Read all unread PDFs', 'relevanssi'); ?>' class='button-primary' /><br /><br />
		</th>
		<td>
			<p class="description" id="indexing_button_instructions">
				<?php printf(__("Clicking the button will read the contents of all the unread PDF files and store the contents to the %s custom field for future indexing. PDF files with errors will be skipped, except for the files with timeout and connection related errors: those will be attempted again.", "relevanssi"), "<code>_relevanssi_pdf_content</code>"); ?>
			</p>
			<div id='relevanssi-progress' class='rpi-progress'><div></div></div>
			<div id='relevanssi-timer'><?php _e("Time elapsed", "relevanssi"); ?>: <span id="relevanssi_elapsed">0:00:00</span> | <?php _e("Time remaining", "relevanssi"); ?>: <span id="relevanssi_estimated"><?php _e("some time", "relevanssi"); ?></span></div>
			<textarea id='relevanssi_results' rows='10' cols='80'></textarea>
		</td>
	</tr>
	<tr>
		<th scope="row"><?php _e("State of the PDFs", "relevanssi"); ?></td>
		<?php 
			$pdf_count = $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->postmeta WHERE meta_key = '_relevanssi_pdf_content' AND meta_value != ''");
			$pdf_error_count = $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->postmeta WHERE meta_key = '_relevanssi_pdf_error' AND meta_value != ''");
		?>
		<td id="stateofthepdfindex">
			<p><?php echo $pdf_count ?> <?php echo _n("document has read PDF content.", "documents have read PDF content.", $pdf_count, "relevanssi"); ?></p>
            <p><?php echo $pdf_error_count ?> <?php echo _n("document has a PDF reading error.", "documents have PDF reading errors.", $pdf_error_count, "relevanssi"); ?>
            <?php if ($pdf_error_count > 0) : ?><span id="relevanssi_show_pdf_errors"><?php _e('Show PDF errors', 'relevanssi'); ?></span>.<?php endif; ?></p>
			<textarea id="relevanssi_pdf_errors" rows="4" cols="120"></textarea>
		</td>
	</tr>
	<tr>
		<th scope="row"><?php _e("Reset PDF content", "relevanssi"); ?></td>
		<td>
            <input type="button" id="reset" value="<?php _e('Reset all PDF data from posts', 'relevanssi'); ?>" class="button-primary" />
            <p class="description"><?php printf(__("This will remove all %s and %s custom fields from all posts. If you want to reread all PDF files, use this to clean up; clicking the reading button doesn't wipe the slate clean like it does in regular indexing.", "relevanssi"), "<code>_relevanssi_pdf_content</code>", "<code>_relevanssi_pdf_error</code>");?></p>
		</td>
	</tr>
	<tr>
		<th scope="row">
			<label for='relevanssi_read_new_files'><?php _e("Read new files", "relevanssi"); ?></label>
		</th>
		<td>
		<fieldset>
			<legend class="screen-reader-text"><?php _e("Read new files automatically", "relevanssi"); ?></legend>
			<label for='relevanssi_read_new_files'>
				<input type='checkbox' name='relevanssi_read_new_files' id='relevanssi_read_new_files' <?php echo $read_new_files; ?> />
				<?php _e("Read new files automatically", "relevanssi"); ?>
			</label>
			<p class="description"><?php _e("If this option is enabled, Relevanssi will automatically read the contents of new attachments as they are uploaded. This may cause unexpected delays in uploading posts. If this is not enabled, new attachments are not read automatically and need to be manually read and reindexed.", "relevanssi"); ?></p>
		</fieldset>
		</td>
	</tr>
	<tr>
		<th scope="row">
			<label for='relevanssi_send_pdf_files'><?php _e("Upload PDF files", "relevanssi"); ?></label>
		</th>
		<td>
		<fieldset>
			<legend class="screen-reader-text"><?php _e("Upload PDF files for reading", "relevanssi"); ?></legend>
			<label for='relevanssi_send_pdf_files'>
				<input type='checkbox' name='relevanssi_send_pdf_files' id='relevanssi_send_pdf_files' <?php echo $send_pdf_files; ?> />
				<?php _e("Upload PDF files for reading", "relevanssi"); ?>
			</label>
			<p class="description"><?php _e("By default, Relevanssi only sends a link to the PDF to the PDF reader. If your PDFs are not accessible (for example your site is inside an intranet, password protected, or a local dev site, and the PDF files can't be downloaded if given the URL of the file), check this option to upload the whole file to the reader.", "relevanssi"); ?></p>
		</fieldset>
		</td>
	</tr>
	<tr>
		<th scope="row">
			<label for='relevanssi_link_pdf_files'><?php _e("Link to PDFs", "relevanssi"); ?></label>
		</th>
		<td>
		<fieldset>
			<legend class="screen-reader-text"><?php _e("Link search results directly to PDF files", "relevanssi"); ?></legend>
			<label for='relevanssi_link_pdf_files'>
				<input type='checkbox' name='relevanssi_link_pdf_files' id='relevanssi_link_pdf_files' <?php echo $link_pdf_files; ?> />
				<?php _e("Link search results directly to PDF files", "relevanssi"); ?>
			</label>
			<p class="description"><?php _e("If this option is checked, attachment results in search results will link directly to the PDF file. Otherwise the results will link to the attachment page.", "relevanssi"); ?></p>
			<?php if (!$indexing_attachments) : ?>
			<p class="important description"><?php printf(__("You're not indexing the %s post type, so this setting doesn't have any effect.", "relevanssi"), '<code>attachment</code>'); ?>
			<?php endif; ?>
			<?php if (!$indexing_attachments && !$index_pdf_parent) : ?>
			<p class="important description"><?php printf(__("You're not indexing the %s post type and haven't connected the PDFs to the post parents in the indexing settings. You won't be seeing any PDFs in the results.", "relevanssi"), '<code>attachment</code>'); ?>
			<?php endif; ?>
		</fieldset>
		</td>
	</tr>
	<tr>
		<th scope="row"><?php _e('Instructions', 'relevanssi'); ?></th>
		<td>
			<p><?php printf(__("When Relevanssi reads attachment content, the text is extracted and saved in the %s custom field for the attachment post. This alone does not add the attachment content in the Relevanssi index; it just makes the contents of the attachments easily available for the regular Relevanssi indexing process.", 'relevanssi'), "<code>_relevanssi_pdf_content</code>"); ?></p>
			<p><?php printf(__("There are two ways to index the attachment content. If you choose to index the %s post type, Relevanssi will show the attachment posts in the results.", 'relevanssi'), "'attachment'"); ?></p>
			<p><?php _e("You can also choose to index the attachment content for the parent post, in which case Relevanssi will show the parent post in the results (this setting can be found on the indexing settings). Obviously this does not find the content in attachments that are not attached to another post – if you just upload a file to the WordPress Media Library, it is not attached and won't be found unless you index the attachment posts.", "relevanssi"); ?></p>
			<p><?php _e("In any case, in order to see attachments in the results, you must read the attachment content here first, then build the index on the Indexing tab.", "relevanssi"); ?></p>
			<p><?php _e("If you need to reread a PDF file, you can do read individual PDF files from Media Library. Choose an attachment and click 'Edit more details' to read the PDF content.", "relevanssi"); ?></p>
		</td>
	</tr>
	<tr>
		<th scope="row"><?php _e('Key not valid?', 'relevanssi'); ?></th>
		<td>
			<p><?php _e("Are you a new Relevanssi customer and seeing 'Key xxxxxx is not valid' error messages? New API keys are delivered to the PDF server once per hour, so if try again an hour later, the key should work.", "relevanssi"); ?></p>
		</td>
	</tr>
	<tr>
		<th scope="row"><?php _e('Important!', 'relevanssi'); ?></th>
		<td>
			<p><?php _e("In order to read the contents of the PDFs, the files are sent over to Relevanssiservices.com, a PDF processing service hosted on a Digital Ocean Droplet in the USA. The service creates a working copy of the files. The copy is removed after the file has been processed, but there are no guarantees that someone with an access to the server couldn't see the files. Do not read files with confidential information in them. In order to block individual files from reading, use the Relevanssi post controls on attachment edit page to exclude attachment posts from indexing.", 'relevanssi'); ?></p>
		</td>
	</tr>
    </table>
<?php
}

function relevanssi_add_metaboxes() {
	global $post;
	if ($post === NULL) return;
	if ($post->post_type === 'acf') return; 		// no metaboxes for Advanced Custom Fields pages
	add_meta_box(
        'relevanssi_hidebox',
        __( 'Relevanssi post controls', 'relevanssi' ),
    	'relevanssi_post_metabox',
     	array($post->post_type, 'edit-category')
   	 );
}

function relevanssi_post_metabox() {
    global $relevanssi_variables;
	wp_nonce_field(plugin_basename($relevanssi_variables['file']), 'relevanssi_hidepost');

	global $post;
	$check = checked('on', get_post_meta($post->ID, '_relevanssi_hide_post', true), false);

	$pins = get_post_meta($post->ID, '_relevanssi_pin', false);
	$pin = implode(', ', $pins);

	$unpins = get_post_meta($post->ID, '_relevanssi_unpin', false);
	$unpin = implode(', ', $unpins);

	// The actual fields for data entry
	echo '<input type="hidden" id="relevanssi_metabox" name="relevanssi_metabox" value="true" />';
	
	echo '<input type="checkbox" id="relevanssi_hide_post" name="relevanssi_hide_post" ' . $check . ' />';
	echo ' <label for="relevanssi_hide_post">';
	_e("Exclude this post or page from the index.", 'relevanssi');
	echo '</label> ';

	echo '<p><strong>' . __('Pin this post', 'relevanssi') . '</strong></p>';
	echo '<p>' . __('A comma-separated list of single word keywords or multi-word phrases. If any of these keywords are present in the search query, this post will be moved on top of the search results.', 'relevanssi') . '</p>';
	echo '<textarea type="text" id="relevanssi_pin" name="relevanssi_pin" cols="80" rows="2">' . $pin . '</textarea/>';

	echo '<p><strong>' . __('Exclude this post', 'relevanssi') . '</strong></p>';
	echo '<p>' . __('A comma-separated list of single word keywords or multi-word phrases. If any of these keywords are present in the search query, this post will be removed from the search results.', 'relevanssi') . '</p>';
	echo '<textarea type="text" id="relevanssi_unpin" name="relevanssi_unpin" cols="80" rows="2">' . $unpin . '</textarea/>';
}

function relevanssi_premium_admin_help() {
	$screen = get_current_screen();
	//$screen->remove_help_tabs();
	$screen->add_help_tab( array(
		'id'       => 'relevanssi-boolean',
		'title'    => __( 'Boolean operators', 'relevanssi' ),
		'content'  => 	"<ul>" . 
			"<li>" . __("Relevanssi Premium offers limited support for Boolean logic. In addition of setting the default operator from Relevanssi settings, you can use AND and NOT operators in searches.", 'relevanssi') . "</li>" .
			"<li>" . __("To use the NOT operator, prefix the search term with a minus sign:", "relevanssi") . 
			sprintf("<pre>%s</pre>", __("cats -dogs", "relevanssi")) .
			__("This would only show posts that have the word 'cats' but not the word 'dogs'.", "relevanssi") . "</li>" .
			"<li>" . __("To use the AND operator, set the default operator to OR and prefix the search term with a plus sign:", "relevanssi") . 
			sprintf("<pre>%s</pre>", __("+cats dogs mice", "relevanssi")) .
			__("This would show posts that have the word 'cats' and either 'dogs' or 'mice' or both, and would prioritize posts that have all three.", "relevanssi") . "</li>" .
			"</ul>",
	));
	$screen->add_help_tab( array(
		'id'       => 'relevanssi-title-user-profiles',
		'title'    => __( 'User profiles', 'relevanssi' ),
		'content'  => 	"<ul>" . 
			"<li>" . sprintf(__("Permalinks to user profiles may not always work on search results templates. %s should work, but if it doesn't, you can replace it with %s.", 'relevanssi'), "<code>the_permalink()</code>", "<code>relevanssi_the_permalink()</code>") . "</li>" .
			"<li>" . sprintf(__("To control which user meta fields are indexed, you can use the %s option. It should have a comma-separated list of user meta fields. It can be set like this (you only need to run this code once):", "relevanssi"), "<code>relevanssi_index_user_fields</code>") . 
			"<pre>update_option('relevanssi_index_user_fields', 'field_a,field_b,field_c');</pre></li>" .
			"<li>" . sprintf(__("For more details on user profiles and search results templates, see <a href='%s'>this knowledge base entry</a>.", 'relevanssi'), "https://www.relevanssi.com/knowledge-base/user-profile-search/") . "</li>" .
			"</ul>",
	));
    $screen->add_help_tab( array(
        'id'       => 'relevanssi-internal-links',
        'title'    => __( 'Internal links', 'relevanssi' ),
        'content'  => 	"<ul>" . 
            "<li>" . __("This option sets how Relevanssi handles internal links that point to your own site.", "relevanssi") . "</li>" .
            "<li>" . __("If you choose 'No special processing', Relevanssi doesn’t care about links and indexes the link anchor (the text of the link) like it is any other text.", "relevanssi") . "</li>" .
            "<li>" . __("If you choose 'Index internal links for target documents only', then the link is indexed like the link anchor text were the part of the link target, not the post where the link is.", "relevanssi") . "</li>" .
            "<li>" . __("If you choose 'Index internal links for target and source', the link anchor text will count for both posts.", "relevanssi") . "</li>" .
            "</ul>",
	));
    $screen->add_help_tab( array(
        'id'       => 'relevanssi-stemming',
        'title'    => __( 'Stemming', 'relevanssi' ),
        'content'  => 	"<ul>" . 
			"<li>" . __("By default Relevanssi doesn't understand anything about singular word forms, plurals or anything else. You can, however, add a stemmer that will stem all the words to their basic form, making all different forms equal in searching.", "relevanssi") . "</li>" .
			"<li>" . __("To enable the English-language stemmer, add this to the theme functions.php:", "relevanssi") . 
			"<pre>add_filter('relevanssi_stemmer', 'relevanssi_simple_english_stemmer');</pre>" . "</li>" .
            "</ul>",
	));
    $screen->add_help_tab( array(
        'id'       => 'relevanssi-wpcli',
        'title'    => __( 'WP CLI', 'relevanssi' ),
        'content'  => 	"<ul>" . 
			"<li>" . sprintf(__("If you have WP CLI installed, Relevanssi Premium has some helpful commands. Use %s to get a list of available commands.", "relevanssi"), '<code>wp help relevanssi</code>') . "</li>" .
			"<li>" . sprintf(__("You can also see %sthe user manual page%s.", "relevanssi"), "<a href='https://www.relevanssi.com/user-manual/wp-cli/'>", "</a>") . "</li>" .
            "</ul>",
	));
	
	// Help sidebars are optional
	$screen->set_help_sidebar(
		'<p><strong>' . __( 'For more information:', 'relevanssi' ) . '</strong></p>' .
		'<p><a href="http://www.relevanssi.com/support/" target="_blank">' . __( 'Plugin support page', 'relevanssi' ) . '</a></p>' .
		'<p><a href="http://wordpress.org/tags/relevanssi?forum_id=10" target="_blank">' . __( 'WordPress.org forum', 'relevanssi' ) . '</a></p>' .
		'<p><a href="mailto:support@relevanssi.zendesk.com">Support email</a></p>' .
		'<p><a href="http://www.relevanssi.com/knowledge-base/" target="_blank">' . __( 'Plugin knowledge base', 'relevanssi' ) . '</a></p>'
	);
}

function relevanssi_premium_plugin_page_actions($plugin_page) {
    add_action( 'load-' . $plugin_page, 'relevanssi_premium_admin_help' );
    add_action( 'admin_footer-' . $plugin_page, 'relevanssi_pdf_action_javascript' );
}

function relevanssi_premium_add_admin_scripts($hook) {
	global $relevanssi_variables;

	$plugin_dir_url = plugin_dir_url($relevanssi_variables['file']);

	$post_hooks = array('post.php', 'post-new.php');
	if (in_array($hook, $post_hooks)) {
		global $post;
		if ($post->post_type === 'attachment') {
			$api_key = get_site_option('relevanssi_api_key');
			wp_enqueue_script( 'relevanssi_admin_pdf_js', $plugin_dir_url . 'premium/admin_pdf_scripts.js', array('jquery') );
			wp_localize_script( 'relevanssi_admin_pdf_js', 'admin_pdf_data', array('processor_url' => RELEVANSSI_SERVICE_URL, 'api_key' => $api_key ) );
		}
	}
}

function relevanssi_import_options($options) {
	$unserialized = json_decode(stripslashes($options));
	foreach ($unserialized as $key => $value) {
		if (in_array($key, array("relevanssi_post_type_weights", "relevanssi_recency_bonus", "relevanssi_punctuation"))) {
			// handling associative arrays that are translated to objects in JSON
			$value = (array) $value;
		}
		update_option($key, $value);
	}

	echo "<div id='relevanssi-warning' class='updated fade'>" . __("Options updated!", "relevanssi") . "</div>";
}

function relevanssi_update_premium_options() {
	if (isset($_REQUEST['relevanssi_link_boost'])) {
		$boost = floatval($_REQUEST['relevanssi_link_boost']);
		update_option('relevanssi_link_boost', $boost);
	}


	if (empty($_REQUEST['relevanssi_api_key'])) {
		unset($_REQUEST['relevanssi_api_key']);
	}
	
	if ($_REQUEST['tab'] === "overview") {
		if (!isset($_REQUEST['relevanssi_hide_post_controls'])) {
			$_REQUEST['relevanssi_hide_post_controls'] = "off";
		}
		if (!isset($_REQUEST['relevanssi_show_post_controls'])) {
			$_REQUEST['relevanssi_show_post_controls'] = "off";
		}
	}

	if ($_REQUEST['tab'] === "indexing") {
		if (!isset($_REQUEST['relevanssi_index_users'])) {
			$_REQUEST['relevanssi_index_users'] = "off";
		}

		if (!isset($_REQUEST['relevanssi_index_synonyms'])) {
			$_REQUEST['relevanssi_index_synonyms'] = "off";
		}

		if (!isset($_REQUEST['relevanssi_index_taxonomies'])) {
			$_REQUEST['relevanssi_index_taxonomies'] = "off";
		}

		if (!isset($_REQUEST['relevanssi_index_subscribers'])) {
			$_REQUEST['relevanssi_index_subscribers'] = "off";
		}

		if (!isset($_REQUEST['relevanssi_index_pdf_parent'])) {
			$_REQUEST['relevanssi_index_pdf_parent'] = "off";
		}
	}

	if ($_REQUEST['tab'] === 'attachments') {
		if (!isset($_REQUEST['relevanssi_read_new_files'])) {
			$_REQUEST['relevanssi_read_new_files'] = "off";
		}

		if (!isset($_REQUEST['relevanssi_send_pdf_files'])) {
			$_REQUEST['relevanssi_send_pdf_files'] = "off";
		}

		if (!isset($_REQUEST['relevanssi_link_pdf_files'])) {
			$_REQUEST['relevanssi_link_pdf_files'] = "off";
		}
	}

	if ($_REQUEST['tab'] === "searching") {
		if (isset($_REQUEST['relevanssi_recency_bonus']) && isset($_REQUEST['relevanssi_recency_days'])) {
			$relevanssi_recency_bonus = array();
			$relevanssi_recency_bonus['bonus'] = floatval($_REQUEST['relevanssi_recency_bonus']);
			$relevanssi_recency_bonus['days'] = intval($_REQUEST['relevanssi_recency_days']);
			update_option('relevanssi_recency_bonus', $relevanssi_recency_bonus);
		}

		if (!isset($_REQUEST['relevanssi_searchblogs_all'])) {
			$_REQUEST['relevanssi_searchblogs_all'] = "off";
		}
	}

	if ($_REQUEST['tab'] === "logging") {
		if (!isset($_REQUEST['relevanssi_hide_branding'])) {
			$_REQUEST['relevanssi_hide_branding'] = "off";
		}
	}

	if ($_REQUEST['tab'] === "excerpts") {
		if (!isset($_REQUEST['relevanssi_highlight_docs_external'])) {
			$_REQUEST['relevanssi_highlight_docs_external'] = "off";
		}
	}

	if (isset($_REQUEST['relevanssi_remove_api_key'])) update_option('relevanssi_api_key', "");
	if (isset($_REQUEST['relevanssi_api_key'])) update_option('relevanssi_api_key', $_REQUEST['relevanssi_api_key']);
	if (isset($_REQUEST['relevanssi_highlight_docs_external'])) update_option('relevanssi_highlight_docs_external', $_REQUEST['relevanssi_highlight_docs_external']);
	if (isset($_REQUEST['relevanssi_index_synonyms'])) update_option('relevanssi_index_synonyms', $_REQUEST['relevanssi_index_synonyms']);
	if (isset($_REQUEST['relevanssi_index_users'])) update_option('relevanssi_index_users', $_REQUEST['relevanssi_index_users']);
	if (isset($_REQUEST['relevanssi_index_subscribers'])) update_option('relevanssi_index_subscribers', $_REQUEST['relevanssi_index_subscribers']);
	if (isset($_REQUEST['relevanssi_index_user_fields'])) update_option('relevanssi_index_user_fields', $_REQUEST['relevanssi_index_user_fields']);
	if (isset($_REQUEST['relevanssi_internal_links'])) update_option('relevanssi_internal_links', $_REQUEST['relevanssi_internal_links']);
	if (isset($_REQUEST['relevanssi_hide_branding'])) update_option('relevanssi_hide_branding', $_REQUEST['relevanssi_hide_branding']);
	if (isset($_REQUEST['relevanssi_hide_post_controls'])) update_option('relevanssi_hide_post_controls', $_REQUEST['relevanssi_hide_post_controls']);
	if (isset($_REQUEST['relevanssi_show_post_controls'])) update_option('relevanssi_show_post_controls', $_REQUEST['relevanssi_show_post_controls']);
	if (isset($_REQUEST['relevanssi_index_taxonomies'])) update_option('relevanssi_index_taxonomies', $_REQUEST['relevanssi_index_taxonomies']);
	if (isset($_REQUEST['relevanssi_taxonomies_to_index'])) update_option('relevanssi_taxonomies_to_index', $_REQUEST['relevanssi_taxonomies_to_index']);
	if (isset($_REQUEST['relevanssi_thousand_separator'])) update_option('relevanssi_thousand_separator', $_REQUEST['relevanssi_thousand_separator']);
	if (isset($_REQUEST['relevanssi_disable_shortcodes'])) update_option('relevanssi_disable_shortcodes', $_REQUEST['relevanssi_disable_shortcodes']);
	if (isset($_REQUEST['relevanssi_mysql_columns'])) update_option('relevanssi_mysql_columns', $_REQUEST['relevanssi_mysql_columns']);
	if (isset($_REQUEST['relevanssi_searchblogs'])) update_option('relevanssi_searchblogs', $_REQUEST['relevanssi_searchblogs']);
	if (isset($_REQUEST['relevanssi_searchblogs_all'])) update_option('relevanssi_searchblogs_all', $_REQUEST['relevanssi_searchblogs_all']);
	if (isset($_REQUEST['relevanssi_read_new_files'])) update_option('relevanssi_read_new_files', $_REQUEST['relevanssi_read_new_files']);
	if (isset($_REQUEST['relevanssi_send_pdf_files'])) update_option('relevanssi_send_pdf_files', $_REQUEST['relevanssi_send_pdf_files']);
	if (isset($_REQUEST['relevanssi_index_pdf_parent'])) update_option('relevanssi_index_pdf_parent', $_REQUEST['relevanssi_index_pdf_parent']);
	if (isset($_REQUEST['relevanssi_link_pdf_files'])) update_option('relevanssi_link_pdf_files', $_REQUEST['relevanssi_link_pdf_files']);
}

function relevanssi_save_postdata($post_id) {
	global $relevanssi_variables;
	// verify if this is an auto save routine.
	// If it is our form has not been submitted, so we dont want to do anything
	if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
		return;

		// If relevanssi_metabox is not set, it's a quick edit.
	if (!isset($_POST['relevanssi_metabox'])) 
		return;

	if (isset($_POST['relevanssi_hidepost'])) {
		if (!wp_verify_nonce($_POST['relevanssi_hidepost'], plugin_basename( $relevanssi_variables['file'] ))) {
			return;
		}
	}

	// Check permissions
	if (isset($_POST['post_type'])) {
		if ('page' == $_POST['post_type']) {
			if (!current_user_can('edit_page', $post_id)) return;
		}
		else {
			if (!current_user_can('edit_post', $post_id)) return;
		}
	}

	isset($_POST['relevanssi_hide_post']) ? $hide = $_POST['relevanssi_hide_post'] : $hide = '';

	if ('on' == $hide) {
		relevanssi_delete($post_id);
	}

	$hide == 'on' ?
		update_post_meta($post_id, '_relevanssi_hide_post', $hide) :
		delete_post_meta($post_id, '_relevanssi_hide_post');

	if (isset($_POST['relevanssi_pin'])) {
		delete_post_meta($post_id, '_relevanssi_pin');
		$pins = explode(',', $_POST['relevanssi_pin']);
		foreach ($pins as $pin) {
			$pin = trim($pin);
			add_post_meta($post_id, '_relevanssi_pin', $pin);
		}
	}
	else {
		delete_post_meta($post_id, '_relevanssi_pin');
	}

	if (isset($_POST['relevanssi_unpin'])) {
		delete_post_meta($post_id, '_relevanssi_unpin');
		$pins = explode(',', $_POST['relevanssi_unpin']);
		foreach ($pins as $pin) {
			$pin = trim($pin);
			add_post_meta($post_id, '_relevanssi_unpin', $pin);
		}
	}
	else {
		delete_post_meta($post_id, '_relevanssi_unpin');
	}
}

function relevanssi_network_menu() {
	global $relevanssi_variables;
	RELEVANSSI_PREMIUM ? $name = "Relevanssi Premium" : $name = "Relevanssi";
	add_menu_page(
		$name,
		$name,
		apply_filters('relevanssi_options_capability', 'manage_options'),
		$relevanssi_variables['file'],
		'relevanssi_network_options'
	);
}

function relevanssi_network_options() {
	global $relevanssi_variables;

?>
<div class='wrap'><h2><?php _e('Relevanssi network options', 'relevanssi'); ?></h2>
<?php

	if (!empty($_POST)) {
		if (isset($_REQUEST['submit'])) {
			check_admin_referer(plugin_basename($relevanssi_variables['file']), 'relevanssi_network_options');
			relevanssi_update_network_options();
		}
		if (isset($_REQUEST['copytoall'])) {
			check_admin_referer(plugin_basename($relevanssi_variables['file']), 'relevanssi_network_options');
			relevanssi_copy_options_to_subsites($_REQUEST);
		}
	}

	$this_page = "?page=relevanssi/relevanssi.php";
	if (RELEVANSSI_PREMIUM) {
		$this_page = "?page=relevanssi-premium/relevanssi.php";
	}

	echo "<form method='post' action='admin.php{$this_page}'>";

	wp_nonce_field(plugin_basename($relevanssi_variables['file']), 'relevanssi_network_options');

	$api_key = get_site_option('relevanssi_api_key');

?>
    <table class="form-table">

<?php
	relevanssi_form_api_key($api_key);
    ?>

</table>
    <input type='submit' name='submit' value='<?php esc_attr_e('Save the options', 'relevanssi'); ?>' class='button button-primary' />


</form>

<h2><?php _e('Copy options from one site to other sites', 'relevanssi'); ?></h2>
<p><?php _e("Choose a blog and copy all the options from that blog to all other blogs that have active Relevanssi Premium. Be careful! There's no way to undo the procedure!", "relevanssi");?></p>

<form id='copy_config' method='post' action='admin.php?page=relevanssi-premium/relevanssi.php'>
<?php wp_nonce_field(plugin_basename($relevanssi_variables['file']), 'relevanssi_network_options'); ?>

<table class="form-table">
<tr>
	<th scope="row"><?php _e("Copy options", "relevanssi"); ?></th>
	<td>
<?php

	$raw_blog_list = get_sites(array('number' => 2000));
	$blog_list = array();
	foreach ($raw_blog_list as $blog) {
		$details = get_blog_details($blog->blog_id);
		$blog_list[$details->blogname] = $blog->blog_id;
	}
	ksort($blog_list);
	echo "<select id='sourceblog' name='sourceblog'>";
	foreach ($blog_list as $name => $id) {
		echo "<option value='$id'>$name</option>";
	}
	echo "</select>";

?>
	<input type='submit' name='copytoall' value='<?php esc_attr_e('Copy options to all other subsites', 'relevanssi'); ?>' class='button button-primary' />
	</td>
</tr>
</table>

<?php

	echo "</form>";

	echo "</div>";
}

function relevanssi_update_network_options() {
	if (empty($_REQUEST['relevanssi_api_key'])) {
		unset($_REQUEST['relevanssi_api_key']);
	}

	if (isset($_REQUEST['relevanssi_remove_api_key'])) update_site_option('relevanssi_api_key', "");
	if (isset($_REQUEST['relevanssi_api_key'])) update_site_option('relevanssi_api_key', $_REQUEST['relevanssi_api_key']);
}

function relevanssi_copy_options_to_subsites($data) {
	$sourceblog = $data['sourceblog'];
	if (!is_numeric($sourceblog)) return;
	$sourceblog = esc_sql($sourceblog);

	printf("<h2>" . __("Copying options from blog %s", "relevanssi") . "</h2>", $sourceblog);
	global $wpdb;
	switch_to_blog($sourceblog);
	$q = "SELECT * FROM $wpdb->options WHERE option_name LIKE 'relevanssi%'";
	restore_current_blog();

	$results = $wpdb->get_results($q);

	$blog_list = get_sites(array('number' => 2000));
	foreach ($blog_list as $blog) {
		if ($blog->blog_id == $sourceblog) continue;
		switch_to_blog($blog->blog_id);

		printf("<p>" . __('Processing blog %s:', 'relevanssi') . "<br />", $blog->blog_id);
		if (!is_plugin_active('relevanssi-premium/relevanssi.php')) {
			echo __('Relevanssi is not active in this blog.', 'relevanssi') . "</p>";
			continue;
		}
		foreach ($results as $option) {
			is_serialized($option->option_value) ? $value = unserialize($option->option_value) : $value = $option->option_value;
			update_option($option->option_name, $value);
		}
		echo __("Options updated.", "relevanssi") . "</p>";
		restore_current_blog();
	}
}